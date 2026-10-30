<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import DashIcon from './DashIcon.vue';

defineProps({
    store: { type: Object, required: true },
    user: { type: Object, default: null },
    groups: { type: Array, required: true },
    active: { type: String, required: true },
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    plan: { type: String, default: '' },
    pagesUsed: { type: [Number, String], default: 0 },
    pagesLimit: { type: [Number, String], default: null },
});

const emit = defineEmits(['logout']);

const route = useRoute();
const navOpen = ref(false);
const isMobile = ref(false);
const sidebar = ref(null);
let media;

function closeNav() {
    navOpen.value = false;
}

function toggleNav() {
    navOpen.value = !navOpen.value;
}

function hrefFor(key) {
    return key === 'overview' ? '/dashboard' : `/dashboard/${key}`;
}

function onKey(event) {
    if (event.key === 'Escape') closeNav();
}

function syncMobile() {
    isMobile.value = media.matches;
    if (!media.matches) navOpen.value = false;
}

watch(() => route.fullPath, closeNav);

watch(navOpen, (open) => {
    document.body.classList.toggle('is-locked', open && isMobile.value);
});

onMounted(() => {
    media = window.matchMedia('(max-width: 959px)');
    syncMobile();
    media.addEventListener('change', syncMobile);
    window.addEventListener('keydown', onKey);
});

onBeforeUnmount(() => {
    media?.removeEventListener('change', syncMobile);
    window.removeEventListener('keydown', onKey);
    document.body.classList.remove('is-locked');
});
</script>

<template>
    <div class="dash-shell">
        <div v-if="navOpen && isMobile" class="dash-backdrop" @click="closeNav" />

        <aside
            id="dash-sidebar"
            ref="sidebar"
            class="dash-sidebar"
            :class="{ 'is-open': navOpen }"
            :inert="isMobile && !navOpen ? true : undefined"
            aria-label="ড্যাশবোর্ড মেনু"
        >
            <div class="dash-brand">
                <div class="dash-brand__row">
                    <div class="dash-logo" aria-hidden="true">
                        <svg viewBox="0 0 32 32" class="dash-logo__mark">
                            <path d="M8.2 18.6c4.8-.6 8.6-4.6 10.4-11.2 1.1 6.6.2 11.4-3.2 14.8-2.6 2.6-6.2 3.6-10.4 2.8 1.1-2.1 2-4.4 3.2-6.4z" />
                            <path d="M16.8 8.2c2.2 2.8 3.2 6 2.6 9.4" />
                        </svg>
                    </div>
                    <div class="dash-brand__text">
                        <p class="dash-brand__kicker">Sell<span>Pata</span></p>
                        <h2 class="dash-brand__name">{{ store.name }}</h2>
                    </div>
                    <button type="button" class="dash-drawer-close" @click="closeNav">
                        <DashIcon name="close" />
                        <span class="sr-only">মেনু বন্ধ করুন</span>
                    </button>
                </div>
                <a class="dash-storelink" :href="store.url" target="_blank" rel="noopener">
                    <DashIcon name="store" />
                    <span>{{ store.username }}.sellpata.com</span>
                    <DashIcon name="external" />
                </a>
            </div>

            <nav class="dash-nav">
                <template v-for="group in groups" :key="group.label || 'home'">
                    <p v-if="group.label" class="dash-nav__label">{{ group.label }}</p>
                    <RouterLink
                        v-for="item in group.items"
                        :key="item.key"
                        :to="hrefFor(item.key)"
                        class="dash-nav__link"
                        :class="{ 'is-active': active === item.key }"
                        :aria-current="active === item.key ? 'page' : undefined"
                        @click="closeNav"
                    >
                        <DashIcon :name="item.icon" />
                        <span>{{ item.label }}</span>
                    </RouterLink>
                </template>
            </nav>

            <div class="dash-sidefoot">
                <div v-if="plan" class="dash-planline">
                    <span class="dash-badge">{{ plan }}</span>
                    <span class="dash-muted">{{ pagesUsed }}/{{ pagesLimit ?? '—' }} পেজ</span>
                </div>
                <p v-if="user" class="dash-who">
                    <strong>{{ user.name }}</strong>
                    <span>{{ user.phone }}</span>
                </p>
                <button type="button" class="dash-logout" @click="emit('logout')">
                    <DashIcon name="logout" />
                    লগআউট
                </button>
            </div>
        </aside>

        <div class="dash-main">
            <header class="dash-topbar">
                <button
                    type="button"
                    class="dash-burger"
                    :aria-expanded="navOpen ? 'true' : 'false'"
                    aria-controls="dash-sidebar"
                    @click="toggleNav"
                >
                    <DashIcon name="menu" />
                    <span class="sr-only">{{ navOpen ? 'মেনু বন্ধ করুন' : 'মেনু খুলুন' }}</span>
                </button>
                <div class="dash-topbar__text">
                    <strong>{{ store.name }}</strong>
                    <span>{{ title }}</span>
                </div>
            </header>

            <div class="dash-content">
                <slot name="banner" />
                <header class="dash-pagehead">
                    <div>
                        <h1>{{ title }}</h1>
                        <p v-if="subtitle">{{ subtitle }}</p>
                    </div>
                    <div v-if="$slots.actions" class="dash-pagehead__actions">
                        <slot name="actions" />
                    </div>
                </header>
                <slot />
            </div>
        </div>
    </div>
</template>
