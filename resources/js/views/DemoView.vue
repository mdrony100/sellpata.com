<template>
    <div class="demo">
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
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import TemplateRender from '../components/builder/TemplateRender.vue';
import { api } from '../lib/api';

const route = useRoute();
const template = ref(null);
const loading = ref(true);
const notFound = ref(false);

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
