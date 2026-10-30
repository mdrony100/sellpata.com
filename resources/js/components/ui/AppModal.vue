<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="open"
                class="modal"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="titleId"
                @keydown.esc.prevent="requestClose"
                @keydown.tab="trapFocus"
            >
                <div class="modal__backdrop" @click="requestClose" />
                <div ref="panel" class="modal__panel" tabindex="-1">
                    <button class="modal__close" type="button" :aria-label="closeLabel" @click="requestClose">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h2 :id="titleId" class="modal__title">{{ title }}</h2>
                    <div class="modal__body">
                        <slot />
                    </div>
                    <div v-if="$slots.actions" class="modal__actions">
                        <slot name="actions" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, required: true },
    closeLabel: { type: String, default: 'বন্ধ করুন' },
});

const emit = defineEmits(['close']);

const panel = ref(null);
const titleId = `modal-title-${Math.random().toString(36).slice(2, 8)}`;
let lastFocused = null;

function focusables() {
    if (!panel.value) return [];
    return Array.from(
        panel.value.querySelectorAll(
            'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
        )
    ).filter((el) => el.offsetParent !== null);
}

function trapFocus(event) {
    const items = focusables();
    if (items.length === 0) {
        event.preventDefault();
        panel.value?.focus();
        return;
    }
    const first = items[0];
    const last = items[items.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

function requestClose() {
    emit('close');
}

watch(
    () => props.open,
    async (isOpen) => {
        if (isOpen) {
            lastFocused = document.activeElement;
            document.body.classList.add('is-locked');
            await nextTick();
            (focusables()[0] ?? panel.value)?.focus();
        } else {
            document.body.classList.remove('is-locked');
            if (lastFocused instanceof HTMLElement) lastFocused.focus();
        }
    }
);

onBeforeUnmount(() => document.body.classList.remove('is-locked'));
</script>
