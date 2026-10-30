<template>
    <div class="builder">
        <div v-if="loading" class="builder__state">লোড হচ্ছে…</div>

        <div v-else-if="notFound" class="builder__state">
            <h1>এই ড্রাফটটি পাওয়া যায়নি</h1>
            <p>লিংকটি অসম্পূর্ণ, অথবা এটি অন্য ব্রাউজারে তৈরি হয়েছে।</p>
            <RouterLink to="/start" class="btn btn--primary">টেমপ্লেটে ফিরে যান</RouterLink>
        </div>

        <template v-else>
            <header class="builder__bar">
                <RouterLink :to="{ name: 'start', query: fromCategory ? { category: fromCategory } : {} }" class="onb__back">
                    ← টেমপ্লেটে ফিরে যান
                </RouterLink>
                <div class="builder__title">
                    <strong>{{ template?.name }}</strong>
                    <span v-if="statusLabel" class="builder__status" :class="`builder__status--${status}`">{{ statusLabel }}</span>
                </div>
                <div class="builder__bar-actions">
                    <button v-if="themes.length" type="button" class="btn btn--ghost" @click="themeOpen = true">থিম বদলান</button>
                    <button type="button" class="btn btn--primary btn--lg" @click="publish">প্রকাশ করুন</button>
                </div>
            </header>

            <p class="builder__note">
                আপনার কাজ এই ব্রাউজারে সংরক্ষিত থাকবে। ফোন নম্বর যাচাই করলে এটি আপনার অ্যাকাউন্টে যুক্ত হবে।
            </p>

            <div v-if="recovery" class="builder__banner">
                <p>এই ব্রাউজারে সংরক্ষিত না হওয়া নতুন পরিবর্তন পাওয়া গেছে।</p>
                <button type="button" class="btn btn--primary btn--sm" @click="draftApi.applyRecovery()">পুনরুদ্ধার করুন</button>
                <button type="button" class="btn btn--ghost btn--sm" @click="draftApi.dismissRecovery()">বাতিল করুন</button>
            </div>

            <div v-if="status === 'conflict'" class="builder__banner builder__banner--warn">
                <p>অন্য একটি ট্যাবে এই ড্রাফট বদলানো হয়েছে।</p>
                <button type="button" class="btn btn--primary btn--sm" @click="draftApi.restoreServer()">সার্ভারের সংস্করণ নিন</button>
                <button type="button" class="btn btn--ghost btn--sm" @click="draftApi.keepMine()">আমার লেখা রাখুন</button>
            </div>

            <div v-if="error" class="builder__banner builder__banner--error">
                <p>{{ error }}</p>
                <button v-if="status === 'error' || status === 'offline'" type="button" class="btn btn--ghost btn--sm" @click="draftApi.saveNow()">
                    আবার চেষ্টা করুন
                </button>
            </div>

            <!-- Editing workspace: choosing a design drops you straight in here -->
            <div class="builder__workspace">
                <div class="builder__tabs" role="tablist">
                    <button type="button" role="tab" :aria-selected="tab === 'edit'" :class="{ on: tab === 'edit' }" @click="tab = 'edit'">এডিট করুন</button>
                    <button type="button" role="tab" :aria-selected="tab === 'live'" :class="{ on: tab === 'live' }" @click="tab = 'live'">লাইভ প্রিভিউ</button>
                </div>

                <div class="builder__pane" :class="{ 'builder__pane--hidden': tab !== 'edit' }">
                    <TemplateRender :content="content" :design="template?.design" :media="media" editable @edit="openEditor" />
                </div>

                <div class="builder__pane builder__pane--preview" :class="{ 'builder__pane--hidden': tab !== 'live' }">
                    <TemplateRender :content="content" :design="template?.design" :media="media" />
                </div>
            </div>

            <!-- Field editor -->
            <div v-if="editingField" class="editor" role="dialog" aria-modal="true" :aria-label="`${editingField.label} সম্পাদনা`">
                <div class="editor__panel">
                    <header>
                        <h2>{{ editingField.label }}</h2>
                        <button type="button" class="btn btn--ghost btn--sm" @click="closeEditor">বন্ধ করুন</button>
                    </header>

                    <template v-if="editingField.type === 'list'">
                        <div v-for="(item, index) in listItems" :key="index" class="editor__list-item">
                            <div v-for="sub in editingField.item" :key="sub.key" class="editor__field">
                                <label :for="`${editingField.key}-${index}-${sub.key}`">{{ sub.label }}</label>
                                <input
                                    v-if="sub.type === 'image'"
                                    :id="`${editingField.key}-${index}-${sub.key}`"
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp"
                                    @change="uploadImage(`${editingField.key}.${index}.${sub.key}`, $event)"
                                />
                                <textarea
                                    v-else
                                    :id="`${editingField.key}-${index}-${sub.key}`"
                                    :value="item[sub.key] ?? ''"
                                    rows="2"
                                    maxlength="500"
                                    @input="setListItem(index, sub.key, $event.target.value)"
                                />
                            </div>
                            <div class="editor__row-actions">
                                <button type="button" class="btn btn--ghost btn--sm" :disabled="index === 0" @click="moveItem(index, -1)">উপরে</button>
                                <button type="button" class="btn btn--ghost btn--sm" :disabled="index === listItems.length - 1" @click="moveItem(index, 1)">নিচে</button>
                                <button type="button" class="btn btn--ghost btn--sm" @click="removeItem(index)">মুছুন</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn--primary btn--sm" @click="addItem">নতুন যোগ করুন</button>
                    </template>

                    <template v-else-if="editingField.type === 'toggle'">
                        <label class="editor__toggle">
                            <input type="checkbox" :checked="valueAt(editingField.key) !== false" @change="update(editingField.key, $event.target.checked)" />
                            সেকশনটি দেখাবেন
                        </label>
                    </template>

                    <template v-else-if="editingField.type === 'select'">
                        <select :value="valueAt(editingField.key)" @change="update(editingField.key, $event.target.value)">
                            <option v-for="option in editingField.options || []" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </select>
                    </template>

                    <template v-else-if="editingField.type === 'section_order'">
                        <ol class="editor__order">
                            <li v-for="(key, index) in sectionOrder" :key="key">
                                <span>{{ sectionLabel(key) }}</span>
                                <button type="button" class="btn btn--ghost btn--sm" :disabled="index === 0" @click="moveSection(index, -1)">উপরে</button>
                                <button type="button" class="btn btn--ghost btn--sm" :disabled="index === sectionOrder.length - 1" @click="moveSection(index, 1)">নিচে</button>
                            </li>
                        </ol>
                    </template>

                    <template v-else-if="editingField.type === 'image'">
                        <input type="file" accept="image/png,image/jpeg,image/webp" @change="uploadImage(editingField.key, $event)" />
                        <p class="editor__hint">JPG, PNG বা WebP, সর্বোচ্চ ৪ মেগাবাইট।</p>
                        <button v-if="media[editingField.key]" type="button" class="btn btn--ghost btn--sm" @click="clearImage(editingField.key)">ছবি সরান</button>
                    </template>

                    <template v-else-if="editingField.type === 'color'">
                        <input type="color" :value="valueAt(editingField.key) || '#0d3b2e'" @input="update(editingField.key, $event.target.value)" />
                    </template>

                    <template v-else>
                        <textarea
                            v-if="editingField.type === 'textarea'"
                            :value="valueAt(editingField.key)"
                            rows="4"
                            :maxlength="editingField.maxlength || 2000"
                            @input="update(editingField.key, $event.target.value)"
                        />
                        <input
                            v-else
                            :type="editingField.type === 'price' ? 'number' : 'text'"
                            :inputmode="editingField.type === 'price' ? 'decimal' : undefined"
                            :maxlength="editingField.type === 'price' ? 8 : (editingField.maxlength || 180)"
                            :value="valueAt(editingField.key)"
                            @input="update(editingField.key, editingField.type === 'price' ? formatNumberInput($event.target.value) : $event.target.value)"
                        />
                    </template>

                    <footer>
                        <button type="button" class="btn btn--primary" @click="closeEditor">সংরক্ষণ করুন</button>
                    </footer>
                </div>
            </div>

            <div v-if="themeOpen" class="editor" role="dialog" aria-modal="true" aria-label="থিম বদলান">
                <div class="editor__panel editor__panel--narrow">
                    <h2>থিম বদলান</h2>
                    <p class="editor__lede">লেখা, দাম, ছবি, ভ্যারিয়েন্ট, প্রশ্ন ও ডেলিভারি থাকবে। হিরোর ধরন ও সেকশনের ক্রম নতুন থিমের মতো হবে। যে থিমে নমুনা রিভিউ নেই, সেখানে নমুনা মতামত যাবে না।</p>
                    <label v-for="item in themes" :key="item.id" class="editor__toggle">
                        <input v-model="nextThemeId" type="radio" name="next-theme" :value="item.id" />
                        <span><strong>{{ item.name }}</strong> — {{ item.tagline }}</span>
                    </label>
                    <p v-if="themeError" class="editor__error">{{ themeError }}</p>
                    <footer>
                        <button type="button" class="btn btn--primary" :disabled="!nextThemeId || switching" @click="confirmTheme">বদল নিশ্চিত করুন</button>
                        <button type="button" class="btn btn--ghost" @click="themeOpen = false">বাতিল</button>
                    </footer>
                </div>
            </div>

            <div class="builder__publish-bar">
                <button type="button" class="btn btn--primary btn--lg" @click="publish">প্রকাশ করুন</button>
            </div>

            <!-- Phone + OTP -->
            <div v-if="phoneOpen" class="editor" role="dialog" aria-modal="true" @keydown.esc="closePhone">
                <div class="editor__panel editor__panel--narrow">
                    <template v-if="claimStep === 'phone'">
                        <h2>{{ skipOtp ? 'আপনার ল্যান্ডিং পেজটি নিজের নামে সংরক্ষণ করুন' : 'আপনার ল্যান্ডিং পেজটি নিজের নামে সংরক্ষণ করুন' }}</h2>
                        <p class="editor__lede">মোবাইল নম্বর যাচাই করুন। আপনার সাজানো পেজটি আপনার অ্যাকাউন্টে যুক্ত হবে।</p>
                        <label for="phone">মোবাইল নম্বর</label>
                        <div class="editor__phone">
                            <span>+88</span>
                            <input id="phone" v-model="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" />
                        </div>
                        <p class="editor__hint">{{ skipOtp ? 'এই নম্বরেই আপনার অ্যাকাউন্ট তৈরি হবে।' : 'এই নম্বরে একটি যাচাইকরণ কোড পাঠানো হবে।' }}</p>
                        <p v-if="phoneError" class="editor__error">{{ phoneError }}</p>
                        <footer>
                            <button type="button" class="btn btn--primary" :disabled="sendingPhone" @click="sendOtp">
                                {{ sendingPhone ? 'একটু অপেক্ষা করুন…' : (skipOtp ? 'এগিয়ে যান' : 'ওটিপি পাঠান') }}
                            </button>
                            <button type="button" class="btn btn--ghost" @click="closePhone">বন্ধ করুন</button>
                        </footer>
                    </template>

                    <template v-else-if="claimStep === 'code'">
                        <h2>কোড যাচাই করুন</h2>
                        <p class="editor__lede">{{ maskedPhone }} নম্বরে পাঠানো ৬ সংখ্যার কোডটি লিখুন।</p>
                        <input
                            v-model="code"
                            class="editor__code"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            placeholder="------"
                            aria-label="যাচাইকরণ কোড"
                        />
                        <div v-if="testCode" class="editor__demo" role="status">
                            <p class="editor__demo-title">ডেমো মোড — এখনো এসএমএস চালু হয়নি</p>
                            <p class="editor__demo-code">{{ testCode }}</p>
                            <button type="button" class="btn btn--ghost btn--sm" @click="fillTestCode">কোড বসিয়ে দিন</button>
                        </div>
                        <p v-if="codeError" class="editor__error">{{ codeError }}</p>
                        <footer>
                            <button type="button" class="btn btn--primary" :disabled="verifying" @click="verifyOtp">
                                {{ verifying ? 'যাচাই হচ্ছে…' : 'যাচাই করুন' }}
                            </button>
                            <button type="button" class="btn btn--ghost" @click="changeNumber">নম্বর পরিবর্তন করুন</button>
                            <button type="button" class="btn btn--ghost" :disabled="cooldown > 0 || sendingPhone" @click="resendOtp">
                                {{ cooldown > 0 ? `আবার কোড পাঠান (${cooldown})` : 'আবার কোড পাঠান' }}
                            </button>
                        </footer>
                    </template>

                    <template v-else>
                        <h2>ফোন নম্বর যাচাই হয়েছে!</h2>
                        <p class="editor__lede">আপনার ল্যান্ডিং পেজটি আপনার অ্যাকাউন্টে সংরক্ষিত হয়েছে। এটি এখনো প্রকাশিত হয়নি।</p>
                        <footer>
                            <button type="button" class="btn btn--primary" @click="closePhone">এডিটরে ফিরে যান</button>
                        </footer>
                    </template>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import TemplateRender from '../components/builder/TemplateRender.vue';
