<template>
    <div class="dash">
        <div v-if="impersonation" class="dash__banner" role="status">
            <span>আপনি গ্রাহক হিসেবে দেখছেন — সীমিত মোড।</span>
            <button type="button" class="btn btn--sm btn--secondary" @click="stopImpersonation">অ্যাডমিনে ফিরে যান</button>
        </div>

        <div v-if="loading" class="dash__state">লোড হচ্ছে…</div>

        <div v-else-if="!me" class="dash__state">অনুগ্রহ করে আবার লগইন করুন।</div>

        <!-- First-run setup: a store address is mandatory before publishing -->
        <section v-else-if="!me.store" class="dash__setup">
            <h1>আপনার স্টোরের ঠিকানা বেছে নিন</h1>
            <p>এই ঠিকানাতেই আপনার ক্রেতারা আপনার ল্যান্ডিং পেজ দেখতে পাবে।</p>
            <label class="dash__field">
                <span>স্টোরের ঠিকানা</span>
                <input v-model="username" type="text" inputmode="latin" autocomplete="off" placeholder="organicfood" @input="checkUsername">
            </label>
            <p class="dash__preview">{{ username || 'yourbrand' }}.sellpata.com</p>
            <p class="dash__muted">এই ঠিকানায় আপনার মূল ল্যান্ডিং পেজ দেখা যাবে।</p>
            <p v-if="usernameError" class="dash__error">{{ usernameError }}</p>
            <p v-else-if="usernameSaved" class="dash__ok">ঠিকানাটি পাওয়া গেছে।</p>
            <button type="button" class="btn btn--primary btn--lg" :disabled="!username || !!usernameError || savingStore" @click="createStore">
                {{ savingStore ? 'সংরক্ষণ হচ্ছে…' : 'স্টোর তৈরি করুন' }}
            </button>
            <p class="dash__muted">আপনি শুরু করছেন ফ্রি প্ল্যানে — কোনো পেমেন্ট বা কার্ড লাগবে না।</p>
        </section>

        <template v-else>
            <header class="dash__top">
                <div>
                    <p class="dash__label">স্টোর</p>
                    <h1>{{ me.store.name }}</h1>
                    <a :href="me.store.url" target="_blank" rel="noopener" class="dash__link">{{ me.store.username }}.sellpata.com</a>
                </div>
                <div class="dash__plan">
                    <span class="dash__badge">{{ me.store.plan }}</span>
                    <span class="dash__muted">{{ usage.pages_used ?? 0 }}/{{ usage.pages_limit ?? me.store.max_pages }} পেজ</span>
                </div>
            </header>

            <nav class="dash__tabs">
                <RouterLink v-for="item in tabs" :key="item.key" :to="`/dashboard/${item.key === 'overview' ? '' : item.key}`" class="dash__tab">
                    {{ item.label }}
                </RouterLink>
                <button type="button" class="dash__tab dash__tab--out" @click="logout">লগআউট</button>
            </nav>

            <section v-if="tab === 'overview'" class="dash__body">
                <div class="dash__metrics">
                    <div class="dash__metric"><p>মোট অর্ডার</p><strong>{{ metrics.orders_total ?? 0 }}</strong></div>
                    <div class="dash__metric"><p>নতুন</p><strong>{{ metrics.orders_new ?? 0 }}</strong></div>
                    <div class="dash__metric"><p>কনফার্মড</p><strong>{{ metrics.orders_confirmed ?? 0 }}</strong></div>
                    <div class="dash__metric"><p>ডেলিভারি হয়েছে</p><strong>{{ metrics.orders_delivered ?? 0 }}</strong></div>
                    <div class="dash__metric"><p>বাতিল</p><strong>{{ metrics.orders_cancelled ?? 0 }}</strong></div>
                    <div class="dash__metric"><p>ডেলিভারি হওয়া অর্ডারের মূল্য</p><strong>৳ {{ fmt(metrics.delivered_value) }}</strong></div>
                </div>

                <div v-if="reminders.length" class="dash__reminders">
                    <p v-for="(r, i) in reminders" :key="i">• {{ r }}</p>
                </div>

                <div class="dash__actions">
                    <RouterLink to="/start" class="btn btn--primary">নতুন ল্যান্ডিং পেজ</RouterLink>
                    <a :href="me.store.url" target="_blank" rel="noopener" class="btn btn--secondary">স্টোর দেখুন</a>
                </div>

                <h2>সাম্প্রতিক অর্ডার</h2>
                <p v-if="!recent.length" class="dash__muted">এখনো কোনো অর্ডার আসেনি।</p>
                <table v-else class="dash__table">
                    <thead><tr><th>অর্ডার</th><th>ক্রেতা</th><th>মোট</th><th>অবস্থা</th></tr></thead>
                    <tbody>
                        <tr v-for="o in recent" :key="o.reference">
                            <td>{{ o.reference }}</td><td>{{ o.customer_name }}</td>
                            <td>৳ {{ fmt(o.total) }}</td><td>{{ statusLabel(o.status) }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section v-else-if="tab === 'pages'" class="dash__body">
                <div class="dash__actions">
                    <RouterLink to="/start" class="btn btn--primary">নতুন ল্যান্ডিং পেজ</RouterLink>
                    <span class="dash__muted">{{ pages.length }}/{{ pageLimit }} পেজ ব্যবহৃত</span>
                </div>
                <p v-if="pageNotice" class="dash__notice">{{ pageNotice }}</p>
                <div class="dash__cards">
                    <article v-for="page in pages" :key="page.id" class="dash__card">
                        <div class="dash__thumb">
                            <img v-if="page.thumbnail" :src="page.thumbnail" :alt="page.title" loading="lazy">
                        </div>
                        <div class="dash__card-body">
                            <h3>{{ page.title }}</h3>
                            <p class="dash__muted">{{ page.url || 'এখনো প্রকাশিত হয়নি' }}</p>
                            <p class="dash__tags">
                                <span class="dash__badge" :class="{ 'dash__badge--draft': page.status !== 'published' }">
                                    {{ page.status === 'published' ? 'প্রকাশিত' : 'ড্রাফট' }}
                                </span>
                                <span v-if="page.is_primary" class="dash__badge dash__badge--primary">মূল পেজ</span>
                            </p>
                            <div class="dash__card-actions">
                                <button type="button" class="btn btn--sm btn--primary" @click="togglePublish(page)">
                                    {{ page.status === 'published' ? 'প্রকাশ বন্ধ' : 'পরিবর্তন প্রকাশ করুন' }}
                                </button>
                                <button v-if="page.status === 'published' && !page.is_primary" type="button" class="btn btn--sm btn--ghost" @click="makePrimary(page)">মূল পেজ করুন</button>
                                <a v-if="page.url" :href="page.url" target="_blank" rel="noopener" class="btn btn--sm btn--ghost">দেখুন</a>
                                <button type="button" class="btn btn--sm btn--ghost" @click="editPage(page)">এডিট</button>
                                <button type="button" class="btn btn--sm btn--ghost" @click="renamePage(page)">নাম/ঠিকানা</button>
                                <button type="button" class="btn btn--sm btn--ghost" @click="duplicatePage(page)">কপি</button>
                                <button type="button" class="btn btn--sm btn--ghost dash__danger" @click="deletePage(page)">মুছুন</button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section v-else-if="tab === 'orders'" class="dash__body">
                <form class="dash__filters" @submit.prevent="loadOrders">
                    <input v-model="filters.q" type="search" placeholder="অর্ডার নম্বর, মোবাইল বা নাম">
                    <select v-model="filters.status">
                        <option value="">সব অবস্থা</option>
                        <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                    </select>
                    <select v-model="filters.payment_status">
                        <option value="">সব পেমেন্ট</option>
                        <option value="unpaid">বাকি</option>
                        <option value="paid">পরিশোধিত</option>
                        <option value="refunded">ফেরত</option>
                    </select>
                    <button type="submit" class="btn btn--sm btn--primary">খুঁজুন</button>
                    <a v-if="me.store.features?.csv_export" href="/api/orders/export" class="btn btn--sm btn--ghost">CSV এক্সপোর্ট</a>
                </form>

                <p v-if="!orders.length" class="dash__muted">কোনো অর্ডার পাওয়া যায়নি।</p>
                <table v-else class="dash__table">
                    <thead><tr><th>অর্ডার</th><th>ক্রেতা</th><th>মোট</th><th>অবস্থা</th><th>পেমেন্ট</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="o in orders" :key="o.id">
                            <td>{{ o.reference }}</td>
                            <td>{{ o.customer_name }}<br><small>{{ o.customer_phone }}</small></td>
                            <td>৳ {{ fmt(o.total) }}</td>
                            <td>
                                <select :value="o.status" @change="updateOrder(o, { status: $event.target.value })">
                                    <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                                </select>
                            </td>
                            <td>
                                <select :value="o.payment_status" @change="updateOrder(o, { payment_status: $event.target.value })">
                                    <option value="unpaid">বাকি</option>
                                    <option value="paid">পরিশোধিত</option>
                                    <option value="refunded">ফেরত</option>
                                </select>
                            </td>
                            <td><a :href="`/api/orders/${o.id}/invoice`" target="_blank" rel="noopener">ইনভয়েস</a></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section v-else-if="tab === 'plans'" class="dash__body">
                <p class="dash__muted">আপনার বর্তমান প্ল্যান: <strong>{{ me.store.plan }}</strong></p>
                <div class="dash__cards">
                    <article v-for="plan in plans" :key="plan.slug" class="dash__card dash__card--plan">
                        <h3>{{ plan.name }}</h3>
                        <p class="dash__price">
                            <template v-if="plan.purchasable">৳ {{ fmt(plan.price_monthly) }} / মাস</template>
                            <template v-else-if="plan.slug === 'free'">ফ্রি</template>
                            <template v-else>বিস্তারিত জানতে যোগাযোগ করুন</template>
                        </p>
                        <p class="dash__muted">{{ plan.max_pages }}টি ল্যান্ডিং পেজ</p>
                        <ul class="dash__feats">
                            <li v-for="(on, key) in plan.features" :key="key" :class="{ off: !on }">{{ featureLabel(key) }}</li>
                        </ul>
                        <button v-if="plan.current" type="button" class="btn btn--sm btn--ghost" disabled>চালু আছে</button>
                        <button v-else-if="plan.slug === 'free'" type="button" class="btn btn--sm btn--primary" @click="downgradeToFree(plan)">ফ্রি প্ল্যানে নামুন</button>
                        <button v-else type="button" class="btn btn--sm btn--primary" @click="requestPlan(plan)">
                            {{ plan.purchasable ? 'প্ল্যান নিন' : 'যোগাযোগ করুন' }}
                        </button>
                    </article>
                </div>
                <p v-if="planMessage" class="dash__notice">{{ planMessage }}</p>

                <div v-if="downgrade" class="dash__notice">
                    <p><strong>{{ downgrade.message }}</strong></p>
                    <label v-for="page in downgrade.published" :key="page.id" style="display:flex;gap:6px;align-items:center">
                        <input type="checkbox" :value="page.id" v-model="downgrade.selected">
                        {{ page.title }} <span v-if="page.is_primary" class="dash__badge">মূল পেজ</span>
                    </label>
                    <button type="button" class="btn btn--sm btn--primary" :disabled="downgrade.selected.length !== downgrade.required" @click="confirmDowngrade">
                        সংরক্ষণ করুন ({{ downgrade.selected.length }}/{{ downgrade.required }})
                    </button>
                </div>
            </section>

            <section v-else-if="tab === 'billing'" class="dash__body">
                <h2>লেনদেন ও ইনভয়েস</h2>
                <p v-if="subscription" class="dash__muted">
                    {{ subscription.plan }} · শুরু {{ date(subscription.started_at) }}
                    <template v-if="subscription.ends_at"> · শেষ {{ date(subscription.ends_at) }}</template>
                </p>
                <p v-if="!invoices.length" class="dash__muted">এখনো কোনো ইনভয়েস তৈরি হয়নি।</p>
                <table v-else class="dash__table">
                    <thead><tr><th>ইনভয়েস</th><th>তারিখ</th><th>পরিমাণ</th><th>অবস্থা</th><th></th></tr></thead>
                    <tbody>
                        <tr v-for="i in invoices" :key="i.id">
                            <td>{{ i.number }}</td><td>{{ date(i.issued_at) }}</td>
                            <td>৳ {{ fmt(i.amount) }}</td>
                            <td>{{ i.status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}</td>
                            <td><a :href="`/api/invoices/${i.id}/download`" target="_blank" rel="noopener">ডাউনলোড</a></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section v-else-if="tab === 'profile'" class="dash__body dash__profile">
                <h2>প্রোফাইল ও স্টোর সেটিংস</h2>
                <form @submit.prevent="saveProfile">
                    <label class="dash__field"><span>আপনার নাম</span><input v-model="profile.name" type="text"></label>
                    <label class="dash__field"><span>ইমেইল (ঐচ্ছিক)</span><input v-model="profile.email" type="email"></label>
                    <label class="dash__field"><span>স্টোরের নাম</span><input v-model="profile.store_name" type="text"></label>
                    <label class="dash__field"><span>যোগাযোগের নম্বর</span><input v-model="profile.contact_phone" type="tel" inputmode="tel"></label>
                    <label class="dash__field"><span>ডেলিভারি চার্জ (৳)</span><input v-model.number="profile.delivery_charge" type="number" min="0"></label>
                    <p class="dash__muted">লগইনের নম্বর: {{ me.user.phone }} — নম্বর বদলাতে সহায়তা টিমের সঙ্গে যোগাযোগ করুন (OTP যাচাই প্রয়োজন)।</p>
                    <p class="dash__muted">স্টোরের ঠিকানা: {{ me.store.username }}.sellpata.com — প্রকাশের পর এটি নিজে বদলানো যায় না।</p>
                    <button type="submit" class="btn btn--primary" :disabled="savingProfile">{{ savingProfile ? 'সংরক্ষণ হচ্ছে…' : 'সংরক্ষণ করুন' }}</button>
                    <p v-if="profileMessage" class="dash__ok">{{ profileMessage }}</p>
                </form>
            </section>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../lib/api';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const me = ref(null);
const overview = ref({});
const pages = ref([]);
const pageLimit = ref(1);
const orders = ref([]);
const statuses = ref(['new', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned']);
const plans = ref([]);
const invoices = ref([]);
const subscription = ref(null);

const username = ref('');
const usernameError = ref('');
const usernameSaved = ref(false);
const savingStore = ref(false);
const pageNotice = ref('');
const planMessage = ref('');
const downgrade = ref(null);
const profileMessage = ref('');
const savingProfile = ref(false);
const filters = reactive({ q: '', status: '', payment_status: '' });
const profile = reactive({ name: '', email: '', store_name: '', contact_phone: '', delivery_charge: 0 });

const impersonation = computed(() => me.value?.impersonation ?? null);
const metrics = computed(() => overview.value.metrics ?? {});
const recent = computed(() => overview.value.recent ?? []);
const reminders = computed(() => overview.value.reminders ?? []);
const usage = computed(() => metrics.value);
const tab = computed(() => route.params.tab || 'overview');

const tabs = [
    { key: 'overview', label: 'ড্যাশবোর্ড' },
    { key: 'pages', label: 'ল্যান্ডিং পেজ' },
    { key: 'orders', label: 'অর্ডার' },
    { key: 'plans', label: 'প্যাকেজ' },
    { key: 'profile', label: 'প্রোফাইল' },
    { key: 'billing', label: 'লেনদেন ও ইনভয়েস' },
];

const fmt = (n) => Number(n || 0).toLocaleString('en-IN');
const date = (iso) => (iso ? new Date(iso).toLocaleDateString('bn-BD') : '');
const statusLabel = (s) => ({
    new: 'নতুন', confirmed: 'কনফার্মড', processing: 'প্রস্তুত হচ্ছে', shipped: 'পাঠানো হয়েছে',
    delivered: 'ডেলিভারি হয়েছে', cancelled: 'বাতিল', returned: 'ফেরত',
}[s] ?? s);
const featureLabel = (key) => ({
    cod: 'ক্যাশ অন ডেলিভারি', subdomain: 'SellPata সাবডোমেইন', invoice_pdf: 'ইনভয়েস PDF',
    online_payment: 'অনলাইন পেমেন্ট', courier: 'কুরিয়ার ইন্টিগ্রেশন', meta_pixel: 'মেটা পিক্সেল',
    ga4: 'GA4', custom_domain: 'নিজস্ব ডোমেইন', remove_branding: 'SellPata ব্র্যান্ডিং ছাড়াই',
    csv_export: 'CSV এক্সপোর্ট', advanced_analytics: 'অ্যাডভান্সড অ্যানালিটিক্স',
}[key] ?? key);

async function loadMe() {
    me.value = await api('/api/me');
    if (me.value.store) {
        Object.assign(profile, {
            name: me.value.user.name,
            email: me.value.user.email ?? '',
            store_name: me.value.store.name,
            contact_phone: me.value.store.contact_phone ?? '',
            delivery_charge: me.value.store.delivery_charge ?? 0,
        });
    }
}

async function loadOverview() {
    overview.value = await api('/api/dashboard');
}

async function loadPages() {
    const data = await api('/api/pages');
    pages.value = data.pages;
    pageLimit.value = data.limit;
}

async function loadOrders() {
    const params = new URLSearchParams(Object.entries(filters).filter(([, v]) => v));
    const data = await api(`/api/orders?${params}`);
    orders.value = data.data;
    statuses.value = data.statuses;
}

async function loadPlans() {
    plans.value = (await api('/api/plans')).plans;
}

async function loadBilling() {
    const data = await api('/api/invoices');
    invoices.value = data.invoices;
    subscription.value = data.subscription;
}

async function refreshTab() {
    if (!me.value?.store) return;
    if (tab.value === 'overview') await loadOverview();
    if (tab.value === 'pages') await loadPages();
    if (tab.value === 'orders') await loadOrders();
    if (tab.value === 'plans') await loadPlans();
    if (tab.value === 'billing') await loadBilling();
}

onMounted(async () => {
    await loadMe();
    await refreshTab();
    loading.value = false;
});

watch(tab, refreshTab);

let usernameTimer;
function checkUsername() {
    usernameSaved.value = false;
    clearTimeout(usernameTimer);
    usernameTimer = setTimeout(async () => {
        const data = await api(`/api/store/username?username=${encodeURIComponent(username.value)}`);
        usernameError.value = data.error ?? '';
        usernameSaved.value = !data.error && username.value.trim() !== '';
    }, 250);
}

async function createStore() {
    savingStore.value = true;
    try {
        await api('/api/store', { method: 'POST', body: { username: username.value, display_name: profile.store_name || null } });
        await loadMe();
        await router.push('/dashboard/pages');
    } catch (error) {
        usernameError.value = error.data?.message ?? 'স্টোর তৈরি হয়নি। আবার চেষ্টা করুন।';
    } finally {
        savingStore.value = false;
    }
}

async function togglePublish(page) {
    pageNotice.value = '';
    try {
        if (page.status === 'published') {
            await api(`/api/pages/${page.id}/unpublish`, { method: 'POST' });
        } else {
            const data = await api(`/api/pages/${page.id}/publish`, { method: 'POST' });
            pageNotice.value = `প্রকাশিত: ${data.url}`;
        }
        await loadPages();
    } catch (error) {
        pageNotice.value = error.data?.message ?? 'কাজটি সম্পন্ন হয়নি।';
    }
}

async function makePrimary(page) {
    await api(`/api/pages/${page.id}/primary`, { method: 'POST' });
    await loadPages();
}

async function editPage(page) {
    try {
        const data = await api(`/api/pages/${page.id}/edit`, { method: 'POST' });
        await router.push(`/builder/${data.public_id}?mode=page`);
    } catch (error) {
        pageNotice.value = error.data?.message ?? 'এডিটর খোলা যায়নি।';
    }
}

async function duplicatePage(page) {
    try {
        await api(`/api/pages/${page.id}/duplicate`, { method: 'POST' });
        await loadPages();
    } catch (error) {
        pageNotice.value = error.data?.message ?? 'কপি করা যায়নি।';
    }
}

async function deletePage(page) {
    if (!confirm('পেজটি মুছে ফেলতে চান?')) return;
    try {
        await api(`/api/pages/${page.id}`, { method: 'DELETE' });
        await loadPages();
    } catch (error) {
        pageNotice.value = error.data?.message ?? 'মুছে ফেলা যায়নি।';
    }
}

async function renamePage(page) {
    const title = prompt('পেজের নাম', page.title);
    if (title === null) return;
    const slug = prompt('URL ঠিকানা (ইংরেজি ছোট হাতের অক্ষর)', page.slug);
    if (slug === null) return;
    try {
        await api(`/api/pages/${page.id}`, { method: 'PATCH', body: { title, slug } });
        await loadPages();
    } catch (error) {
        pageNotice.value = error.data?.message ?? 'নাম পরিবর্তন হয়নি।';
    }
}

async function updateOrder(order, patch) {
    await api(`/api/orders/${order.id}`, { method: 'PATCH', body: patch });
    await loadOrders();
}

async function downgradeToFree(plan) {
    planMessage.value = '';
    const data = await api(`/api/plans/${plan.slug}/downgrade`, { method: 'POST', body: {} });
    if (data.ok) {
        planMessage.value = data.message ?? 'ফ্রি প্ল্যানে নামানো হয়েছে।';
        downgrade.value = null;
        await loadMe();
        await loadPages();
        return;
    }
    if (data.code === 'choose_pages') {
        downgrade.value = { ...data, planSlug: plan.slug, selected: data.published.filter((p) => p.is_primary).map((p) => p.id) };
        return;
    }
    planMessage.value = data.message ?? '';
}

async function confirmDowngrade() {
    const data = await api(`/api/plans/${downgrade.value.planSlug}/downgrade`, {
        method: 'POST',
        body: { page_ids: downgrade.value.selected },
    });
    planMessage.value = data.message ?? '';
    downgrade.value = null;
    await loadMe();
    await loadPages();
}

async function requestPlan(plan) {
    const data = await api(`/api/plans/${plan.slug}/request`, { method: 'POST' });
    planMessage.value = data.message ?? '';
}

async function saveProfile() {
    savingProfile.value = true;
    try {
        await api('/api/profile', { method: 'PATCH', body: profile });
        profileMessage.value = 'সংরক্ষণ হয়েছে।';
        await loadMe();
    } catch (error) {
        profileMessage.value = error.data?.message ?? 'সংরক্ষণ হয়নি।';
    } finally {
        savingProfile.value = false;
    }
}

async function stopImpersonation() {
    await api('/api/impersonation/stop', { method: 'POST' });
    window.location.assign('/admin');
}

async function logout() {
    window.location.assign('/admin/logout');
}
</script>
