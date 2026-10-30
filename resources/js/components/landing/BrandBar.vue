<template>
    <header class="brandbar">
        <EditableField field="brand.name" label="দোকানের নাম" :editable="landing.editable" as="div" @edit="landing.edit">
            <div class="brandbar__name">
                <img v-if="logo" :src="logo" alt="" class="brandbar__logo" />
                <span>{{ landing.content.brand?.name || 'দোকান' }}</span>
            </div>
        </EditableField>
        <div class="brandbar__side">
            <EditableField field="contact.phone" label="যোগাযোগের নম্বর" :editable="landing.editable" as="div" @edit="landing.edit">
                <p v-if="phone" class="brandbar__phone">
                    <a :href="`tel:${phone}`">{{ phone }}</a>
                    <span v-if="verified" class="brandbar__ok">যাচাইকৃত</span>
                </p>
                <p v-else-if="landing.demo || landing.editable" class="brandbar__phone brandbar__phone--empty">নম্বর যোগ করা হয়নি</p>
            </EditableField>
            <EditableField field="contact.message" label="ছোট বার্তা" :editable="landing.editable" as="p" @edit="landing.edit">
                <span v-if="landing.content.contact?.message">{{ landing.content.contact.message }}</span>
            </EditableField>
        </div>
    </header>
</template>

<script setup>
import { computed, inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
const logo = computed(() => landing.mediaSrc('brand.logo', landing.content.brand?.logo));
const phone = computed(() => String(landing.content.contact?.phone || landing.checkout?.storePhone || '').trim());
const verified = computed(() => phone.value && (landing.content.contact?.verified === true || landing.content.contact?.verified === 1 || landing.content.contact?.verified === '1'));
</script>
