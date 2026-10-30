<template>
    <section class="section notfound">
        <div class="container">
            <p class="eyebrow">৪০৪</p>
            <h1 class="page-hero__title">পেজটি খুঁজে পাওয়া যায়নি</h1>
            <p class="page-hero__body">
                লিংকটি ভুল হতে পারে, অথবা পেজটি সরানো হয়েছে। হোম পেজ থেকে আবার শুরু করুন।
            </p>
            <AppButton size="lg" :to="routes.home">হোম পেজে ফিরুন</AppButton>
        </div>
    </section>
</template>

<script setup>
import AppButton from '../components/ui/AppButton.vue';
import config from '../lib/config';

const routes = config.routes;
</script>
