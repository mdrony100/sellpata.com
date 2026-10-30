<script setup>
defineProps({
    title: { type: String, required: true },
    text: { type: String, default: '' },
});
</script>

<template>
    <div class="dash-empty">
        <div class="dash-empty__icon" aria-hidden="true">
            <slot name="icon" />
        </div>
        <strong>{{ title }}</strong>
        <p v-if="text">{{ text }}</p>
        <div v-if="$slots.default" class="dash-empty__action">
            <slot />
        </div>
    </div>
</template>
