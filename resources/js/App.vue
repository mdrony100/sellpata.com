<template>
    <a class="skip-link" href="#main">মূল কনটেন্টে যান</a>
    <SiteHeader v-if="!isBare" />
    <main id="main" class="site-main">
        <RouterView />
    </main>
    <SiteFooter v-if="!isBare" />
    <MobileStickyCta v-if="!isBare" />
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const isBare = computed(() => route.meta?.bare === true);
import SiteHeader from './components/layout/SiteHeader.vue';
import SiteFooter from './components/layout/SiteFooter.vue';
import MobileStickyCta from './components/layout/MobileStickyCta.vue';
</script>
