<template>
    <component
        :is="tag"
        v-bind="{ ...tagProps, ...attrs }"
        :type="tag === 'button' ? type : undefined"
        class="btn"
        :class="[`btn--${variant}`, `btn--${size}`, { 'btn--block': block }]"
        @click="$emit('click', $event)"
    >
        <span class="btn__label"><slot /></span>
        <span v-if="variant === 'link'" class="btn__arrow" aria-hidden="true">→</span>
    </component>
</template>

<script setup>
import { computed, useAttrs } from 'vue';
import { RouterLink } from 'vue-router';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    variant: { type: String, default: 'primary' }, // primary | secondary | ghost | link
    size: { type: String, default: 'md' }, // md | lg | sm
    to: { type: String, default: '' },
    href: { type: String, default: '' },
    type: { type: String, default: 'button' },
    block: { type: Boolean, default: false },
});

defineEmits(['click']);

const attrs = useAttrs();

const tag = computed(() => {
    if (props.to) return RouterLink;
    if (props.href) return 'a';
    return 'button';
});

/**
 * `to` and `href` are declared props, so they never reach the rendered element
 * through attrs. They have to be forwarded explicitly, otherwise RouterLink
 * receives an undefined location.
 */
const tagProps = computed(() => {
    if (props.to) return { to: props.to };
    if (props.href) return { href: props.href, rel: 'noopener noreferrer' };
    return {};
});
</script>
