<template>
    <article class="benefit-card">
        <span class="benefit-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <template v-if="name === 'ready'">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <path d="M3 9h18M8 13h8" />
                </template>
                <template v-else-if="name === 'category'">
                    <rect x="4" y="4" width="6" height="6" rx="1" />
                    <rect x="14" y="4" width="6" height="6" rx="1" />
                    <rect x="4" y="14" width="6" height="6" rx="1" />
                    <rect x="14" y="14" width="6" height="6" rx="1" />
                </template>
                <template v-else-if="name === 'custom'">
                    <path d="M4 20h4L20 8a2 2 0 0 0-3-3L5 17v3z" />
                    <path d="M14 6l3 3" />
                </template>
                <template v-else-if="name === 'payments'">
                    <rect x="3" y="6" width="18" height="12" rx="2" />
                    <path d="M3 10h18" />
                </template>
                <template v-else>
                    <path d="M3 7h11v9H3z" />
                    <path d="M14 10h4l3 3v3h-7z" />
                    <circle cx="7" cy="18" r="1.6" />
                    <circle cx="17" cy="18" r="1.6" />
                </template>
            </svg>
        </span>
        <h3 class="benefit-card__title">{{ title }}</h3>
        <p class="benefit-card__body">{{ body }}</p>
        <p v-if="comingSoonLabel" class="benefit-card__tag">{{ comingSoonLabel }}</p>
    </article>
</template>

<script setup>
defineProps({
    name: { type: String, required: true },
    title: { type: String, required: true },
    body: { type: String, required: true },
    comingSoonLabel: { type: String, default: '' },
});
</script>
