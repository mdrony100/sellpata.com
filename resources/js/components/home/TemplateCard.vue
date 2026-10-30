<template>
    <article class="template-card">
        <div class="template-card__frame">
            <TemplatePreview :template="template" :palette="template.palette" />
            <span class="template-card__badge">{{ sampleBadge }}</span>
        </div>

        <div class="template-card__meta">
            <div>
                <p class="template-card__brand">{{ template.brand }}</p>
                <p class="template-card__category">{{ categoryLabel(template.category) }}</p>
            </div>
            <button type="button" class="template-card__open" @click="$emit('preview', template)">
                প্রিভিউ দেখুন
            </button>
        </div>
    </article>
</template>

<script setup>
import TemplatePreview from './TemplatePreview.vue';
import { categoryLabel } from '../../data/templates';

defineProps({
    template: { type: Object, required: true },
    sampleBadge: { type: String, default: 'নমুনা ডিজাইন' },
});

defineEmits(['preview']);
</script>
