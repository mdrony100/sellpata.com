import { reactive, readonly } from 'vue';

/**
 * One place for every "ল্যান্ডিং পেজ নিন" action on the site.
 *
 * The onboarding flow is not specified yet, so today every CTA opens the same
 * modal. When the real flow arrives, only this file and CtaDialog.vue change.
 */
const state = reactive({
    isOpen: false,
    source: 'unknown',
});

export function openCta(source = 'unknown') {
    state.source = source;
    state.isOpen = true;
}

export function closeCta() {
    state.isOpen = false;
}

export const ctaState = readonly(state);
