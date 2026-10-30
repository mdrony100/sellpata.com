<template>
    <div class="sticky-cta">
        <AppButton block size="md" @click="openCta('sticky-mobile')">{{ primaryCtaLabel }}</AppButton>
    </div>
</template>

<script setup>
import AppButton from '../ui/AppButton.vue';
import { primaryCtaLabel } from '../../data/site';
import { openCta } from '../../lib/cta';
</script>
