<template>
    <article class="lp" :class="`lp--${design}`" :style="theme">
        <header class="lp__bar">
            <div class="lp__brand">
                <img v-if="logo" :src="logo" alt="" class="lp__logo-img" />
                <span v-else class="lp__logo">{{ content.brand?.name || 'আপনার ব্র্যান্ড' }}</span>
            </div>
            <span class="lp__phone">{{ content.contact?.phone || '০১৭XXXXXXXX' }}</span>
        </header>

        <section class="lp__hero">
            <div class="lp__hero-copy">
                <EditableField field="hero.badge" label="ব্যাজ" :editable="editable" as="div" @edit="$emit('edit', $event)">
                    <span class="lp__badge">{{ content.hero?.badge || 'নতুন' }}</span>
                </EditableField>

                <EditableField field="hero.headline" label="শিরোনাম" :editable="editable" @edit="$emit('edit', $event)">
                    <h1 class="lp__headline">{{ content.hero?.headline }}</h1>
                </EditableField>

                <EditableField field="hero.subline" label="সহায়ক লেখা" :editable="editable" @edit="$emit('edit', $event)">
                    <p class="lp__sub">{{ content.hero?.subline }}</p>
                </EditableField>

                <EditableField field="cta.label" label="বাটনের লেখা" :editable="editable" as="div" @edit="$emit('edit', $event)">
                    <button class="lp__cta" type="button" @click.prevent>{{ content.cta?.label || 'অর্ডার করুন' }}</button>
                </EditableField>

                <EditableField field="cta.delivery" label="ডেলিভারি তথ্য" :editable="editable" @edit="$emit('edit', $event)">
                    <p class="lp__delivery">{{ content.cta?.delivery }}</p>
                </EditableField>
            </div>

            <div class="lp__hero-media">
                <EditableField field="product.image" label="পণ্যের ছবি" :editable="editable" as="div" @edit="$emit('edit', $event)">
                    <div class="lp__shot">
                        <img v-if="productImage" :src="productImage" :alt="content.product?.name || ''" loading="lazy" />
                        <span v-else class="lp__shot-empty">পণ্যের ছবি</span>
                    </div>
                </EditableField>

                <div class="lp__price-card">
                    <EditableField field="product.name" label="পণ্যের নাম" :editable="editable" @edit="$emit('edit', $event)">
                        <p class="lp__product">{{ content.product?.name }}</p>
                    </EditableField>
                    <div class="lp__prices">
                        <EditableField field="product.sale_price" label="অফার দাম" :editable="editable" as="span" @edit="$emit('edit', $event)">
                            <span class="lp__sale">{{ money(content.product?.sale_price) }}</span>
                        </EditableField>
                        <EditableField field="product.price" label="নিয়মিত দাম" :editable="editable" as="span" @edit="$emit('edit', $event)">
                            <span class="lp__was">{{ money(content.product?.price) }}</span>
                        </EditableField>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp__story">
            <EditableField field="product.description" label="পণ্যের বিবরণ" :editable="editable" @edit="$emit('edit', $event)">
                <p class="lp__desc">{{ content.product?.description }}</p>
            </EditableField>
        </section>

        <section v-if="content.sections?.features !== false" class="lp__features">
            <h2 class="lp__h2">কেন আমাদের থেকে নেবেন</h2>
            <ul class="lp__feature-list">
                <li v-for="(feature, index) in content.features" :key="index">
                    <EditableField :field="`features.${index}`" label="সুবিধা" :editable="editable" @edit="$emit('edit', $event)">
                        <strong>{{ feature.title }}</strong>
                        <span>{{ feature.text }}</span>
                    </EditableField>
                </li>
            </ul>
        </section>

        <section v-if="content.sections?.faq !== false && content.faq?.length" class="lp__faq">
            <h2 class="lp__h2">সচরাচর প্রশ্ন</h2>
            <details v-for="(item, index) in content.faq" :key="index">
                <summary>{{ item.q }}</summary>
                <p>{{ item.a }}</p>
            </details>
        </section>

        <footer class="lp__footer">
            <p>{{ content.brand?.name }}</p>
            <span>{{ content.contact?.phone || '০১৭XXXXXXXX' }}</span>
        </footer>
    </article>
</template>

<script setup>
import { computed } from 'vue';
import EditableField from './EditableField.vue';

const props = defineProps({
    content: { type: Object, required: true },
    design: { type: String, default: 'spotlight' },
    media: { type: Object, default: () => ({}) },
    editable: { type: Boolean, default: false },
});

defineEmits(['edit']);

const theme = computed(() => ({
    '--lp-primary': props.content.brand?.color || '#0d3b2e',
    '--lp-accent': props.content.brand?.accent || '#c9f24d',
}));

const productImage = computed(() => props.media['product.image'] || props.content.product?.image || null);
const logo = computed(() => props.media['brand.logo'] || props.content.brand?.logo || null);

function money(value) {
    if (value === null || value === undefined || value === '') return '';

    const digits = String(value).replace(/[^0-9.]/g, '');
    const formatted = digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    return `৳${formatted}`;
}
</script>
