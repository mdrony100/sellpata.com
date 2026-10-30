<template>
    <section v-if="items.length" class="block faq">
        <EditableField field="headings.faq" label="প্রশ্নোত্তরের শিরোনাম" :editable="landing.editable" @edit="landing.edit">
            <h2>{{ landing.content.headings?.faq || 'সচরাচর প্রশ্ন' }}</h2>
        </EditableField>
        <EditableField field="faq" label="প্রশ্নোত্তর" :editable="landing.editable" as="div" @edit="landing.edit">
            <div>
                <details v-for="(item, index) in items" :key="index">
                    <summary>{{ item.q }}</summary>
                    <p>{{ answer(item) }}</p>
                </details>
            </div>
        </EditableField>
    </section>
</template>

<script setup>
import { computed, inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { faqAnswer, landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
const items = computed(() => (landing.content.faq || []).filter((item) => item?.q));

function answer(item) {
    return faqAnswer(item, {
        areas: landing.areas,
        payments: landing.payments,
        policies: landing.content.policies,
        fallbackDelivery: landing.checkout?.delivery,
    });
}
</script>
