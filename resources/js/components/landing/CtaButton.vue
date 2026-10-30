<template>
    <p class="cta-wrap">
        <EditableField field="cta.label" label="বাটনের লেখা" :editable="landing.editable" as="span" @edit="landing.edit">
            <button type="button" class="lp-cta" @click="landing.scrollToOrder">
                {{ landing.content.cta?.label || 'অর্ডার করুন' }}
            </button>
        </EditableField>
    </p>
</template>

<script setup>
import { inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
</script>
