<template>
    <div class="demo">
        <div v-if="!embedded" class="demo-bar">
            <RouterLink to="/start" class="demo-bar__back">টেমপ্লেট তালিকায় ফিরে যান</RouterLink>
            <button type="button" class="demo-bar__take" :disabled="taking || !template" @click="takeDesign">
                {{ taking ? 'অপেক্ষা করুন…' : 'এই ডিজাইন নিন' }}
            </button>
        </div>
        <p v-if="takeError && !embedded" class="demo-bar__error" role="alert">{{ takeError }}</p>
        <div class="demo__flag">নমুনা ডিজাইন — এটি একটি ডেমো, এখানে কোনো অর্ডার হয় না</div>

        <div v-if="loading" class="builder__state">লোড হচ্ছে…</div>
        <div v-else-if="notFound" class="builder__state">
            <h1>ডিজাইনটি পাওয়া যায়নি</h1>
            <RouterLink to="/start" class="btn btn--primary">সব ডিজাইন দেখুন</RouterLink>
        </div>
        <TemplateRender v-else demo :content="template.content" :design="template.design" />
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import TemplateRender from '../components/builder/TemplateRender.vue';
import { api } from '../lib/api';

const route = useRoute();
const router = useRouter();
const template = ref(null);
const loading = ref(true);
const notFound = ref(false);
const taking = ref(false);
const takeError = ref('');

const embedded = computed(() => route.query.embed === '1' || framed());

function framed() {
    try {
        return window.self !== window.top;
    } catch {
        return true;
    }
}

async function takeDesign() {
    if (!template.value?.id || taking.value) return;
    taking.value = true;
    takeError.value = '';
    try {
        const result = await api('/api/drafts', { method: 'POST', body: { template_id: template.value.id } });
        await router.push({ name: 'builder', params: { publicId: result.draft.public_id } });
    } catch {
        takeError.value = 'ড্রাফট তৈরি করা যায়নি। আবার চেষ্টা করুন।';
    } finally {
        taking.value = false;
    }
}

onMounted(async () => {
    try {
        template.value = await api(`/api/demo/${route.params.slug}`);
        document.title = `${template.value.name} — নমুনা ডিজাইন`;
        document.querySelector('meta[name="robots"]')?.setAttribute('content', 'noindex, nofollow');
    } catch {
        notFound.value = true;
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.demo-bar {
    position: sticky;
    top: 0;
    z-index: 30;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    background: #0d3b2e;
    color: #f7f5ee;
}
.demo-bar__back,
.demo-bar__take {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 999px;
    font: inherit;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}
.demo-bar__back {
    background: transparent;
    color: #f7f5ee;
    border: 1.5px solid rgba(247, 245, 238, 0.55);
}
.demo-bar__take {
    background: #c9f24d;
    color: #12281f;
    border: 0;
}
.demo-bar__take:disabled { opacity: 0.65; cursor: progress; }
.demo-bar__back:focus-visible,
.demo-bar__take:focus-visible { outline: 3px solid #c9f24d; outline-offset: 2px; }
.demo-bar__error {
    margin: 0;
    padding: 8px 16px;
    background: #fdecea;
    color: #8a1c12;
    text-align: center;
}
</style>
