<template>
    <div class="dash-app">
        <div v-if="loading" class="dash-state">
            <p class="dash-brand__kicker">Sell<span>Pata</span></p>
            <p>লোড হচ্ছে…</p>
        </div>

        <div v-else-if="!me" class="dash-state">
            <p class="dash-brand__kicker">Sell<span>Pata</span></p>
            <h1>অনুগ্রহ করে আবার লগইন করুন</h1>
            <RouterLink to="/login" class="btn btn--primary">লগইন</RouterLink>
        </div>

        <div v-else-if="!me.store" class="dash-setup">
            <aside class="dash-setup__side">
                <a href="/" class="wordmark wordmark--light">Sell<span>Pata</span></a>
                <p>ঠিকানা বেছে নিলেই আপনার ল্যান্ডিং পেজ এই সাবডোমেইনে প্রকাশ করা যাবে।</p>
                <button type="button" class="dash-logout dash-logout--light" @click="logout">
                    <DashIcon name="logout" />
                    লগআউট
                </button>
            </aside>
            <main class="dash-setup__main">
                <form class="dash-panel dash-setup__card" @submit.prevent="createStore">
                    <p class="dash-kicker">নতুন স্টোর</p>
                    <h1>আপনার স্টোরের ঠিকানা বেছে নিন</h1>
                    <p class="dash-muted">এই ঠিকানাতেই আপনার ক্রেতারা আপনার ল্যান্ডিং পেজ দেখতে পাবে।</p>
                    <label class="dash-field">
                        <span>স্টোরের ঠিকানা</span>
                        <span class="dash-domain">
                            <input v-model="username" type="text" inputmode="latin" autocomplete="off" placeholder="organicfood" @input="checkUsername">
                            <em>.sellpata.com</em>
                        </span>
                    </label>
                    <p class="dash-preview">{{ username || 'yourbrand' }}.sellpata.com</p>
                    <p class="dash-muted">এই ঠিকানায় আপনার মূল ল্যান্ডিং পেজ দেখা যাবে।</p>
                    <p v-if="usernameError" class="dash-note dash-note--bad" role="alert">{{ usernameError }}</p>
                    <p v-else-if="usernameSaved" class="dash-note dash-note--ok">ঠিকানাটি পাওয়া গেছে।</p>
                    <button type="submit" class="btn btn--primary btn--lg" :disabled="!username || !!usernameError || savingStore">
                        {{ savingStore ? 'সংরক্ষণ হচ্ছে…' : 'স্টোর তৈরি করুন' }}
                    </button>
                    <p class="dash-muted">আপনি শুরু করছেন ফ্রি প্ল্যানে — কোনো পেমেন্ট বা কার্ড লাগবে না।</p>
                </form>
            </main>
        </div>

        <DashboardShell
            v-else
            :store="me.store"
            :user="me.user"
            :groups="navGroups"
            :active="tab"
            :title="pageTitle"
            :subtitle="pageSubtitle"
            :plan="me.store.plan"
            :pages-used="pagesUsed"
            :pages-limit="pagesLimitLabel"
            @logout="logout"
        >
            <template v-if="impersonation" #banner>
                <div class="dash-banner" role="status">
                    <span>আপনি গ্রাহক হিসেবে দেখছেন — সীমিত মোড।</span>
                    <button type="button" class="btn btn--sm btn--secondary" @click="stopImpersonation">অ্যাডমিনে ফিরে যান</button>
                </div>
            </template>

            <template #actions>
                <RouterLink v-if="tab === 'overview' || tab === 'pages'" to="/start" class="btn btn--primary">নতুন ল্যান্ডিং পেজ</RouterLink>
                <a v-if="tab === 'overview'" :href="me.store.url" target="_blank" rel="noopener" class="btn btn--secondary">স্টোর দেখুন</a>
                <a v-if="tab === 'orders' && me.store.features?.csv_export" href="/api/orders/export" class="btn btn--secondary">CSV এক্সপোর্ট</a>
                <a v-if="tab === 'downloads' && downloads.csv_export" href="/api/orders/export" class="btn btn--primary">অর্ডারের CSV এক্সপোর্ট</a>
                <button v-if="tab === 'profile'" type="submit" form="profile-form" class="btn btn--primary" :disabled="savingProfile">
                    {{ savingProfile ? 'সংরক্ষণ হচ্ছে…' : 'সংরক্ষণ করুন' }}
                </button>
            </template>

            <section v-if="tab === 'overview'" class="dash-stack">
                <div class="dash-stats">
                    <article v-for="card in statCards" :key="card.key" class="dash-stat">
                        <div class="dash-stat__icon" :class="`dash-stat__icon--${card.tone}`">
                            <DashIcon :name="card.icon" />
                        </div>
                        <p>{{ card.label }}</p>
                        <strong>{{ card.value }}</strong>
                    </article>
                </div>

                <div class="dash-split">
                    <section class="dash-panel">
                        <div class="dash-panel__head">
                            <h2>সাম্প্রতিক অর্ডার</h2>
                            <RouterLink to="/dashboard/orders" class="dash-textlink">সব অর্ডার</RouterLink>
                        </div>
                        <EmptyState
                            v-if="!recent.length"
                            title="এখনো কোনো অর্ডার আসেনি"
                            text="ল্যান্ডিং পেজ প্রকাশিত হলে ক্রেতার অর্ডার এখানে দেখা যাবে।"
                        >
                            <template #icon><DashIcon name="orders" /></template>
                            <a :href="me.store.url" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">স্টোর দেখুন</a>
                        </EmptyState>
                        <ul v-else class="dash-recent">
                            <li v-for="o in recent" :key="o.reference">
                                <div>
                                    <strong>{{ o.reference }}</strong>
                                    <span class="dash-muted dash-block">{{ o.customer_name }} · {{ date(o.created_at) }}</span>
                                </div>
                                <div class="dash-recent__meta">
                                    <span class="dash-badge" :class="statusClass(o.status)">{{ statusLabel(o.status) }}</span>
                                    <strong>৳ {{ fmt(o.total) }}</strong>
                                </div>
                            </li>
                        </ul>
                        <div v-if="recent.length" class="dash-tablewrap dash-wide">
                            <table class="dash-table">
                                <thead>
                                    <tr><th>অর্ডার</th><th>ক্রেতা</th><th>মোট</th><th>অবস্থা</th><th>তারিখ</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-for="o in recent" :key="o.reference">
                                        <td class="dash-strong">{{ o.reference }}</td>
                                        <td>{{ o.customer_name }}</td>
                                        <td>৳ {{ fmt(o.total) }}</td>
                                        <td><span class="dash-badge" :class="statusClass(o.status)">{{ statusLabel(o.status) }}</span></td>
                                        <td class="dash-muted">{{ date(o.created_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="dash-panel">
                        <div class="dash-panel__head">
                            <h2>করার কাজ</h2>
                        </div>
                        <div v-if="!reminders.length" class="dash-check dash-check--ok">
                            <DashIcon name="check" />
                            <div>
                                <strong>সব ঠিক আছে</strong>
                                <p>কোনো অমীমাংসিত কাজ নেই।</p>
                            </div>
                        </div>
                        <div v-for="(reminder, i) in reminders" :key="i" class="dash-check">
                            <DashIcon name="alert" />
                            <div>
                                <p>{{ reminder }}</p>
                                <RouterLink v-if="reminderLink(reminder)" :to="reminderLink(reminder).to" class="dash-textlink">
                                    {{ reminderLink(reminder).label }}
                                </RouterLink>
                            </div>
                        </div>
                    </section>
                </div>
            </section>

            <section v-else-if="tab === 'pages'" class="dash-stack">
                <p v-if="pageNotice" class="dash-note" role="status">{{ pageNotice }}</p>
                <div class="dash-usage">
                    <span>{{ pages.length }}/{{ pageLimit }} পেজ ব্যবহৃত</span>
                    <div class="dash-meter" role="presentation">
                        <span :style="{ width: pageMeter + '%' }" />
                    </div>
                </div>
                <EmptyState
                    v-if="!pages.length"
                    title="এখনো কোনো ল্যান্ডিং পেজ নেই"
                    text="একটি ডিজাইন বেছে নিয়ে আপনার প্রথম পেজ তৈরি করুন।"
                >
                    <template #icon><DashIcon name="pages" /></template>
                    <RouterLink to="/start" class="btn btn--primary">নতুন ল্যান্ডিং পেজ</RouterLink>
                </EmptyState>
                <div v-else class="dash-cards dash-cards--pages">
                    <article v-for="page in pages" :key="page.id" class="dash-card" :class="{ 'is-primary': page.is_primary }">
                        <div class="dash-thumb">
                            <img v-if="page.thumbnail" :src="page.thumbnail" :alt="page.title" loading="lazy">
                            <span v-else class="dash-thumb__ph"><DashIcon name="pages" /></span>
                        </div>
                        <div class="dash-card__body">
                            <h3>{{ page.title }}</h3>
                            <p v-if="page.template" class="dash-muted">{{ page.template }}</p>
                            <a v-if="page.url" :href="page.url" target="_blank" rel="noopener" class="dash-ellipsis">{{ prettyUrl(page.url) }}</a>
                            <p v-else class="dash-muted">এখনো প্রকাশিত হয়নি</p>
                            <p class="dash-tags">
                                <span class="dash-badge" :class="page.status === 'published' ? 'dash-badge--ok' : 'dash-badge--muted'">
                                    {{ page.status === 'published' ? 'প্রকাশিত' : 'ড্রাফট' }}
                                </span>
                                <span v-if="page.is_primary" class="dash-badge dash-badge--lime">মূল পেজ</span>
                            </p>
                            <div class="dash-card__actions">
                                <button type="button" class="btn btn--sm btn--primary" @click="togglePublish(page)">
                                    {{ page.status === 'published' ? 'প্রকাশ বন্ধ' : 'পরিবর্তন প্রকাশ করুন' }}
                                </button>
                                <a v-if="page.url" :href="page.url" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">দেখুন</a>
                                <button type="button" class="btn btn--sm btn--secondary" @click="editPage(page)">এডিট</button>
                                <button v-if="page.status === 'published' && !page.is_primary" type="button" class="btn btn--sm btn--secondary" @click="makePrimary(page)">মূল পেজ করুন</button>
                                <button type="button" class="btn btn--sm btn--secondary" @click="renamePage(page)">নাম/ঠিকানা</button>
                                <button type="button" class="btn btn--sm btn--secondary" @click="duplicatePage(page)">কপি</button>
                                <button type="button" class="btn btn--sm btn--danger" @click="deletePage(page)">মুছুন</button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section v-else-if="tab === 'orders'" class="dash-stack">
                <form class="dash-filters" @submit.prevent="searchOrders">
                    <label class="dash-field dash-field--grow">
                        <span class="sr-only">অর্ডার খুঁজুন</span>
                        <input v-model="filters.q" type="search" placeholder="অর্ডার নম্বর, মোবাইল বা নাম" aria-label="অর্ডার খুঁজুন">
                    </label>
                    <label class="dash-field">
                        <span class="sr-only">অবস্থা</span>
                        <select v-model="filters.status" aria-label="অবস্থা">
                            <option value="">সব অবস্থা</option>
                            <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                        </select>
                    </label>
                    <label class="dash-field">
                        <span class="sr-only">পেমেন্ট</span>
                        <select v-model="filters.payment_status" aria-label="পেমেন্ট">
                            <option value="">সব পেমেন্ট</option>
                            <option value="unpaid">বাকি</option>
                            <option value="paid">পরিশোধিত</option>
                            <option value="refunded">ফেরত</option>
                        </select>
                    </label>
                    <button type="submit" class="btn btn--primary">খুঁজুন</button>
                </form>

                <div class="dash-panel dash-panel--flush">
                    <EmptyState
                        v-if="!orders.length"
                        :title="ordersFiltered ? 'কোনো অর্ডার পাওয়া যায়নি' : 'এখনো কোনো অর্ডার আসেনি'"
                        :text="ordersFiltered ? 'অন্য শব্দ বা অবস্থা দিয়ে আবার খুঁজুন।' : 'ক্রেতা অর্ডার করলে সেগুলো এখানে সাজানো থাকবে।'"
                    >
                        <template #icon><DashIcon name="search" /></template>
                    </EmptyState>
                    <div v-else class="dash-orderlist">
                        <article v-for="o in orders" :key="`card-${o.id}`" class="dash-order">
                            <div class="dash-order__top">
                                <strong>{{ o.reference }}</strong>
                                <span>৳ {{ fmt(o.total) }}</span>
                            </div>
                            <p>{{ o.customer_name }} <span class="dash-muted">· {{ o.customer_phone }}</span></p>
                            <p class="dash-muted">{{ date(o.created_at) }}</p>
                            <label class="dash-field">
                                <span>অবস্থা</span>
                                <select class="dash-select" :value="o.status" aria-label="অর্ডারের অবস্থা" @change="updateOrder(o, { status: $event.target.value })">
                                    <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                                </select>
                            </label>
                            <label class="dash-field">
                                <span>পেমেন্ট</span>
                                <select class="dash-select" :value="o.payment_status" aria-label="পেমেন্টের অবস্থা" @change="updateOrder(o, { payment_status: $event.target.value })">
                                    <option value="unpaid">বাকি</option>
                                    <option value="paid">পরিশোধিত</option>
                                    <option value="refunded">ফেরত</option>
                                </select>
                            </label>
                            <a :href="`/api/orders/${o.id}/invoice`" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">ইনভয়েস</a>
                        </article>
                    </div>
                    <div v-if="orders.length" class="dash-tablewrap dash-wide">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>অর্ডার</th>
                                    <th>ক্রেতা</th>
                                    <th>মোট</th>
                                    <th>অবস্থা</th>
                                    <th>পেমেন্ট</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="o in orders" :key="o.id">
                                    <td>
                                        <span class="dash-strong">{{ o.reference }}</span>
                                        <span class="dash-muted dash-block">{{ date(o.created_at) }}</span>
                                    </td>
                                    <td>
                                        {{ o.customer_name }}
                                        <span class="dash-muted dash-block">{{ o.customer_phone }}</span>
                                    </td>
                                    <td>৳ {{ fmt(o.total) }}</td>
                                    <td>
                                        <span class="dash-status">
                                            <span class="dash-dot" :class="statusClass(o.status)" />
                                            <select class="dash-select" :value="o.status" aria-label="অর্ডারের অবস্থা" @change="updateOrder(o, { status: $event.target.value })">
                                                <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                                            </select>
                                        </span>
                                    </td>
                                    <td>
                                        <select class="dash-select" :value="o.payment_status" aria-label="পেমেন্টের অবস্থা" @change="updateOrder(o, { payment_status: $event.target.value })">
                                            <option value="unpaid">বাকি</option>
                                            <option value="paid">পরিশোধিত</option>
                                            <option value="refunded">ফেরত</option>
                                        </select>
                                    </td>
                                    <td>
                                        <a :href="`/api/orders/${o.id}/invoice`" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">ইনভয়েস</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="orderMeta.last_page > 1" class="dash-pager">
                        <button type="button" class="btn btn--sm btn--secondary" :disabled="orderMeta.current_page <= 1" @click="goOrderPage(orderMeta.current_page - 1)">আগের</button>
                        <span>{{ orderMeta.current_page }} / {{ orderMeta.last_page }}</span>
                        <button type="button" class="btn btn--sm btn--secondary" :disabled="orderMeta.current_page >= orderMeta.last_page" @click="goOrderPage(orderMeta.current_page + 1)">পরের</button>
                    </div>
                </div>
            </section>

            <section v-else-if="tab === 'plans'" class="dash-stack">
                <p class="dash-note">আপনার বর্তমান প্ল্যান: <strong>{{ me.store.plan }}</strong></p>
                <p v-if="planMessage" class="dash-note" role="status">{{ planMessage }}</p>
                <div v-if="downgrade" class="dash-panel">
                    <p><strong>{{ downgrade.message }}</strong></p>
                    <label v-for="page in downgrade.published" :key="page.id" class="dash-choice">
                        <input type="checkbox" :value="page.id" v-model="downgrade.selected">
                        <span>{{ page.title }}</span>
                        <span v-if="page.is_primary" class="dash-badge dash-badge--lime">মূল পেজ</span>
                    </label>
                    <button type="button" class="btn btn--sm btn--primary" :disabled="downgrade.selected.length !== downgrade.required" @click="confirmDowngrade">
                        সংরক্ষণ করুন ({{ downgrade.selected.length }}/{{ downgrade.required }})
                    </button>
                </div>
                <div class="dash-cards">
                    <article v-for="plan in plans" :key="plan.slug" class="dash-card dash-plan" :class="{ 'is-current': plan.current }">
                        <div class="dash-card__body">
                            <p class="dash-tags">
                                <span v-if="plan.current" class="dash-badge dash-badge--ok">চালু আছে</span>
                            </p>
                            <h3>{{ plan.name }}</h3>
                            <p class="dash-price" :class="{ 'dash-price--soft': !plan.purchasable && plan.slug !== 'free' }">
                                <template v-if="plan.purchasable">৳ {{ fmt(plan.price_monthly) }} <small>/ মাস</small></template>
                                <template v-else-if="plan.slug === 'free'">ফ্রি</template>
                                <template v-else>বিস্তারিত জানতে যোগাযোগ করুন</template>
                            </p>
                            <p class="dash-muted">{{ plan.max_pages }}টি ল্যান্ডিং পেজ</p>
                            <ul class="dash-feats">
                                <li v-for="(on, key) in plan.features" :key="key" :class="{ 'is-off': !on }">
                                    <DashIcon :name="on ? 'check' : 'close'" />
                                    <span>{{ featureLabel(key) }}</span>
                                </li>
                            </ul>
                            <button v-if="plan.current" type="button" class="btn btn--sm btn--secondary" disabled>চালু আছে</button>
                            <button v-else-if="plan.slug === 'free'" type="button" class="btn btn--sm btn--primary" @click="downgradeToFree(plan)">ফ্রি প্ল্যানে নামুন</button>
                            <button v-else type="button" class="btn btn--sm btn--primary" @click="requestPlan(plan)">
                                {{ plan.purchasable ? 'প্ল্যান নিন' : 'যোগাযোগ করুন' }}
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <section v-else-if="tab === 'billing'" class="dash-stack">
                <article v-if="subscription" class="dash-panel dash-sub">
                    <p class="dash-kicker">বর্তমান সাবস্ক্রিপশন</p>
                    <h2>{{ subscription.plan }}</h2>
                    <p class="dash-muted">
                        শুরু {{ date(subscription.started_at) }}
                        <template v-if="subscription.ends_at"> · শেষ {{ date(subscription.ends_at) }}</template>
                    </p>
                    <span class="dash-badge dash-badge--ok">{{ subStatus(subscription.status) }}</span>
                </article>
                <div class="dash-panel dash-panel--flush">
                    <div class="dash-panel__head dash-panel__head--pad">
                        <h2>ইনভয়েস</h2>
                    </div>
                    <EmptyState
                        v-if="!invoices.length"
                        title="এখনো কোনো ইনভয়েস তৈরি হয়নি"
                        text="প্যাকেজের পেমেন্ট হলে ইনভয়েস এখানে জমা হবে।"
                    >
                        <template #icon><DashIcon name="billing" /></template>
                        <RouterLink to="/dashboard/plans" class="btn btn--sm btn--secondary">প্যাকেজ দেখুন</RouterLink>
                    </EmptyState>
                    <div v-else class="dash-invoicelist">
                        <article v-for="invoice in invoices" :key="`bill-${invoice.id}`" class="dash-invoice">
                            <div class="dash-order__top">
                                <strong>{{ invoice.number }}</strong>
                                <span class="dash-badge" :class="invoice.status === 'paid' ? 'dash-badge--ok' : 'dash-badge--warn'">{{ invoice.status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}</span>
                            </div>
                            <p class="dash-muted">{{ date(invoice.issued_at) }} · ৳ {{ fmt(invoice.amount) }}</p>
                            <a :href="`/api/invoices/${invoice.id}/download`" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">ডাউনলোড</a>
                        </article>
                    </div>
                    <div v-if="invoices.length" class="dash-tablewrap dash-wide">
                        <table class="dash-table">
                            <thead>
                                <tr><th>ইনভয়েস</th><th>তারিখ</th><th>পরিমাণ</th><th>অবস্থা</th><th></th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in invoices" :key="invoice.id">
                                    <td class="dash-strong">{{ invoice.number }}</td>
                                    <td>{{ date(invoice.issued_at) }}</td>
                                    <td>৳ {{ fmt(invoice.amount) }}</td>
                                    <td><span class="dash-badge" :class="invoice.status === 'paid' ? 'dash-badge--ok' : 'dash-badge--warn'">{{ invoice.status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}</span></td>
                                    <td><a :href="`/api/invoices/${invoice.id}/download`" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">ডাউনলোড</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-else-if="tab === 'downloads'" class="dash-stack">
                <article class="dash-panel dash-export">
                    <div>
                        <h2>অর্ডার এক্সপোর্ট</h2>
                        <p class="dash-muted">প্ল্যানে থাকলে সব অর্ডার একসাথে CSV ফাইলে নামাতে পারবেন।</p>
                    </div>
                    <a v-if="downloads.csv_export" href="/api/orders/export" class="btn btn--primary">অর্ডারের CSV এক্সপোর্ট</a>
                    <p v-else class="dash-muted">CSV এক্সপোর্ট আপনার প্ল্যানে নেই। <RouterLink to="/dashboard/plans">প্যাকেজ দেখুন</RouterLink></p>
                </article>
                <div class="dash-panel dash-panel--flush">
                    <div class="dash-panel__head dash-panel__head--pad">
                        <h2>ইনভয়েস</h2>
                    </div>
                    <EmptyState
                        v-if="!downloads.invoices.length"
                        title="এখনো কোনো ইনভয়েস তৈরি হয়নি"
                        text="অর্ডার ও সাবস্ক্রিপশনের ইনভয়েস তৈরি হলে এখান থেকে ডাউনলোড করা যাবে।"
                    >
                        <template #icon><DashIcon name="download" /></template>
                    </EmptyState>
                    <div v-else class="dash-invoicelist">
                        <article v-for="invoice in downloads.invoices" :key="`dl-${invoice.id}`" class="dash-invoice">
                            <div class="dash-order__top">
                                <strong>{{ invoice.number }}</strong>
                                <span class="dash-badge" :class="invoice.status === 'paid' ? 'dash-badge--ok' : 'dash-badge--warn'">{{ invoice.status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}</span>
                            </div>
                            <p class="dash-muted">{{ invoice.type_label }} · {{ date(invoice.issued_at) }} · ৳ {{ fmt(invoice.amount) }}</p>
                            <a :href="`/api/invoices/${invoice.id}/download`" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">ডাউনলোড</a>
                        </article>
                    </div>
                    <div v-if="downloads.invoices.length" class="dash-tablewrap dash-wide">
                        <table class="dash-table">
                            <thead>
                                <tr><th>ইনভয়েস</th><th>ধরন</th><th>তারিখ</th><th>টাকা</th><th>অবস্থা</th><th></th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in downloads.invoices" :key="invoice.id">
                                    <td class="dash-strong">{{ invoice.number }}</td>
                                    <td>{{ invoice.type_label }}</td>
                                    <td>{{ date(invoice.issued_at) }}</td>
                                    <td>৳ {{ fmt(invoice.amount) }}</td>
                                    <td><span class="dash-badge" :class="invoice.status === 'paid' ? 'dash-badge--ok' : 'dash-badge--warn'">{{ invoice.status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}</span></td>
                                    <td><a :href="`/api/invoices/${invoice.id}/download`" target="_blank" rel="noopener" class="btn btn--sm btn--secondary">ডাউনলোড</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-else-if="tab === 'profile'" class="dash-stack">
                <p v-if="profileMessage" class="dash-note" :class="profileOk ? 'dash-note--ok' : 'dash-note--bad'" role="status">{{ profileMessage }}</p>
                <form id="profile-form" class="dash-profile" @submit.prevent="saveProfile">
                    <div class="dash-panel">
                        <h2>অ্যাকাউন্ট</h2>
                        <label class="dash-field"><span>আপনার নাম</span><input v-model="profile.name" type="text" autocomplete="name"></label>
                        <label class="dash-field"><span>ইমেইল (ঐচ্ছিক)</span><input v-model="profile.email" type="email" autocomplete="email"></label>
                        <div class="dash-field">
                            <span>লগইনের নম্বর: <strong>{{ me.user.phone }}</strong></span>
                            <div class="dash-inline">
                                <input v-model="phoneNew" type="tel" inputmode="tel" placeholder="নতুন নম্বর (যেমন 01712345678)">
                                <button type="button" class="btn btn--sm btn--secondary" :disabled="sendingPhone" @click="sendPhoneOtp">
                                    {{ sendingPhone ? 'পাঠানো হচ্ছে…' : 'কোড পাঠান' }}
                                </button>
                            </div>
                            <div v-if="phoneStage === 'code'" class="dash-inline">
                                <input v-model="phoneCode" type="text" inputmode="numeric" maxlength="6" placeholder="৬ ডিজিটের কোড">
                                <button type="button" class="btn btn--sm btn--primary" :disabled="verifyingPhone" @click="verifyPhoneOtp">
                                    {{ verifyingPhone ? 'যাচাই হচ্ছে…' : 'যাচাই করুন' }}
                                </button>
                            </div>
                            <p v-if="phoneOtpMessage" class="dash-muted">{{ phoneOtpMessage }}</p>
                        </div>
                    </div>
                    <div class="dash-panel">
                        <h2>স্টোর সেটিংস</h2>
                        <label class="dash-field"><span>স্টোরের নাম</span><input v-model="profile.store_name" type="text"></label>
                        <label class="dash-field"><span>যোগাযোগের নম্বর</span><input v-model="profile.contact_phone" type="tel" inputmode="tel"></label>
                        <label class="dash-field"><span>ডেলিভারি চার্জ (৳)</span><input v-model.number="profile.delivery_charge" type="number" min="0"></label>
                        <p class="dash-muted">স্টোরের ঠিকানা: {{ me.store.username }}.sellpata.com — প্রকাশের পর এটি নিজে বদলানো যায় না।</p>
                    </div>
                </form>
            </section>

            <section v-else class="dash-panel">
                <EmptyState title="এই পাতাটি পাওয়া যায়নি" text="মেনু থেকে একটি সেকশন বেছে নিন।">
                    <template #icon><DashIcon name="alert" /></template>
                    <RouterLink to="/dashboard" class="btn btn--primary">ড্যাশবোর্ড</RouterLink>
                </EmptyState>
            </section>
        </DashboardShell>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../lib/api';
import DashIcon from '../components/dashboard/DashIcon.vue';
import DashboardShell from '../components/dashboard/DashboardShell.vue';
import EmptyState from '../components/dashboard/EmptyState.vue';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const me = ref(null);
const overview = ref({});
const pages = ref([]);
const pageLimit = ref(1);
const orders = ref([]);
const orderPage = ref(1);
const orderMeta = ref({ current_page: 1, last_page: 1, total: 0 });
const statuses = ref(['new', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned']);
const plans = ref([]);
const invoices = ref([]);
const downloads = ref({ invoices: [], csv_export: false });
const phoneNew = ref('');
const phoneCode = ref('');
const phoneStage = ref('idle');
const phoneOtpMessage = ref('');
const sendingPhone = ref(false);
const verifyingPhone = ref(false);
const subscription = ref(null);

const username = ref('');
const usernameError = ref('');
const usernameSaved = ref(false);
const savingStore = ref(false);
const pageNotice = ref('');
const planMessage = ref('');
const downgrade = ref(null);
const profileMessage = ref('');
const profileOk = ref(true);
const savingProfile = ref(false);
const filters = reactive({ q: '', status: '', payment_status: '' });
const profile = reactive({ name: '', email: '', store_name: '', contact_phone: '', delivery_charge: 0 });

const impersonation = computed(() => me.value?.impersonation ?? null);
const metrics = computed(() => overview.value.metrics ?? {});
const recent = computed(() => overview.value.recent ?? []);
const reminders = computed(() => overview.value.reminders ?? []);
const usage = computed(() => metrics.value);
const tab = computed(() => route.params.tab || 'overview');
const pagesUsed = computed(() => usage.value.pages_used ?? 0);
const pagesLimitLabel = computed(() => usage.value.pages_limit ?? me.value?.store?.max_pages ?? null);
const ordersFiltered = computed(() => !!(filters.q || filters.status || filters.payment_status));
const pageMeter = computed(() => {
    const limit = Number(pageLimit.value) || 0;
    if (!limit) return 0;
    return Math.min(100, Math.round((pages.value.length / limit) * 100));
});

const sections = [
    { key: 'overview', label: 'ড্যাশবোর্ড', icon: 'grid', title: 'ড্যাশবোর্ড', subtitle: 'স্টোরের অর্ডার, পেজ ও আজকের কাজ এক নজরে।' },
    { key: 'pages', label: 'ল্যান্ডিং পেজ', icon: 'pages', title: 'ল্যান্ডিং পেজ', subtitle: 'প্রকাশিত ও ড্রাফট পেজ সাজান, সম্পাদনা করুন বা কপি করুন।' },
    { key: 'orders', label: 'অর্ডার', icon: 'orders', title: 'অর্ডার', subtitle: 'ক্রেতার অর্ডার খুঁজুন এবং অবস্থা বা পেমেন্ট আপডেট করুন।' },
    { key: 'plans', label: 'প্যাকেজ', icon: 'plans', title: 'প্যাকেজ', subtitle: 'আপনার বর্তমান প্ল্যান এবং অন্যান্য প্যাকেজের সুবিধা।' },
    { key: 'profile', label: 'প্রোফাইল', icon: 'profile', title: 'প্রোফাইল', subtitle: 'স্টোরের নাম, যোগাযোগ ও ডেলিভারি চার্জ আপডেট করুন।' },
    { key: 'downloads', label: 'ডাউনলোড', icon: 'download', title: 'ডাউনলোড', subtitle: 'ইনভয়েস ও অর্ডারের এক্সপোর্ট ফাইল এক জায়গায়।' },
    { key: 'billing', label: 'লেনদেন ও ইনভয়েস', icon: 'billing', title: 'লেনদেন ও ইনভয়েস', subtitle: 'সাবস্ক্রিপশন এবং পেমেন্টের ইনভয়েস।' },
];

const navGroups = [
    { label: '', items: sections.filter((item) => item.key === 'overview') },
    { label: 'স্টোর', items: sections.filter((item) => ['pages', 'orders'].includes(item.key)) },
    { label: 'অ্যাকাউন্ট', items: sections.filter((item) => ['plans', 'profile', 'downloads', 'billing'].includes(item.key)) },
];

const currentSection = computed(() => sections.find((item) => item.key === tab.value));
const pageTitle = computed(() => currentSection.value?.title ?? 'পাতা পাওয়া যায়নি');
const pageSubtitle = computed(() => {
    if (!currentSection.value) return 'মেনু থেকে একটি সেকশন বেছে নিন।';
    if (tab.value === 'orders' && orderMeta.value.total) {
        return `${currentSection.value.subtitle} মোট ${orderMeta.value.total}টি।`;
    }
    return currentSection.value.subtitle;
});

const statCards = computed(() => [
    { key: 'total', label: 'মোট অর্ডার', value: metrics.value.orders_total ?? 0, icon: 'orders', tone: 'brand' },
    { key: 'new', label: 'নতুন', value: metrics.value.orders_new ?? 0, icon: 'plus', tone: 'amber' },
    { key: 'confirmed', label: 'কনফার্মড', value: metrics.value.orders_confirmed ?? 0, icon: 'check', tone: 'brand' },
    { key: 'delivered', label: 'ডেলিভারি হয়েছে', value: metrics.value.orders_delivered ?? 0, icon: 'box', tone: 'ok' },
    { key: 'cancelled', label: 'বাতিল', value: metrics.value.orders_cancelled ?? 0, icon: 'close', tone: 'bad' },
    { key: 'value', label: 'ডেলিভারি হওয়া অর্ডারের মূল্য', value: `৳ ${fmt(metrics.value.delivered_value)}`, icon: 'billing', tone: 'lime' },
]);

const fmt = (n) => Number(n || 0).toLocaleString('en-IN');
const date = (iso) => (iso ? new Date(iso).toLocaleDateString('bn-BD') : '');
const statusLabel = (s) => ({
    new: 'নতুন', confirmed: 'কনফার্মড', processing: 'প্রস্তুত হচ্ছে', shipped: 'পাঠানো হয়েছে',
    delivered: 'ডেলিভারি হয়েছে', cancelled: 'বাতিল', returned: 'ফেরত',
}[s] ?? s);
const statusClass = (s) => ({
    new: 'dash-badge--warn',
    confirmed: 'dash-badge--brand',
    processing: 'dash-badge--brand',
    shipped: 'dash-badge--brand',
    delivered: 'dash-badge--ok',
    cancelled: 'dash-badge--bad',
    returned: 'dash-badge--muted',
}[s] ?? 'dash-badge--muted');
const featureLabel = (key) => ({
    cod: 'ক্যাশ অন ডেলিভারি', subdomain: 'SellPata সাবডোমেইন', invoice_pdf: 'ইনভয়েস PDF',
    online_payment: 'অনলাইন পেমেন্ট', courier: 'কুরিয়ার ইন্টিগ্রেশন', meta_pixel: 'মেটা পিক্সেল',
    ga4: 'GA4', custom_domain: 'নিজস্ব ডোমেইন', remove_branding: 'SellPata ব্র্যান্ডিং ছাড়াই',
    csv_export: 'CSV এক্সপোর্ট', advanced_analytics: 'অ্যাডভান্সড অ্যানালিটিক্স',
}[key] ?? key);
const subStatus = (s) => ({ active: 'সক্রিয়', cancelled: 'বাতিল', expired: 'মেয়াদোত্তীর্ণ' }[s] ?? s);
const prettyUrl = (url) => String(url).replace(/^https?:\/\//, '');

function reminderLink(text) {
    if (String(text).includes('ল্যান্ডিং')) return { to: '/dashboard/pages', label: 'পেজ দেখুন' };
    if (String(text).includes('যোগাযোগ')) return { to: '/dashboard/profile', label: 'নম্বর যোগ করুন' };
    return null;
}

function syncPageNotice() {
    if (tab.value !== 'pages') return;
    const notice = route.query.notice;
    if (typeof notice === 'string' && notice !== '') {
        pageNotice.value = notice;
    } else if (route.query.published) {
        pageNotice.value = 'পেজটি প্রকাশিত হয়েছে।';
    } else if (route.query.created) {
        pageNotice.value = 'নতুন ল্যান্ডিং পেজ তৈরি হয়েছে।';
    }
}

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
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
        if (value) params.set(key, value);
    });
    if (orderPage.value > 1) params.set('page', String(orderPage.value));
    const data = await api(`/api/orders?${params}`);
    orders.value = data.data;
    statuses.value = data.statuses;
    orderMeta.value = data.meta ?? { current_page: 1, last_page: 1, total: data.data?.length ?? 0 };
}

function searchOrders() {
    orderPage.value = 1;
    return loadOrders();
}

function goOrderPage(page) {
    orderPage.value = page;
    return loadOrders();
}

async function loadPlans() {
    plans.value = (await api('/api/plans')).plans;
}

async function loadBilling() {
    const data = await api('/api/invoices');
    invoices.value = data.invoices;
    subscription.value = data.subscription;
}

async function loadDownloads() {
    try {
        downloads.value = await api('/api/downloads');
    } catch (error) {
        downloads.value = { invoices: [], csv_export: false };
    }
}

async function refreshTab() {
    if (!me.value?.store) return;
    const jobs = [];
    if (tab.value === 'overview' || !overview.value.metrics) jobs.push(loadOverview());
    if (tab.value === 'pages') jobs.push(loadPages());
    if (tab.value === 'orders') jobs.push(loadOrders());
    if (tab.value === 'plans') jobs.push(loadPlans());
    if (tab.value === 'billing') jobs.push(loadBilling());
    if (tab.value === 'downloads') jobs.push(loadDownloads());
    await Promise.all(jobs);
}

onMounted(async () => {
    try {
        await loadMe();
        syncPageNotice();
        await refreshTab();
    } catch (error) {
        if (!me.value) me.value = null;
    } finally {
        loading.value = false;
    }
});

watch(tab, () => {
    syncPageNotice();
    refreshTab();
});

watch(() => [route.query.notice, route.query.published, route.query.created], syncPageNotice);

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

function applyDowngradeChoice(data, plan) {
    downgrade.value = {
        ...data,
        planSlug: plan.slug,
        selected: (data.published ?? []).filter((p) => p.is_primary).map((p) => p.id),
    };
}

async function downgradeToFree(plan) {
    planMessage.value = '';
    try {
        const data = await api(`/api/plans/${plan.slug}/downgrade`, { method: 'POST', body: {} });
        if (data.ok) {
            planMessage.value = data.message ?? 'ফ্রি প্ল্যানে নামানো হয়েছে।';
            downgrade.value = null;
            await loadMe();
            await loadPages();
            return;
        }
        if (data.code === 'choose_pages') {
            applyDowngradeChoice(data, plan);
            return;
        }
        planMessage.value = data.message ?? '';
    } catch (error) {
        const data = error.data ?? {};
        if (data.code === 'choose_pages') {
            applyDowngradeChoice(data, plan);
            return;
        }
        planMessage.value = data.message ?? 'প্ল্যান পরিবর্তন হয়নি।';
    }
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
        profileOk.value = true;
        profileMessage.value = 'সংরক্ষণ হয়েছে।';
        await loadMe();
    } catch (error) {
        profileOk.value = false;
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
