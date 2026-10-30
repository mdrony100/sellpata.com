<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../lib/api';

const info = ref(null);
const loading = ref(true);
const loadError = ref('');
const input = ref('');
const error = ref('');
const busy = ref('');
const copied = ref('');
const confirmRemove = ref(false);
let copyTimer;

const domain = computed(() => info.value?.domain ?? null);
const entitled = computed(() => info.value?.entitled === true);
const planLabel = computed(() => joinBn(info.value?.upgrade_plans ?? []));
const exampleName = computed(() => domain.value?.host || 'mybrand.com');
const records = computed(() => info.value?.records ?? []);
const statusMeta = computed(() => ({
    pending: {
        label: 'DNS অপেক্ষমাণ',
        badge: 'dash-badge--warn',
        text: 'DNS এখনো যাচাই হয়নি। রেকর্ড বসানোর পর আবার যাচাই করুন। আপডেট হতে কয়েক মিনিট থেকে ৪৮ ঘণ্টা লাগতে পারে।',
    },
    verified: {
        label: 'DNS যাচাই হয়েছে — সক্রিয়',
        badge: 'dash-badge--ok',
        text: 'DNS মিলেছে। এই ডোমেইনে স্টোর খুলবে। SSL সার্টিফিকেট যাচাইয়ের পর স্বয়ংক্রিয়ভাবে সেট হবে।',
    },
    failed: {
        label: 'যাচাই ব্যর্থ',
        badge: 'dash-badge--bad',
        text: 'DNS রেকর্ড এখনো সার্ভারে পৌঁছায়নি। নিচের টেবিলের সাথে মিলিয়ে পুরনো A/AAAA মুছে আবার যাচাই করুন।',
    },
}[domain.value?.status] ?? null));

onMounted(load);

async function load() {
    loading.value = true;
    loadError.value = '';
    try {
        info.value = await api('/api/domain');
    } catch (err) {
        loadError.value = err.data?.message ?? 'সেটিংস লোড হয়নি। পাতাটি রিফ্রেশ করে আবার চেষ্টা করুন।';
    } finally {
        loading.value = false;
    }
}

async function add() {
    error.value = '';
    busy.value = 'add';
    try {
        info.value = await api('/api/domain', { method: 'POST', body: { domain: input.value } });
        input.value = '';
        confirmRemove.value = false;
    } catch (err) {
        error.value = err.data?.errors?.domain?.[0] ?? err.data?.message ?? 'ডোমেইন যোগ করা যায়নি।';
    } finally {
        busy.value = '';
    }
}

async function check() {
    error.value = '';
    busy.value = 'check';
    try {
        info.value = await api('/api/domain/check', { method: 'POST' });
    } catch (err) {
        error.value = err.data?.message ?? 'যাচাই করা যায়নি। আবার চেষ্টা করুন।';
    } finally {
        busy.value = '';
    }
}

async function remove() {
    error.value = '';
    busy.value = 'remove';
    try {
        info.value = await api('/api/domain', { method: 'DELETE' });
        confirmRemove.value = false;
    } catch (err) {
        error.value = err.data?.message ?? 'ডোমেইন সরানো যায়নি।';
    } finally {
        busy.value = '';
    }
}

async function copy(value, key) {
    const text = String(value ?? '');
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.left = '-9999px';
        document.body.appendChild(area);
        area.select();
        document.execCommand('copy');
        area.remove();
    }
    copied.value = key;
    clearTimeout(copyTimer);
    copyTimer = setTimeout(() => {
        if (copied.value === key) copied.value = '';
    }, 1600);
}

function joinBn(names) {
    if (!names.length) return 'উচ্চতর';
    if (names.length === 1) return names[0];
    if (names.length === 2) return `${names[0]} ও ${names[1]}`;
    return `${names.slice(0, -1).join(', ')} ও ${names[names.length - 1]}`;
}

