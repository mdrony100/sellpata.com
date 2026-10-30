<template>
    <article class="client-card">
        <div class="client-card__media">
            <img v-if="client.image" :src="client.image" :alt="`${client.brand} — ল্যান্ডিং পেজের প্রিভিউ`" loading="lazy" width="640" height="420" />
        </div>
        <div class="client-card__body">
            <h3 class="client-card__brand">{{ client.brand }}</h3>
            <p class="client-card__category">{{ categoryLabel(client.category) }}</p>
            <a v-if="client.url" class="client-card__link" :href="client.url" rel="noopener" target="_blank">
                লাইভ পেজ দেখুন → <span class="sr-only">(নতুন ট্যাবে খুলবে)</span>
            </a>
        </div>
    </article>
</template>

<script setup>
import { categoryLabel } from '../../data/templates';

defineProps({
    client: { type: Object, required: true },
});
</script>
