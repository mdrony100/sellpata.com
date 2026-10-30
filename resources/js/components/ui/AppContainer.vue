<template>
    <div class="container" :class="{ 'container--narrow': narrow }">
        <slot />
    </div>
</template>

<script setup>
defineProps({
    narrow: { type: Boolean, default: false },
});
</script>
