<template>
    <header class="section-heading" :class="{ 'section-heading--center': center }">
        <p v-if="eyebrow" class="section-heading__eyebrow">{{ eyebrow }}</p>
        <h2 class="section-heading__title"><slot name="title">{{ title }}</slot></h2>
        <p v-if="body || $slots.body" class="section-heading__body">
            <slot name="body">{{ body }}</slot>
        </p>
    </header>
</template>

<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, default: '' },
    body: { type: String, default: '' },
    center: { type: Boolean, default: false },
});
</script>
