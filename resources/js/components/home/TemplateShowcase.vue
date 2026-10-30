<template>
    <section id="templates" class="section showcase" aria-labelledby="showcase-title">
        <div class="container">
            <SectionHeading center eyebrow="টেমপ্লেট" :title="copy.heading" :body="copy.body">
                <template #title><span id="showcase-title">{{ copy.heading }}</span></template>
            </SectionHeading>

            <p class="showcase__count">{{ countLabel }}</p>

            <div class="filters" role="group" aria-label="ক্যাটাগরি অনুযায়ী ফিল্টার">
                <button
                    v-for="filter in filters"
                    :key="filter.id"
                    type="button"
                    class="filters__button"
                    :class="{ 'is-active': active === filter.id }"
                    :aria-pressed="active === filter.id ? 'true' : 'false'"
                    @click="active = filter.id"
                >
                    {{ filter.label }}
                </button>
            </div>

            <div v-if="visibleTemplates.length" class="template-grid">
                <TemplateCard
                    v-for="template in visibleTemplates"
                    :key="template.id"
                    :template="template"
                    :sample-badge="copy.sampleBadge"
                    @preview="openPreview"
                />
            </div>
            <p v-else class="showcase__empty">
                এই ক্যাটাগরির নমুনা ডিজাইন শিগগিরই যুক্ত হবে। এখন অন্য ক্যাটাগরি দেখে নিন।
            </p>

            <div class="showcase__cta">
                <AppButton size="lg" @click="openCta('showcase')">{{ copy.sectionCta }}</AppButton>
            </div>

            <ul class="benefit-grid">
                <li v-for="benefit in benefits" :key="benefit.key">
                    <BenefitCard
                        :name="benefit.key"
                        :title="benefit.title"
                        :body="benefit.body"
                        :coming-soon-label="comingSoonLabel(benefit)"
                    />
                </li>
            </ul>
        </div>

        <TemplatePreviewModal :template="previewTemplate" @close="previewTemplate = null" />
    </section>
</template>

<script setup>
import { computed, ref } from 'vue';
import AppButton from '../ui/AppButton.vue';
import SectionHeading from '../ui/SectionHeading.vue';
import BenefitCard from '../ui/BenefitCard.vue';
import TemplateCard from './TemplateCard.vue';
import TemplatePreviewModal from './TemplatePreviewModal.vue';
import { benefits, showcase as copy } from '../../data/site';
import { categories, templates } from '../../data/templates';
import config from '../../lib/config';
import { openCta } from '../../lib/cta';

const active = ref('all');
const previewTemplate = ref(null);

const filters = [{ id: 'all', label: copy.allLabel }, ...categories];

const visibleTemplates = computed(() =>
    active.value === 'all' ? templates : templates.filter((item) => item.category === active.value)
);

const countLabel = computed(() => config.templateCountLabel || copy.defaultCountLabel);

function comingSoonLabel(benefit) {
    if (!benefit.feature) return '';
    const feature = config.features[benefit.feature];
    return feature.available ? '' : feature.coming_soon_label;
}

function openPreview(template) {
    previewTemplate.value = template;
}
</script>
