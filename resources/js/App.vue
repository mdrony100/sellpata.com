<template>
    <a class="skip-link" href="#main">মূল কনটেন্টে যান</a>
    <SiteHeader />
    <main id="main" class="site-main">
        <RouterView />
    </main>
    <SiteFooter />
    <MobileStickyCta />
    <CtaDialog />
</template>

<script setup>
import SiteHeader from './components/layout/SiteHeader.vue';
import SiteFooter from './components/layout/SiteFooter.vue';
import MobileStickyCta from './components/layout/MobileStickyCta.vue';
import CtaDialog from './components/layout/CtaDialog.vue';
</script>
