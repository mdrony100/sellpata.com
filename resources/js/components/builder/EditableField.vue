<template>
    <component :is="as" class="lp-field" :class="{ 'lp-field--editable': editable }">
        <slot />
        <button
            v-if="editable"
            type="button"
            class="lp-field__pencil"
            :aria-label="`${label} সম্পাদনা করুন`"
            @click.stop="$emit('edit', field)"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M4 20h4l10-10-4-4L4 16v4Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                <path d="M14 6l4 4" fill="none" stroke="currentColor" stroke-width="1.8" />
            </svg>
        </button>
    </component>
</template>

<script setup>
defineProps({
    field: { type: String, required: true },
    label: { type: String, default: '' },
    editable: { type: Boolean, default: false },
    as: { type: String, default: 'div' },
});

defineEmits(['edit']);
</script>