import { useDraft } from '../composables/useDraft';
import { api, getPath } from '../lib/api';

const route = useRoute();
const router = useRouter();
const publicId = route.params.publicId;

const draftApi = useDraft(publicId);
const { content, media, template, status, statusLabel, recovery } = draftApi;

const loading = ref(true);
const notFound = ref(false);
const tab = ref('edit');
const error = ref('');
const editingField = ref(null);
const themes = ref([]);
const themeOpen = ref(false);
const nextThemeId = ref(null);
const themeError = ref('');
const switching = ref(false);
const fromCategory = route.query.from ?? null;

const phoneOpen = ref(false);
const claimStep = ref('phone');
const phone = ref('');
const code = ref('');
const maskedPhone = ref('');
const testCode = ref(null);
const skipOtp = ref(false);

function fillTestCode() {
    if (testCode.value) code.value = String(testCode.value);
}
const phoneError = ref('');
const codeError = ref('');
const sendingPhone = ref(false);
const verifying = ref(false);
const cooldown = ref(0);
let cooldownTimer = null;

const schema = computed(() => template.value?.schema ?? []);
const listItems = computed(() => (editingField.value ? (getPath(content, editingField.value.key) ?? []) : []));

function openEditor(key) {
    const root = key.split('.')[0];
    const field = schema.value.find((item) => item.key === root) ?? schema.value.find((item) => item.key === key);

    if (field) editingField.value = { ...field, key: field.key };
}

