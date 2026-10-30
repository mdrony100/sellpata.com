<template>
    <p class="price">
        <EditableField field="product.sale_price" label="বিক্রয় মূল্য" :editable="landing.editable" as="span" @edit="landing.edit">
            <span class="price__now">{{ landing.formatMoney(landing.unitPrice) }}</span>
        </EditableField>
        <EditableField v-if="showWas" field="product.price" label="নিয়মিত দাম" :editable="landing.editable" as="span" @edit="landing.edit">
            <span class="price__was">নিয়মিত {{ landing.formatMoney(landing.regularPrice) }}</span>
        </EditableField>
    </p>
</template>

<script setup>
import { computed, inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
const showWas = computed(() => landing.regularPrice > landing.unitPrice && landing.regularPrice > 0);
</script>
