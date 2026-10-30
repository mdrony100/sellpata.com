<template>
  <SiteHeader />
    <main>
    <article class="legal">
      <h1>{{ doc.title }}</h1>
      <p v-if="doc.updated_at" class="meta">সর্বশেষ হালনাগাদ: {{ doc.updated_at }}</p>
      <div v-if="doc.body" class="body" v-html="doc.body"></div>
      <p v-else class="muted">এই পাতার লেখা এখনো যোগ করা হয়নি।</p>
    </article>
  </main>
    <SiteFooter />
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import SiteHeader from '../components/layout/SiteHeader.vue'
import SiteFooter from '../components/layout/SiteFooter.vue'

const route = useRoute()
const doc = ref({ title: '', body: '', updated_at: '' })
const kind = computed(() => (route.path.includes('terms') ? 'terms' : 'privacy'))

async function load () {
  const response = await fetch(`/api/legal/${kind.value}`, { headers: { Accept: 'application/json' } })
  if (response.ok) doc.value = await response.json()
}

onMounted(load)
watch(kind, load)
</script>

<style scoped>
.legal{max-width:760px;margin:0 auto;padding:clamp(28px,6vw,60px) 20px 80px}
h1{margin:0 0 8px;font-size:clamp(1.6rem,4vw,2.1rem);color:#0d3b2e}
.meta{margin:0 0 24px;color:#5d6763;font-size:.86rem}
.body{color:#26312d;line-height:1.9}
.body :deep(h2){font-size:1.2rem;margin:26px 0 8px;color:#0d3b2e}
.body :deep(p){margin:0 0 14px}
.body :deep(ul){margin:0 0 14px 20px}
.muted{color:#5d6763}
</style>