function closeEditor() {
    editingField.value = null;
}

function update(path, value) {
    draftApi.updateAt(path, value);
}

function valueAt(path) {
    return getPath(content, path);
}

function formatNumberInput(value) {
    return value.replace(/[^0-9.]/g, '');
}

function addItem() {
    const field = editingField.value;
    const max = field.max || 12;
    if (listItems.value.length >= max) return;
    const blank = Object.fromEntries(field.item.map((sub) => [sub.key, '']));
    draftApi.updateAt(field.key, [...listItems.value, blank]);
}

const sectionOrder = computed(() => {
    const current = valueAt('section_order');
    return Array.isArray(current) ? current : [];
});

function sectionLabel(key) {
    return editingField.value?.options?.find((option) => option.value === key)?.label || key;
}

function moveSection(index, delta) {
    const items = [...sectionOrder.value];
    const target = index + delta;
    if (target < 0 || target >= items.length) return;
    [items[index], items[target]] = [items[target], items[index]];
    draftApi.updateAt('section_order', items);
}

async function confirmTheme() {
    themeError.value = '';
    switching.value = true;
    try {
        await draftApi.flush();
        await api(`/api/drafts/${publicId}/switch-theme`, {
            method: 'POST',
            body: { template_id: nextThemeId.value, confirm: true },
        });
        const data = await draftApi.load();
        themes.value = data.themes ?? [];
        themeOpen.value = false;
    } catch (e) {
        themeError.value = e.data?.message || 'থিম বদলানো যায়নি।';
    } finally {
        switching.value = false;
    }
}

