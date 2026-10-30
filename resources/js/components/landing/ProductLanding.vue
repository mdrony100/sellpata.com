<template>
    <div class="lp-root" :class="[`lp-root--${design}`, { 'is-ordering': ordering }]" :style="cssVars">
        <div class="lp-canvas">
            <div class="lp-frame">
                <BrandBar />
                <div class="lp-stack">
                    <template v-for="key in flow" :key="key">
                        <HeroBlock v-if="key === 'hero'" />
                        <BenefitsBlock v-else-if="key === 'benefits' && shown(key)" />
                        <DetailsBlock v-else-if="key === 'details' && shown(key)" />
                        <GalleryBlock v-else-if="key === 'gallery' && shown(key)" />
                        <VariantBlock v-else-if="key === 'selection' && shown(key)" />
                        <ReviewsBlock v-else-if="key === 'reviews' && shown(key)" />
                        <PoliciesBlock v-else-if="key === 'policies' && shown(key)" />
                        <FaqBlock v-else-if="key === 'faq' && shown(key)" />
                        <OrderBlock v-else-if="key === 'order'" />
                    </template>
                </div>
                <FootBar />
            </div>
        </div>

        <div v-if="showSticky" class="lp-sticky">
            <button type="button" @click="scrollToOrder">
                অর্ডার করুন
                <span>{{ formatMoney(totals.total) }}</span>
            </button>
        </div>

        <div v-if="lightbox" class="lp-light" role="dialog" aria-modal="true" aria-label="বড় ছবি" @keydown.esc="closeLightbox" @click.self="closeLightbox">
            <button ref="lightClose" type="button" class="lp-light__close" @click="closeLightbox">বন্ধ করুন</button>
            <button type="button" class="lp-light__nav" @click="stepLightbox(-1)" aria-label="আগের ছবি">‹</button>
            <img :src="lightbox.src" :alt="lightbox.alt || ''" />
            <button type="button" class="lp-light__nav" @click="stepLightbox(1)" aria-label="পরের ছবি">›</button>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, provide, reactive, ref, watch } from 'vue';
import BrandBar from './BrandBar.vue';
import HeroBlock from './HeroBlock.vue';
import BenefitsBlock from './BenefitsBlock.vue';
import DetailsBlock from './DetailsBlock.vue';
import GalleryBlock from './GalleryBlock.vue';
import VariantBlock from './VariantBlock.vue';
import ReviewsBlock from './ReviewsBlock.vue';
import PoliciesBlock from './PoliciesBlock.vue';
import FaqBlock from './FaqBlock.vue';
import OrderBlock from './OrderBlock.vue';
import FootBar from './FootBar.vue';
import {
    landingKey, buttonColors, deliveryAreas, formatMoney, isAvailable, parseMoney, sectionFlow, sectionShown, visibleReviews,
} from '../../landing/helpers';

