<template>
    <svg class="tpl-art" viewBox="0 0 120 120" role="img" :aria-label="label">
        <template v-if="kind === 'fashion'">
            <path d="M60 22c8 0 12 4 12 4l18 8-6 18-10-4v42H46V56l-10 4-6-18 18-8s4-4 12-4z" fill="var(--art-primary)" opacity=".92" />
            <path d="M52 22h16l-8 12z" fill="var(--art-accent)" />
            <rect x="50" y="62" width="20" height="4" rx="2" fill="var(--art-accent)" opacity=".9" />
        </template>
        <template v-else-if="kind === 'beauty'">
            <rect x="48" y="16" width="24" height="10" rx="4" fill="var(--art-accent)" />
            <path d="M54 26h12v10H54z" fill="var(--art-primary)" opacity=".55" />
            <rect x="42" y="36" width="36" height="66" rx="10" fill="var(--art-primary)" />
            <rect x="48" y="58" width="24" height="26" rx="3" fill="#fff" opacity=".92" />
            <path d="M52 66h16M52 72h12" stroke="var(--art-primary)" stroke-width="2" stroke-linecap="round" opacity=".5" />
        </template>
        <template v-else-if="kind === 'food'">
            <rect x="36" y="42" width="48" height="60" rx="8" fill="var(--art-primary)" />
            <rect x="32" y="30" width="56" height="14" rx="4" fill="var(--art-accent)" />
            <rect x="44" y="56" width="32" height="30" rx="3" fill="#fff" opacity=".94" />
            <path d="M50 66h20M50 73h14" stroke="var(--art-primary)" stroke-width="2.4" stroke-linecap="round" opacity=".55" />
        </template>
        <template v-else-if="kind === 'gadget'">
            <rect x="26" y="44" width="68" height="44" rx="14" fill="var(--art-primary)" />
            <path d="M26 58h68" stroke="#fff" stroke-width="2" opacity=".35" />
            <circle cx="44" cy="70" r="9" fill="#fff" opacity=".9" />
            <circle cx="76" cy="70" r="9" fill="var(--art-accent)" />
            <circle cx="44" cy="70" r="3" fill="var(--art-primary)" />
        </template>
        <template v-else-if="kind === 'home'">
            <ellipse cx="60" cy="42" rx="34" ry="10" fill="var(--art-accent)" opacity=".85" />
            <path d="M28 44h64l-6 34a10 10 0 0 1-10 8H44a10 10 0 0 1-10-8z" fill="var(--art-primary)" />
            <rect x="52" y="22" width="16" height="8" rx="4" fill="var(--art-primary)" opacity=".6" />
        </template>
        <template v-else-if="kind === 'kids'">
            <rect x="26" y="52" width="30" height="30" rx="4" fill="var(--art-primary)" />
            <rect x="60" y="34" width="30" height="30" rx="4" fill="var(--art-accent)" />
            <rect x="60" y="68" width="30" height="26" rx="4" fill="var(--art-primary)" opacity=".55" />
            <circle cx="41" cy="42" r="10" fill="var(--art-accent)" opacity=".9" />
        </template>
        <template v-else>
            <rect x="30" y="44" width="60" height="48" rx="6" fill="var(--art-primary)" />
            <rect x="24" y="32" width="72" height="16" rx="4" fill="var(--art-primary)" opacity=".75" />
            <path d="M60 32v60" stroke="var(--art-accent)" stroke-width="8" />
            <path d="M60 32c-10-2-16-8-14-14 8-2 14 4 14 14zm0 0c10-2 16-8 14-14-8-2-14 4-14 14z" fill="var(--art-accent)" />
        </template>
    </svg>
</template>

<script setup>
defineProps({
    kind: { type: String, required: true },
    label: { type: String, default: 'নমুনা পণ্যের ছবি' },
});
</script>
