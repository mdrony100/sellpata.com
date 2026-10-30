<template>
    <section class="final-cta" aria-labelledby="final-cta-title">
        <div class="container final-cta__inner">
            <div>
                <h2 id="final-cta-title" class="final-cta__title">{{ copy.heading }}</h2>
                <p class="final-cta__body">{{ copy.body }}</p>
            </div>
            <AppButton size="lg" @click="openCta('final-strip')">{{ copy.cta }}</AppButton>
        </div>
    </section>
</template>

<script setup>
import AppButton from '../ui/AppButton.vue';
import { finalCta as copy } from '../../data/site';
import { openCta } from '../../lib/cta';
</script>