function when(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleString('bn-BD', { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
    <p v-if="loading" class="dash-muted">লোড হচ্ছে…</p>
    <p v-else-if="loadError" class="dash-note dash-note--bad" role="alert">{{ loadError }}</p>

    <template v-else-if="info">
        <section class="dash-panel dash-stack">
            <div>
                <p class="dash-kicker">ডোমেইন</p>
                <h2>কাস্টম ডোমেইন</h2>
                <p v-if="entitled" class="dash-muted">ফ্রি ঠিকানা আগের মতোই চালু থাকবে। চাইলে নিজের ডোমেইন যুক্ত করতে পারেন — ক্রেতারা সেই ঠিকানাতেই স্টোর দেখবে।</p>
                <p v-else class="dash-muted">ফ্রি ঠিকানা আগের মতোই চালু থাকবে। নিজের ডোমেইন {{ planLabel }} প্যাকেজে যুক্ত করা যায়।</p>
            </div>

            <div>
                <p class="dash-muted">আপনার ফ্রি ঠিকানা</p>
                <div class="dash-hostline">
                    <code>{{ info.free_host }}</code>
                    <button type="button" class="dash-copy" :class="{ 'is-done': copied === 'free' }" @click="copy(info.free_host, 'free')">
                        {{ copied === 'free' ? 'কপি হয়েছে' : 'কপি' }}
                    </button>
                    <a class="dash-textlink" :href="`https://${info.free_host}`" target="_blank" rel="noopener">খুলুন</a>
                </div>
            </div>

            <div v-if="!entitled" class="dash-note dash-lock" role="status">
                <strong>নিজস্ব ডোমেইন {{ planLabel }} প্যাকেজে পাওয়া যায়।</strong>
                <p>ফ্রি ঠিকানা এখনই চালু আছে। নিজের ডোমেইন যুক্ত করতে প্যাকেজ আপগ্রেড করুন।</p>
                <p v-if="domain">যুক্ত আছে: {{ domain.host }} — আপগ্রেড না করা পর্যন্ত এই ঠিকানায় স্টোর খুলবে না।</p>
                <RouterLink to="/dashboard/plans" class="btn btn--sm btn--primary">প্যাকেজ দেখুন</RouterLink>
            </div>

            <form v-if="!domain" class="dash-stack" @submit.prevent="entitled && add()">
                <label class="dash-field">
                    <span>নিজের ডোমেইন</span>
                    <input
                        v-model="input"
                        type="text"
                        inputmode="url"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        placeholder="mybrand.com"
                        :disabled="!entitled"
                    >
                </label>
                <p class="dash-muted">https:// বা শেষের পথ বাদ দিয়ে শুধু ডোমেইন লিখলেই হয়। যেমন mybrand.com।</p>
                <button type="submit" class="btn btn--primary" :disabled="!entitled || busy === 'add' || !input.trim()">
                    {{ busy === 'add' ? 'যোগ হচ্ছে…' : 'ডোমেইন যোগ করুন' }}
                </button>
            </form>

            <div v-else-if="entitled" class="dash-domainbar">
                <div class="dash-domainbar__main">
                    <div class="dash-hostline">
                        <strong>{{ domain.host }}</strong>
                        <span v-if="statusMeta" class="dash-badge" :class="statusMeta.badge">{{ statusMeta.label }}</span>
                    </div>
                    <p v-if="statusMeta" class="dash-muted">{{ statusMeta.text }}</p>
                    <p v-if="domain.checked_at" class="dash-muted">শেষ যাচাই: {{ when(domain.checked_at) }}</p>
                    <p v-if="domain.verified_at" class="dash-muted">সক্রিয় হয়েছে: {{ when(domain.verified_at) }}</p>
                </div>
                <div class="dash-domainbar__actions">
                    <button type="button" class="btn btn--secondary" :disabled="busy === 'check'" @click="check">
                        {{ busy === 'check' ? 'যাচাই হচ্ছে…' : 'আবার যাচাই করুন' }}
                    </button>
                    <button v-if="!confirmRemove" type="button" class="btn btn--danger" @click="confirmRemove = true">সরিয়ে দিন</button>
                    <template v-else>
                        <button type="button" class="btn btn--danger" :disabled="busy === 'remove'" @click="remove">
                            {{ busy === 'remove' ? 'সরানো হচ্ছে…' : 'হ্যাঁ, সরান' }}
                        </button>
                        <button type="button" class="btn btn--secondary" @click="confirmRemove = false">না</button>
                    </template>
                </div>
            </div>

            <p v-if="error" class="dash-note dash-note--bad" role="alert">{{ error }}</p>
        </section>

        <section class="dash-panel dash-stack">
            <div>
                <h2>কীভাবে ডোমেইন যুক্ত করবেন</h2>
                <p class="dash-example">
                    উদাহরণ: আপনি Namecheap বা GoDaddy থেকে <strong>mybrand.com</strong> কিনেছেন।
                    ডোমেইনের DNS পেজে গিয়ে নিচের দুটি রেকর্ড দিন।
                    <template v-if="domain && domain.host !== 'mybrand.com'">
                        আপনার ডোমেইন <strong>{{ domain.host }}</strong>-এর জন্যও রেকর্ড একই — শুধু নাম বদলাবে।
                    </template>
                </p>
            </div>

            <ol class="dash-steps">
                <li>
                    <strong>রুট ডোমেইন</strong> (<code>{{ exampleName }}</code>): টাইপ <strong>A</strong>, Host <strong>@</strong>, Value <strong>{{ info.server_ip }}</strong>।
                    Namecheap-এ Advanced DNS খুলে Host-এ <code>@</code> লিখুন। GoDaddy-তে DNS → Add → A, Name-এ <code>@</code> দিন।
                </li>
                <li>
                    <strong>www</strong> (<code>www.{{ exampleName }}</code>): টাইপ <strong>CNAME</strong>, Host <strong>www</strong>, Value <strong>{{ info.free_host }}</strong>।
                    শেষে স্ল্যাশ বা অতিরিক্ত ডট দেবেন না।
                </li>
                <li>
                    <code>@</code>-এর জন্য আগে থেকে থাকা <strong>A</strong> বা <strong>AAAA</strong> রেকর্ড মুছে দিন। পুরনো রেকর্ড থাকলে ক্রেতা অন্য সার্ভারে চলে যেতে পারে।
                </li>
            </ol>

            <p class="dash-muted">
                DNS ছড়িয়ে পড়তে কয়েক মিনিট, কখনো ২৪–৪৮ ঘণ্টা লাগে। রেকর্ড বসানোর পর «আবার যাচাই করুন» চাপুন।
                যাচাই হয়ে গেলে SSL সার্টিফিকেট স্বয়ংক্রিয়ভাবে সেট হবে — আলাদা করে সার্টিফিকেট কিনতে বা আপলোড করতে হবে না।
            </p>

            <div>
                <h3>DNS রেকর্ড</h3>
                <p class="dash-muted">এই মানগুলো কপি করে রেজিস্ট্রারের DNS পেজে বসান।</p>
                <div class="dash-tablewrap">
                    <table class="dash-dns">
                        <thead>
                            <tr>
                                <th>টাইপ</th>
                                <th>হোস্ট</th>
                                <th>মান</th>
                                <th><span class="sr-only">কপি</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in records" :key="row.type + row.host">
                                <td><span class="dash-dns__label">টাইপ</span>{{ row.type }}</td>
                                <td><span class="dash-dns__label">হোস্ট</span><code>{{ row.host }}</code></td>
                                <td><span class="dash-dns__label">মান</span><code>{{ row.value }}</code></td>
                                <td>
                                    <button
                                        type="button"
                                        class="dash-copy"
                                        :class="{ 'is-done': copied === row.type }"
                                        @click="copy(row.value, row.type)"
                                    >
                                        {{ copied === row.type ? 'কপি হয়েছে' : 'কপি' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </template>
</template>