const props = defineProps({
    content: { type: Object, required: true },
    design: { type: String, required: true },
    media: { type: Object, default: () => ({}) },
    editable: { type: Boolean, default: false },
    demo: { type: Boolean, default: false },
    checkout: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['edit']);

const FALLBACK = {
    prakritik: '#1f4d3a',
    abhijatya: '#2a2424',
    smart: '#10233f',
    komol: '#6e3d55',
    sorasari: '#242220',
};

const selectedVariantId = ref(null);
const selectedBundleId = ref(null);
const quantity = ref(1);
const sending = ref(false);
const error = ref('');
const done = ref(null);
const ordering = ref(false);
const lightbox = ref(null);
const lightIndex = ref(0);
const lightClose = ref(null);
let idempotency = '';
let observer;

const form = reactive({
    customer_name: '',
    customer_phone: '',
    address: '',
    area_id: '',
    note: '',
    payment_method: 'cod',
});

const areas = computed(() => deliveryAreas(props.content));
const mode = computed(() => props.content?.selection?.mode || 'simple');
const variants = computed(() => (props.content?.variants || []).filter((row) => row?.id));
const bundles = computed(() => (props.content?.bundles || []).filter((row) => row?.id));
const flow = computed(() => sectionFlow(props.content, props.design));
const reviews = computed(() => visibleReviews(props.content, { demo: props.demo, editable: props.editable }));
const payments = computed(() => {
    const list = (props.checkout?.payments || []).filter((method) => method && method.processes !== false);
    return list.length ? list : [{ id: 'cod', label: 'ক্যাশ অন ডেলিভারি', hint: 'পণ্য হাতে পেয়ে টাকা দেবেন। অর্ডার করলেই পরিশোধ হয় না।', processes: true }];
});

const selectedVariant = computed(() => variants.value.find((row) => String(row.id) === String(selectedVariantId.value)) || null);
const selectedBundle = computed(() => bundles.value.find((row) => String(row.id) === String(selectedBundleId.value)) || null);

const unitPrice = computed(() => {
    if (mode.value === 'variants' && selectedVariant.value) return parseMoney(selectedVariant.value.price);
    if (mode.value === 'bundles' && selectedBundle.value) return parseMoney(selectedBundle.value.price);
    const product = props.content?.product || {};
    return parseMoney(product.sale_price) || parseMoney(product.price);
});

const regularPrice = computed(() => {
    if (mode.value === 'variants' && selectedVariant.value) return parseMoney(selectedVariant.value.regular_price);
    if (mode.value === 'bundles' && selectedBundle.value) return parseMoney(selectedBundle.value.regular_price);
    return parseMoney(props.content?.product?.price);
});

const discount = computed(() => {
    const offer = props.content?.offer;
    if (!offer || offer.active === false || offer.active === 0 || offer.active === '0') return 0;
    if (!offer.active) return 0;
    const amount = parseMoney(offer.amount);
    const subtotal = unitPrice.value * Math.max(1, quantity.value);
    return amount > 0 && amount < subtotal ? amount : 0;
});

const deliveryCharge = computed(() => {
    if (areas.value.length) {
        const area = areas.value.find((item) => item.id === form.area_id);
        return area ? area.charge : 0;
    }
    return props.checkout?.delivery?.enabled ? Number(props.checkout.delivery.charge) || 0 : 0;
});

const totals = computed(() => {
    const subtotal = unitPrice.value * Math.max(1, Number(quantity.value) || 1);
    const delivery = deliveryCharge.value;
    return { subtotal, delivery, discount: discount.value, total: Math.max(0, subtotal - discount.value + delivery) };
});

const cssVars = computed(() => {
    const btn = buttonColors(props.content?.brand?.color, FALLBACK[props.design] || '#1f4d3a');
    const accent = /^#[0-9a-fA-F]{6}$/.test(props.content?.brand?.accent || '') ? props.content.brand.accent : '#c4843c';
    return { '--lp-brand': btn.bg, '--lp-brand-fg': btn.fg, '--lp-accent': accent };
});

const galleryItems = computed(() => (props.content?.gallery || [])
    .map((item, index) => ({
        src: src(`gallery.${index}.src`, item?.src),
        alt: item?.alt || props.content?.product?.name || '',
        ratio: item?.ratio || '1/1',
    }))
    .filter((item) => item.src));

const showSticky = computed(() => !ordering.value && !done.value && !lightbox.value);

function shown(key) {
    return sectionShown(props.content, key);
}

function src(path, fallback) {
    return props.media?.[path] || fallback || '';
}

function syncSelection() {
    const list = mode.value === 'bundles' ? bundles.value : variants.value;
    const current = mode.value === 'bundles' ? selectedBundleId.value : selectedVariantId.value;
    const still = list.find((row) => String(row.id) === String(current) && isAvailable(row));
    if (still) return;
    const first = list.find((row) => isAvailable(row));
    const id = first ? String(first.id) : null;
    if (mode.value === 'bundles') selectedBundleId.value = id;
    else selectedVariantId.value = id;
}

watch(() => [props.content?.variants, props.content?.bundles, props.content?.selection?.mode], syncSelection, { immediate: true, deep: true });

watch(payments, (list) => {
    if (!list.some((method) => method.id === form.payment_method)) {
        form.payment_method = list[0]?.id || 'cod';
    }
}, { immediate: true });

function scrollToOrder() {
    document.getElementById('order')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function setQuantity(value) {
    const next = Math.max(1, Math.min(99, Number(value) || 1));
    quantity.value = next;
}

function chooseVariant(id) {
    const row = variants.value.find((item) => String(item.id) === String(id));
    if (!row || !isAvailable(row)) return;
    selectedVariantId.value = String(id);
}

function chooseBundle(id) {
    const row = bundles.value.find((item) => String(item.id) === String(id));
    if (!row || !isAvailable(row)) return;
    selectedBundleId.value = String(id);
}

function openImage(index) {
    lightIndex.value = index;
    lightbox.value = galleryItems.value[index] || null;
    nextTick(() => lightClose.value?.focus());
}

function stepLightbox(delta) {
    if (!galleryItems.value.length) return;
    lightIndex.value = (lightIndex.value + delta + galleryItems.value.length) % galleryItems.value.length;
    lightbox.value = galleryItems.value[lightIndex.value];
}

function closeLightbox() {
    lightbox.value = null;
}

async function submitOrder() {
    error.value = '';
    if (props.demo || !props.checkout?.live) {
        error.value = props.demo
            ? 'এটি নমুনা পেজ। এখানে অর্ডার বা পেমেন্ট হয় না।'
            : 'প্রকাশের পর ক্রেতা এখান থেকে অর্ডার করবেন। এডিটরে অর্ডার জমা হয় না।';
        return;
    }
    if (!form.customer_name.trim() || !form.customer_phone.trim() || !form.address.trim()) {
        error.value = 'নাম, মোবাইল নম্বর ও সম্পূর্ণ ঠিকানা পূরণ করুন।';
        return;
    }
    form.customer_phone = form.customer_phone.replace(/[০-৯]/g, (digit) => String('০১২৩৪৫৬৭৮৯'.indexOf(digit))).replace(/[\s-]/g, '');
    if (!/^(\+?88)?0?1[3-9]\d{8}$/.test(form.customer_phone)) {
        error.value = 'সঠিক মোবাইল নম্বর লিখুন, যেমন ০১৭XXXXXXXX।';
        return;
    }
    if (areas.value.length && !form.area_id) {
        error.value = 'ডেলিভারি এলাকা বেছে নিন।';
        return;
    }
    if (mode.value === 'variants' && variants.value.length && !selectedVariant.value) {
        error.value = 'পণ্যের একটি অপশন বেছে নিন।';
        return;
    }
    if (mode.value === 'bundles' && bundles.value.length && !selectedBundle.value) {
        error.value = 'একটি প্যাকেজ বেছে নিন।';
        return;
    }

    if (!idempotency) idempotency = (crypto.randomUUID?.() || `sp-${Date.now()}`);
    sending.value = true;
    try {
        const response = await fetch(props.checkout.endpoint || '/checkout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Idempotency-Key': idempotency,
            },
            body: JSON.stringify({
                customer_name: form.customer_name,
                customer_phone: form.customer_phone,
                address: form.address,
                area_id: form.area_id || null,
                note: form.note,
                quantity: quantity.value,
                variant_id: mode.value === 'variants' ? selectedVariantId.value : null,
                bundle_id: mode.value === 'bundles' ? selectedBundleId.value : null,
                payment_method: form.payment_method,
            }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const first = data.errors ? Object.values(data.errors).flat()[0] : null;
            error.value = first || data.message || 'অর্ডারটি সম্পন্ন হয়নি। আবার চেষ্টা করুন।';
            return;
        }
        done.value = data;
        idempotency = '';
    } catch {
        error.value = 'ইন্টারনেট সংযোগ নেই। আবার চেষ্টা করুন।';
    } finally {
        sending.value = false;
    }
}

function resetForm() {
    done.value = null;
    error.value = '';
    form.customer_name = '';
    form.customer_phone = '';
    form.address = '';
    form.note = '';
    quantity.value = 1;
}

onMounted(() => {
    const target = document.getElementById('order');
    if (!target || typeof IntersectionObserver === 'undefined') return;
    observer = new IntersectionObserver(([entry]) => {
        ordering.value = entry.isIntersecting;
    }, { threshold: 0.2 });
    observer.observe(target);
});

onUnmounted(() => observer?.disconnect());

provide(landingKey, reactive({
    content: computed(() => props.content),
    design: computed(() => props.design),
    editable: computed(() => props.editable),
    demo: computed(() => props.demo),
    checkout: computed(() => props.checkout),
    mediaSrc: src,
    edit: (field) => emit('edit', field),
    areas,
    mode,
    variants,
    bundles,
    reviews,
    payments,
    form,
    quantity,
    selectedVariantId,
    selectedBundleId,
    selectedVariant,
    selectedBundle,
    unitPrice,
    regularPrice,
    totals,
    sending,
    error,
    done,
    galleryItems,
    formatMoney,
    scrollToOrder,
    setQuantity,
    chooseVariant,
    chooseBundle,
    openImage,
    submitOrder,
    resetForm,
}));
</script>
