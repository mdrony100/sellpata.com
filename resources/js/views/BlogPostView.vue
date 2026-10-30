<template>
  <SiteHeader />
    <main>
    <article class="post">
      <RouterLink to="/blog" class="back">← সব লেখা</RouterLink>
      <p v-if="loading" class="muted">লোড হচ্ছে…</p>
      <template v-else-if="post">
        <h1>{{ post.title }}</h1>
        <p class="meta">{{ post.published_at }}<span v-if="post.author"> · {{ post.author }}</span></p>
        <img v-if="post.cover_image" :src="post.cover_image" :alt="post.title" class="cover">
        <div class="body" v-html="post.body"></div>
      </template>
      <p v-else class="muted">লেখাটি পাওয়া যায়নি।</p>
    </article>
  </main>
    <SiteFooter />
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import SiteHeader from '../components/layout/SiteHeader.vue'
import SiteFooter from '../components/layout/SiteFooter.vue'

const route = useRoute()
const post = ref(null)
const loading = ref(true)

async function load () {
  loading.value = true
  post.value = null
  const response = await fetch(`/api/blog/${route.params.slug}`, { headers: { Accept: 'application/json' } })
  if (response.ok) post.value = await response.json()
  loading.value = false
}

onMounted(load)
watch(() => route.params.slug, load)
</script>

<style scoped>
.post{max-width:760px;margin:0 auto;padding:clamp(28px,6vw,60px) 20px 80px}
.back{display:inline-block;margin-bottom:18px;color:#155e47;text-decoration:none;font-size:.9rem}
h1{margin:0 0 10px;font-size:clamp(1.7rem,4vw,2.3rem);line-height:1.35;color:#0d3b2e}
.meta{margin:0 0 22px;color:#5d6763;font-size:.88rem}
.cover{width:100%;border-radius:16px;margin-bottom:22px}
.body{color:#26312d;line-height:1.9;font-size:1.02rem}
.body :deep(h2){font-size:1.3rem;margin:28px 0 10px;color:#0d3b2e}
.body :deep(p){margin:0 0 16px}
.body :deep(ul),.body :deep(ol){margin:0 0 16px 20px}
.body :deep(img){max-width:100%;border-radius:12px}
.body :deep(a){color:#155e47}
.muted{color:#5d6763}
</style>
