<template>
    <div class="onb">
        <div class="onb__top">
            <RouterLink to="/" class="onb__back">← হোমে ফিরুন</RouterLink>
        </div>

        <header class="onb__head">
            <h1>কোন ধরনের পণ্য বিক্রি করেন?</h1>
            <p>ক্যাটাগরি বেছে নিন, আপনার ব্যবসার জন্য মানানসই ডিজাইন খুঁজে নিন।</p>
        </header>

        <div class="onb__search">
            <input
                v-model="search"
                type="search"
                class="onb__search-input"
                placeholder="আপনার পণ্যের ক্যাটাগরির নাম খুঁজুন…"
                aria-label="ক্যাটাগরি খুঁজুন"
            />
        </div>

        <p v-if="loading" class="onb__state">লোড হচ্ছে…</p>

        <div v-if="!loading" class="onb__groups">
            <div v-for="group in data.categories" :key="group.slug" class="onb-group">
                <h2>{{ group.label }}</h2>
                <div class="onb-chips">
                    <button
                        v-for="child in group.children"
                        :key="child.slug"
                        type="button"
                        class="onb-chip"
                        :class="{ 'onb-chip--on': data.active_category?.slug === child.slug }"
                        :aria-pressed="data.active_category?.slug === child.slug"
                        @click="selectCategory(child.slug)"
                    >
                        {{ child.label }}
                    </button>
                </div>
            </div>
        </div>

        <section class="onb__results" aria-live="polite">
            <div class="onb__results-head">
                <div>
                    <h2>{{ data.active_category?.label ?? (search ? `“${search}”` : 'সব ডিজাইন') }}</h2>
                    <p>{{ data.templates.length }} টি ডিজাইন পাওয়া গেছে</p>
                </div>
                <button v-if="data.active_category" type="button" class="onb__link" @click="selectCategory(null)">
                    সব ক্যাটাগরি
                </button>
            </div>

            <p v-if="!data.templates.length" class="onb__empty">
                এই ক্যাটাগরিতে এখনো ডিজাইন নেই। অন্য ক্যাটাগরি দেখুন বা সার্চ বদলান।
            </p>

            <div v-else class="onb-grid">
                <article v-for="template in data.templates" :key="template.id" class="onb-card">
                    <button type="button" class="onb-card__frame" @click="preview = template">
                        <img v-if="template.thumbnail" :src="template.thumbnail" :alt="`${template.name} ডিজাইনের ঝলক`" loading="lazy" />
                        <span v-else class="onb-card__ph">নমুনা</span>
                    </button>
                    <div class="onb-card__meta">
                        <h3>{{ template.name }}</h3>
                        <p>{{ template.category }}</p>
                        <div class="onb-card__actions">
                            <button type="button" class="btn btn--ghost btn--sm" @click="preview = template">প্রিভিউ</button>
                            <button type="button" class="btn btn--primary btn--sm" :disabled="busy === template.id" @click="choose(template)">
                                {{ busy === template.id ? 'অপেক্ষা করুন…' : 'এই ডিজাইন নিন' }}
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <p v-if="error" class="onb__state onb__state--error">{{ error }}</p>

        <div v-if="preview" class="shot" role="dialog" aria-modal="true" :aria-label="`${preview.name} প্রিভিউ`">
            <div class="shot__bar">
                <p>{{ preview.name }}</p>
                <div class="shot__actions">
                    <a :href="`/demo/${preview.slug}`" target="_blank" rel="noopener" class="btn btn--ghost btn--sm">নতুন ট্যাবে দেখুন</a>
                    <button type="button" class="btn btn--primary btn--sm" @click="choose(preview)">এই ডিজাইন নিন</button>
                    <button type="button" class="btn btn--ghost btn--sm" @click="preview = null">বন্ধ করুন</button>
                </div>
            </div>
            <div class="shot__scroll">
                <img v-if="preview.thumbnail" :src="preview.thumbnail" :alt="`${preview.name} এর সম্পূর্ণ ডিজাইন`" />
                <p v-else class="onb__empty">এই ডিজাইনের স্ক্রিনশট তৈরি হচ্ছে।</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../lib/api';

const route = useRoute();
const router = useRouter();

const data = ref({ categories: [], templates: [], active_category: null });
const search = ref(route.query.q ?? '');
const loading = ref(true);
const error = ref('');
const busy = ref(null);
const preview = ref(null);

let debounce = null;

async function fetchData() {
    loading.value = !data.value.templates.length && !data.value.active_category;
    error.value = '';

    try {
        const params = new URLSearchParams();
        if (search.value) params.set('q', search.value);
        if (route.query.category) params.set('category', route.query.category);
        data.value = await api(`/api/discovery?${params.toString()}`);
    } catch (e) {
        error.value = 'তালিকা আনা যায়নি। আবার চেষ্টা করুন।';
    } finally {
        loading.value = false;
    }
}

function selectCategory(slug) {
    router.replace({ name: 'start', query: { ...route.query, category: slug ?? undefined } });
}

async function choose(template) {
    if (busy.value) return;

    busy.value = template.id;

    try {
        const result = await api('/api/drafts', { method: 'POST', body: { template_id: template.id } });
        router.push({
            name: 'builder',
            params: { publicId: result.draft.public_id },
            query: route.query.category ? { from: route.query.category } : {},
        });
    } catch (e) {
        error.value = 'ড্রাফট তৈরি করা যায়নি। আবার চেষ্টা করুন।';
    } finally {
        busy.value = null;
    }
}

function onKey(event) {
    if (event.key === 'Escape' && preview.value) preview.value = null;
}

watch(() => route.query.category, fetchData);
watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.replace({ name: 'start', query: { ...route.query, q: search.value || undefined } });
    }, 250);
});
watch(() => route.query.q, (value) => {
    if ((value ?? '') !== search.value) search.value = value ?? '';
    fetchData();
});

onMounted(() => {
    fetchData();
    window.addEventListener('keydown', onKey);
});

onUnmounted(() => {
    clearTimeout(debounce);
    window.removeEventListener('keydown', onKey);
});
</script>
