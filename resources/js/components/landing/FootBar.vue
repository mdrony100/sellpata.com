<template>
    <footer class="foot">
        <strong>{{ landing.content.brand?.name }}</strong>
        <p v-if="phone">{{ phone }}</p>
        <p v-if="returns">{{ returns }}</p>
        <p v-if="!hideBrand">
            এই পেজটি <a href="https://sellpata.com" rel="noopener">SellPata</a> দিয়ে তৈরি
        </p>
    </footer>
</template>

<script setup>
import { computed, inject } from 'vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
const phone = computed(() => String(landing.content.contact?.phone || '').trim());
const returns = computed(() => String(landing.content.policies?.returns || '').trim());
const hideBrand = computed(() => landing.checkout?.features?.remove_branding === true);
</script>