function setListItem(index, key, value) {
    const items = JSON.parse(JSON.stringify(listItems.value));
    items[index][key] = value;
    draftApi.updateAt(editingField.value.key, items);
}

function removeItem(index) {
    const items = JSON.parse(JSON.stringify(listItems.value));
    items.splice(index, 1);
    draftApi.updateAt(editingField.value.key, items);
}

function moveItem(index, delta) {
    const items = JSON.parse(JSON.stringify(listItems.value));
    const target = index + delta;
    if (target < 0 || target >= items.length) return;
    [items[index], items[target]] = [items[target], items[index]];
    draftApi.updateAt(editingField.value.key, items);
}

async function uploadImage(field, event) {
    const file = event.target.files?.[0];
    if (!file) return;

    const form = new FormData();
    form.append('field', field);
    form.append('file', file);

    try {
        const result = await api(`/api/drafts/${publicId}/media`, { method: 'POST', form });
        draftApi.media[field] = result.url;
    } catch (e) {
        error.value = e.data?.message ?? 'ছবি আপলোড করা যায়নি।';
    }
}

function clearImage(field) {
    delete draftApi.media[field];
    draftApi.updateAt(field, null);
}

async function publish() {
    error.value = '';

    // Editing a page you already own: no phone claim, just republish.
    if (route.query.mode === 'page') {
        await republishPage();
        return;
    }

    const missing = schema.value.find((field) => field.required && !String(valueAt(field.key) ?? '').trim());

    if (missing) {
        error.value = `“${missing.label}” পূরণ করুন।`;
        openEditor(missing.key);
        return;
    }

    const saved = await draftApi.flush();

    if (!saved) {
        error.value = 'সব পরিবর্তন সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।';
        return;
    }

    claimStep.value = 'phone';
    phoneOpen.value = true;
}

