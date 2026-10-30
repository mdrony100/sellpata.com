<template>
    <section class="block benefits">
        <EditableField field="headings.benefits" label="সুবিধার শিরোনাম" :editable="landing.editable" @edit="landing.edit">
            <h2>{{ landing.content.headings?.benefits || 'কেন এই পণ্যটি বেছে নেবেন?' }}</h2>
        </EditableField>
        <EditableField field="sections.benefits" label="সুবিধা সেকশন" :editable="landing.editable" as="div" @edit="landing.edit">
            <ul>
                <li v-for="(benefit, index) in items" :key="index">
                    <EditableField :field="`benefits`" label="সুবিধা" :editable="landing.editable" @edit="landing.edit">
                        <strong>{{ benefit.title }}</strong>
                        <span>{{ benefit.text }}</span>
                    </EditableField>
                </li>
            </ul>
        </EditableField>
        <CtaButton />
    </section>
</template>

<script setup>
import { computed, inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';
import CtaButton from './CtaButton.vue';

const landing = inject(landingKey);
const items = computed(() => (landing.content.benefits || []).slice(0, 6));
</script>
