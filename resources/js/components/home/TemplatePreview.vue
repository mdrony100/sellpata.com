<template>
    <div
        class="tpl"
        :style="{
            '--tpl-bg': palette.bg,
            '--tpl-surface': palette.surface,
            '--tpl-ink': palette.ink,
            '--tpl-primary': palette.primary,
            '--tpl-accent': palette.accent,
            '--tpl-soft': palette.soft,
        }"
        aria-hidden="true"
    >
        <div class="tpl__bar">
            <span class="tpl__brand">{{ template.brand }}</span>
            <span class="tpl__nav"><i /><i /><i /></span>
        </div>

        <div class="tpl__hero">
            <div class="tpl__copy">
                <span class="tpl__badge">{{ template.badge }}</span>
                <p class="tpl__headline">{{ template.headline }}</p>
                <p class="tpl__subline">{{ template.subline }}</p>
                <span class="tpl__cta">{{ template.cta }}</span>
            </div>
            <div class="tpl__media">
                <TemplateArt :kind="template.art" />
            </div>
        </div>

        <div class="tpl__price">
            <span class="tpl__price-now">{{ template.price }}</span>
            <span class="tpl__price-old">{{ template.oldPrice }}</span>
            <span class="tpl__price-cta">{{ template.cta }}</span>
        </div>

        <div class="tpl__thumbs">
            <span v-for="n in 3" :key="n" class="tpl__thumb"><TemplateArt :kind="template.art" /></span>
        </div>
    </div>
</template>

<script setup>
import TemplateArt from './TemplateArt.vue';

defineProps({
    template: { type: Object, required: true },
    palette: { type: Object, required: true },
});
</script>