async function republishPage() {
    const saved = await draftApi.flush();
    if (!saved) {
        error.value = 'সব পরিবর্তন সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।';
        return;
    }

    try {
        await api('/api/pages', { method: 'POST', body: { draft_id: draftApi.draft.value?.id } });
        await router.push({ path: '/dashboard/pages', query: { published: 1 } });
    } catch (e) {
        await router.push({ path: '/dashboard/pages', query: { notice: e.data?.message ?? '' } });
    }
}

async function sendOtp() {
    phoneError.value = '';
    sendingPhone.value = true;

    try {
        const result = await api(`/api/drafts/${publicId}/otp`, { method: 'POST', body: { phone: phone.value } });

        // Phone-only claim: the draft is already ours, go straight on.
        if (result.skip_otp) {
            // Account is created and the draft is ours; carry on to store setup.
            claimStep.value = 'done';
            await finishOnboarding(result);
            return;
        }

        maskedPhone.value = result.phone_masked;
        testCode.value = result.test_code;
        claimStep.value = 'code';
        // Local demo mode: put the shown code straight into the field.
        if (result.test_code) fillTestCode();
        startCooldown(result.resend_after ?? 60);
    } catch (e) {
        phoneError.value = e.data?.error ?? e.data?.errors?.phone?.[0] ?? 'কোড পাঠানো যায়নি।';
    } finally {
        sendingPhone.value = false;
    }
}

/**
 * Verification is no longer the end of the flow: the claimed draft is handed to
 * the dashboard, where a store address (the customer's subdomain) is required
 * before the page can go live.
 */
async function finishOnboarding(result) {
    const draftId = draftApi.draft.value?.id ?? result?.draft?.id ?? null;
    if (!draftId) return;

    try {
        const page = await api('/api/pages', { method: 'POST', body: { draft_id: draftId } });
        await router.push({ path: '/dashboard/pages', query: { created: page.page_id } });
    } catch (error) {
        // Keep the work: a page-limit or setup problem lands the customer in the
        // dashboard with an explanation instead of losing the draft.
        await router.push({ path: '/dashboard/pages', query: { notice: error.data?.message ?? '' } });
    }
}

async function verifyOtp() {
    codeError.value = '';
    verifying.value = true;

    try {
        const result = await api(`/api/drafts/${publicId}/otp/verify`, { method: 'POST', body: { phone: phone.value, code: code.value } });
        claimStep.value = 'done';
        await finishOnboarding(result);
    } catch (e) {
        codeError.value = e.data?.error ?? 'কোডটি সঠিক নয়।';
    } finally {
        verifying.value = false;
    }
}

function changeNumber() {
    claimStep.value = 'phone';
    code.value = '';
    codeError.value = '';
}

async function resendOtp() {
    code.value = '';
    await sendOtp();
}

function startCooldown(seconds) {
    cooldown.value = seconds;
    clearInterval(cooldownTimer);
    cooldownTimer = setInterval(() => {
        cooldown.value -= 1;
        if (cooldown.value <= 0) clearInterval(cooldownTimer);
    }, 1000);
}

function closePhone() {
    phoneOpen.value = false;
    clearInterval(cooldownTimer);
    cooldown.value = 0;
    if (claimStep.value === 'done') claimStep.value = 'phone';
}

onMounted(async () => {
    try {
        const data = await draftApi.load();
        skipOtp.value = data?.skip_otp === true;
        themes.value = data?.themes ?? [];
        nextThemeId.value = data?.themes?.[0]?.id ?? null;
    } catch (e) {
        notFound.value = true;
    } finally {
        loading.value = false;
    }
});

onUnmounted(() => clearInterval(cooldownTimer));
</script>
