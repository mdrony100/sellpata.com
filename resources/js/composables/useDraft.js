import { computed, reactive, ref, watch } from 'vue';
import { api, ApiError, getPath, setPath } from '../lib/api';

const DEBOUNCE_MS = 800;

/**
 * Draft state + autosave.
 * - saves 800ms after the last edit
 * - never reports "saved" before the server confirms
 * - one request in flight at a time, newer edits queue behind it
 * - a revision mismatch (another tab) surfaces as a visible conflict
 */
export function useDraft(publicId) {
    const draft = ref(null);
    const template = ref(null);
    const content = reactive({});
    const media = reactive({});
    const revision = ref(1);
    const status = ref('idle'); // idle | saving | saved | error | offline | conflict
    const lastSavedAt = ref(null);
    const conflictServer = ref(null);
    const recovery = ref(null);

    let timer = null;
    let saving = false;
    let queued = false;
    let dirty = false;

    const storageKey = computed(() => `sellpata:draft:${publicId}`);

    function hydrate(data) {
        draft.value = data.draft;
        template.value = data.template;
        revision.value = data.draft.revision;
        lastSavedAt.value = data.draft.last_saved_at;
        Object.keys(content).forEach((key) => delete content[key]);
        Object.assign(content, data.draft.content ?? {});
        Object.keys(media).forEach((key) => delete media[key]);
        Object.assign(media, data.media ?? {});
    }

    async function load() {
        const data = await api(`/api/drafts/${publicId}`);
        hydrate(data);

        try {
            const buffered = JSON.parse(localStorage.getItem(storageKey.value) ?? 'null');
            if (buffered?.lastSavedAt && buffered.lastSavedAt !== data.draft.last_saved_at) {
                recovery.value = buffered;
            }
        } catch {
            /* ignore corrupt buffer */
        }

        status.value = 'idle';

        return data;
    }

    function bufferLocally() {
        if (!draft.value) return;

        try {
            localStorage.setItem(storageKey.value, JSON.stringify({
                content: JSON.parse(JSON.stringify(content)),
                revision: revision.value,
                lastSavedAt: lastSavedAt.value,
            }));
        } catch {
            /* storage may be blocked */
        }
    }

    function clearBuffer() {
        try {
            localStorage.removeItem(storageKey.value);
        } catch {
            /* ignore */
        }
    }

    async function saveNow() {
        if (saving) {
            queued = true;
            return;
        }

        if (!dirty) return;

        saving = true;
        dirty = false;
        status.value = 'saving';

        try {
            const payload = JSON.parse(JSON.stringify(content));
            const result = await api(`/api/drafts/${publicId}`, {
                method: 'PATCH',
                body: { content: payload, revision: revision.value },
            });

            revision.value = result.revision;
            lastSavedAt.value = result.saved_at;
            status.value = 'saved';
            clearBuffer();
        } catch (error) {
            dirty = true;

            if (error instanceof ApiError && error.status === 409) {
                conflictServer.value = error.data.draft ?? null;
                status.value = 'conflict';
            } else if (error instanceof ApiError && error.data?.offline) {
                status.value = 'offline';
                bufferLocally();
            } else {
                status.value = 'error';
                bufferLocally();
            }
        } finally {
            saving = false;

            if (queued) {
                queued = false;
                schedule(0);
            }
        }
    }

    function schedule(delay = DEBOUNCE_MS) {
        dirty = true;
        clearTimeout(timer);
        timer = setTimeout(() => {
            timer = null;
            saveNow();
        }, delay);
    }

    /** Resolves true only once everything typed is confirmed by the server. */
    async function flush() {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }

        if (!dirty && !saving) return true;

        await saveNow();

        while (saving) {
            await new Promise((resolve) => setTimeout(resolve, 60));
        }

        if (dirty && status.value !== 'conflict') return flush();

        return status.value === 'saved' || status.value === 'idle';
    }

    function updateAt(path, value) {
        setPath(content, path, value);
    }

    function valueAt(path) {
        return getPath(content, path);
    }

    function restoreServer() {
        if (!conflictServer.value) return;

        revision.value = conflictServer.value.revision;
        Object.keys(content).forEach((key) => delete content[key]);
        Object.assign(content, conflictServer.value.content ?? {});
        conflictServer.value = null;
        status.value = 'saved';
        dirty = false;
    }

    function keepMine() {
        if (!conflictServer.value) return;

        revision.value = conflictServer.value.revision;
        conflictServer.value = null;
        dirty = true;
        schedule(0);
    }

    function applyRecovery() {
        if (!recovery.value) return;

        Object.keys(content).forEach((key) => delete content[key]);
        Object.assign(content, recovery.value.content);
        revision.value = recovery.value.revision;
        recovery.value = null;
        dirty = true;
        schedule(0);
    }

    function dismissRecovery() {
        recovery.value = null;
        clearBuffer();
    }

    watch(content, () => {
        if (status.value === 'conflict') return;
        bufferLocally();
        schedule();
    }, { deep: true });

    const statusLabel = computed(() => ({
        idle: '',
        saving: 'সংরক্ষণ হচ্ছে…',
        saved: 'সব পরিবর্তন সংরক্ষিত',
        error: 'সংরক্ষণ হয়নি—আবার চেষ্টা করুন',
        offline: 'ইন্টারনেট সংযোগ নেই',
        conflict: 'অন্য ট্যাবে পরিবর্তন হয়েছে',
    }[status.value] ?? ''));

    return {
        draft, template, content, media, revision, status, statusLabel, lastSavedAt,
        conflictServer, recovery, load, saveNow, flush, updateAt, valueAt,
        restoreServer, keepMine, applyRecovery, dismissRecovery, hydrate,
    };
}
