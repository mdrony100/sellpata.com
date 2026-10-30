<template>
    <div class="sf">
        <TemplateRender :design="payload.design" :content="payload.content" :checkout="checkoutPayload" />

        <section v-if="!isLanding" class="sf__order" id="order">
            <div class="sf__order-inner">
                <h2>অর্ডার করতে ফর্মটি পূরণ করুন</h2>
                <p class="sf__muted">ক্যাশ অন ডেলিভারি — পণ্য হাতে পেয়ে টাকা পরিশোধ করুন।</p>

                <div v-if="done" class="sf__done" role="status">
                    <h3>ধন্যবাদ, অর্ডারটি পেয়েছি</h3>
                    <p>আপনার অর্ডার নম্বর: <strong>{{ done.reference }}</strong></p>
                    <p>সর্বমোট: <strong>৳ {{ fmt(done.total) }}</strong></p>
                    <p class="sf__muted">স্টোর শীঘ্রই আপনার নম্বরে যোগাযোগ করবে।</p>
                    <button type="button" class="btn btn--secondary" @click="resetForm">আরেকটি অর্ডার করুন</button>
                </div>

                <form v-else class="sf__form" novalidate @submit.prevent="submitOrder">
                    <label>আপনার নাম<input v-model="form.customer_name" type="text" autocomplete="name" required></label>
                    <label>মোবাইল নম্বর<input v-model="form.customer_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" required></label>
                    <label>সম্পূর্ণ ঠিকানা<textarea v-model="form.address" rows="3" required></textarea></label>
                    <div class="sf__row">
                        <label>জেলা<input v-model="form.city" type="text"></label>
                        <label>থানা / এলাকা<input v-model="form.area" type="text"></label>
                    </div>
                    <div class="sf__row">
                        <label>পরিমাণ<input v-model.number="form.quantity" type="number" min="1" max="99" inputmode="numeric"></label>
                        <label>নোট (ঐচ্ছিক)<input v-model="form.note" type="text"></label>
                    </div>

                    <dl class="sf__totals">
                        <div><dt>পণ্যের দাম</dt><dd>৳ {{ fmt(totals.subtotal) }}</dd></div>
                        <div v-if="payload.delivery.enabled"><dt>ডেলিভারি চার্জ</dt><dd>৳ {{ fmt(totals.delivery) }}</dd></div>
                        <div class="sf__grand"><dt>সর্বমোট</dt><dd>৳ {{ fmt(totals.total) }}</dd></div>
                    </dl>

                    <p v-if="error" class="sf__error" role="alert">{{ error }}</p>

                    <button type="submit" class="btn btn--primary btn--block" :disabled="sending">
                        {{ sending ? 'পাঠানো হচ্ছে…' : 'অর্ডার কনফার্ম করুন' }}
                    </button>
                </form>
            </div>
        </section>

        <footer v-if="!isLanding && !payload.features?.remove_branding" class="sf__foot">
            <span>{{ payload.store.name }}</span>
            <a href="https://sellpata.com" rel="noopener">SellPata দিয়ে তৈরি</a>
        </footer>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import TemplateRender from '../components/builder/TemplateRender.vue';
import { isLandingDesign } from '../landing/helpers';

const props = defineProps({ payload: { type: Object, required: true } });
const isLanding = computed(() => isLandingDesign(props.payload.design));
const checkoutPayload = computed(() => ({
    live: true,
    endpoint: props.payload.order_endpoint,
    payments: props.payload.checkout?.payments || [],
    courier: props.payload.checkout?.courier === true,
    tracking: props.payload.checkout?.tracking || {},
    delivery: props.payload.delivery,
    features: props.payload.features || {},
    storePhone: props.payload.store?.phone || '',
}));

onMounted(() => installTracking(props.payload.checkout?.tracking));

const bengaliDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
const fmt = (n) => Number(n || 0).toLocaleString('en-IN');
const toNumber = (value) => Number(String(value ?? '').replace(/[^\d]/g, '')) || 0;

const form = reactive({ customer_name: '', customer_phone: '', address: '', city: '', area: '', note: '', quantity: 1 });
const sending = ref(false);
const error = ref('');
const done = ref(null);

const unitPrice = computed(() => {
    const product = props.payload.content.product ?? {};
    return toNumber(product.sale_price) || toNumber(product.price);
});
const totals = computed(() => {
    const subtotal = unitPrice.value * Math.max(1, Number(form.quantity) || 1);
    const delivery = props.payload.delivery.enabled ? Number(props.payload.delivery.charge) : 0;
    return { subtotal, delivery, total: subtotal + delivery };
});

async function submitOrder() {
    error.value = '';
    if (!form.customer_name.trim() || !form.customer_phone.trim() || !form.address.trim()) {
        error.value = 'নাম, মোবাইল নম্বর ও ঠিকানা পূরণ করুন।';
        return;
    }
    if (!/^(\+?88)?0?1[3-9]\d{8}$/.test(form.customer_phone.replace(/[\s-]/g, ''))) {
        error.value = 'সঠিক মোবাইল নম্বর লিখুন, যেমন ০১৭XXXXXXXX।';
        return;
    }

    sending.value = true;
    try {
        const response = await fetch(props.payload.order_endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify(form),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            error.value = data.message || 'অর্ডারটি সম্পন্ন হয়নি। আবার চেষ্টা করুন।';
            return;
        }
        done.value = { reference: data.reference, total: data.total };
    } catch {
        error.value = 'ইন্টারনেট সংযোগ নেই। আবার চেষ্টা করুন।';
    } finally {
        sending.value = false;
    }
}

function resetForm() {
    done.value = null;
    Object.assign(form, { customer_name: '', customer_phone: '', address: '', city: '', area: '', note: '', quantity: 1 });
}

function installTracking(tracking) {
    const meta = tracking?.meta;
    const ga = tracking?.ga4;
    if (meta && !document.getElementById('sp-meta')) {
        const script = document.createElement('script');
        script.id = 'sp-meta';
        script.src = 'https://connect.facebook.net/en_US/fbevents.js';
        script.async = true;
        script.onload = () => {
            window.fbq?.('init', meta);
            window.fbq?.('track', 'PageView');
        };
        const boot = document.createElement('script');
        boot.text = 'window.fbq=window.fbq||function(){(window.fbq.q=window.fbq.q||[]).push(arguments)};window.fbq.l=+new Date;';
        document.head.append(boot, script);
    }
    if (ga && !document.getElementById('sp-ga')) {
        const script = document.createElement('script');
        script.id = 'sp-ga';
        script.async = true;
        script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga)}`;
        const boot = document.createElement('script');
        boot.text = `window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',${JSON.stringify(ga)});`;
        document.head.append(script, boot);
    }
}
</script>
