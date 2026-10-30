<template>
    <header class="site-header">
        <div class="container site-header__inner">
            <RouterLink :to="routes.home" class="wordmark" aria-label="SellPata — হোম">
                Sell<span>Pata</span>
            </RouterLink>

            <nav class="site-nav" aria-label="প্রধান মেনু">
                <RouterLink v-for="item in navigation" :key="item.to" :to="item.to" class="site-nav__link">
                    {{ item.label }}
                </RouterLink>
            </nav>

            <div class="site-header__actions">
                <AppButton size="sm" class="site-header__cta" :data-source="'header'" @click="openCta('header')">
                    {{ primaryCtaLabel }}
                </AppButton>

                <button
                    ref="toggle"
                    type="button"
                    class="menu-toggle"
                    :aria-expanded="menuOpen ? 'true' : 'false'"
                    aria-controls="mobile-menu"
                    @click="menuOpen = !menuOpen"
                >
                    <span class="menu-toggle__bars" :class="{ 'is-open': menuOpen }" aria-hidden="true">
                        <span /><span /><span />
                    </span>
                    <span class="sr-only">{{ menuOpen ? 'মেনু বন্ধ করুন' : 'মেনু খুলুন' }}</span>
                </button>
            </div>
        </div>

        <Transition name="sheet">
            <div v-if="menuOpen" id="mobile-menu" class="mobile-menu" @keydown.esc="closeMenu">
                <nav aria-label="মোবাইল মেনু">
                    <RouterLink
                        v-for="item in navigation"
                        :key="item.to"
                        :to="item.to"
                        class="mobile-menu__link"
                        @click="closeMenu"
                    >
                        {{ item.label }}
                    </RouterLink>
                </nav>
                <AppButton block @click="onMobileCta">{{ primaryCtaLabel }}</AppButton>
            </div>
        </Transition>
    </header>
</template>

<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import AppButton from '../ui/AppButton.vue';
import { navigation, primaryCtaLabel } from '../../data/site';
import { openCta } from '../../lib/cta';
import config from '../../lib/config';

const routes = config.routes;
const route = useRoute();
const menuOpen = ref(false);
const toggle = ref(null);

function closeMenu() {
    menuOpen.value = false;
    nextTick(() => toggle.value?.focus());
}

function onMobileCta() {
    menuOpen.value = false;
    openCta('mobile-menu');
}

watch(() => route.fullPath, () => {
    menuOpen.value = false;
});

/**
 * Escape closes the menu even while focus sits on the toggle button, which is
 * outside the panel, so keyboard users are never trapped.
 */
function onKeydown(event) {
    if (event.key === 'Escape' && menuOpen.value) {
        event.preventDefault();
        closeMenu();
    }
}

watch(menuOpen, (isOpen) => {
    if (isOpen) window.addEventListener('keydown', onKeydown);
    else window.removeEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>
