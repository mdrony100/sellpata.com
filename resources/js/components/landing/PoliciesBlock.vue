<template>
    <section class="block policies">
        <h2>{{ landing.content.headings?.policies || 'ডেলিভারি ও নীতিমালা' }}</h2>
        <ul>
            <li v-if="landing.areas.length">
                <strong>এলাকা ও চার্জ</strong>
                <span v-for="area in landing.areas" :key="area.id">{{ area.name }} — {{ landing.formatMoney(area.charge) }}</span>
            </li>
            <li v-else-if="landing.checkout?.delivery?.enabled">
                <strong>ডেলিভারি চার্জ</strong>
                <span>{{ landing.formatMoney(landing.checkout.delivery.charge || 0) }}</span>
            </li>
            <li v-if="eta">
                <strong>সম্ভাব্য সময়</strong>
                <span>{{ eta }}</span>
            </li>
            <li>
                <strong>পেমেন্ট</strong>
                <span v-for="method in landing.payments" :key="method.id">{{ method.label }}. {{ method.hint }}</span>
            </li>
            <li v-if="landing.checkout?.courier">
                <strong>কুরিয়ার</strong>
                <span>এই প্ল্যানে কুরিয়ার সংযোগ চালু। ডেলিভারি এলাকার চার্জ আলাদা।</span>
            </li>
            <li>
                <strong>পরিবর্তন বা ফেরত</strong>
                <EditableField field="policies.returns" label="ফেরতের নিয়ম" :editable="landing.editable" as="span" @edit="landing.edit">
                    {{ returns }}
                </EditableField>
            </li>
            <li v-if="support">
                <strong>সাপোর্ট</strong>
                <span>{{ support }}</span>
            </li>
        </ul>
    </section>
</template>

<script setup>
import { computed, inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
const eta = computed(() => String(landing.content.delivery?.eta || '').trim());
const support = computed(() => String(landing.content.policies?.support || '').trim());
const returns = computed(() => String(landing.content.policies?.returns || '').trim() || 'বিক্রেতা আলাদা কোনো পরিবর্তন বা ফেরতের নিয়ম যোগ করেননি।');
</script>
