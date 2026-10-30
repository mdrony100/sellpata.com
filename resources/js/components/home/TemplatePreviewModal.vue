<template>
    <AppModal :open="Boolean(template)" :title="template ? template.brand : 'নমুনা ডিজাইন'" @close="$emit('close')">
        <template v-if="template">
            <p class="preview-modal__note">
                এটি একটি নমুনা ডিজাইন—আপনার পণ্য ও ব্র্যান্ডের রঙ দিয়ে এভাবেই সাজানো হবে।
            </p>
            <div class="preview-modal__stage">
                <TemplatePreview :template="template" :palette="template.palette" />
            </div>
        </template>
        <template #actions>
            <AppButton @click="onCta">{{ sampleSectionCta }}</AppButton>
            <AppButton variant="ghost" @click="$emit('close')">বন্ধ করুন</AppButton>
        </template>
    </AppModal>
</template>

<script setup>
import AppModal from '../ui/AppModal.vue';
import AppButton from '../ui/AppButton.vue';
import TemplatePreview from './TemplatePreview.vue';
import { showcase } from '../../data/site';
import { openCta } from '../../lib/cta';

defineProps({
    template: { type: Object, default: null },
});

const emit = defineEmits(['close']);
const sampleSectionCta = showcase.sectionCta;

function onCta() {
    emit('close');
    openCta('template-preview');
}
</script>
