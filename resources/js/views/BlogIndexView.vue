<template>
  <SiteHeader />
    <main>
    <section class="blog">
      <div class="blog__intro">
        <p class="eyebrow">ব্লগ</p>
        <h1>অনলাইন বিক্রি, সহজ ভাষায়</h1>
        <p class="lede">ল্যান্ডিং পেজ, ফেসবুক বিজনেস আর অর্ডার সামলানোর ব্যবহারিক লেখা।</p>
      </div>

      <p v-if="loading" class="muted">লোড হচ্ছে…</p>
      <p v-else-if="!posts.length" class="muted">এখনো কোনো লেখা প্রকাশিত হয়নি।</p>

      <div v-else class="blog__grid">
        <RouterLink v-for="post in posts" :key="post.slug" :to="`/blog/${post.slug}`" class="card">
          <img v-if="post.cover_image" :src="post.cover_image" :alt="post.title" loading="lazy">
          <div class="card__body">
            <p class="meta">{{ post.published_at }}"<span v-if="post.author"> · {{ post.author }}</span></p>
            <h2>{{ post.title }}</h2>
            <p class="excerpt">{{ post.excerpt }}</p>
            <span class="more">পড়ুন →</span>
          </div>
        </RouterLink>
      </div>

      <button v-if="hasMore && !loading" class="load" type="button" @click="load">আরও লেখা দেখুন</button>
    </section>
  </main>
    <SiteFooter />
</template>

<script setup>
import { onMounted, ref } from 'vue'
import SiteHeader from '../components/layout/SiteHeader.vue'
import SiteFooter from '../components/layout/SiteFooter.vue'

const posts = ref([])
const page = ref(1)
const hasMore = ref(false)
const loading = ref(true)

async function load () {
  loading.value = true
  const response = await fetch(`/api/blog?page=${page.value}`, { headers: { Accept: 'application/json' } })
  if (response.ok) {
    const data = await response.json()
    posts.value = page.value === 1 ? data.items : [...posts.value, ...data.items]
    hasMore.value = data.has_more
    page.value += 1
  }
  loading.value = false
}

onMounted(load)
</script>

<style scoped>
.blog{max-width:1080px;margin:0 auto;padding:clamp(28px,6vw,64px) 20px 80px}
.eyebrow{font-size:.85rem;letter-spacing:.08em;text-transform:uppercase;color:#5d6763;margin:0 0 8px}
h1{margin:0 0 10px;font-size:clamp(1.7rem,4vw,2.4rem);color:#0d3b2e}
.lede{color:#5d6763;margin:0 0 28px}
.blog__grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))}
.card{display:flex;flex-direction:column;background:#fff;border:1px solid #e4ddcd;border-radius:16px;overflow:hidden;text-decoration:none;color:inherit}
.card img{width:100%;height:170px;object-fit:cover}
.card__body{padding:18px;display:flex;flex-direction:column;gap:8px}
.meta{margin:0;font-size:.82rem;color:#5d6763}
h2{margin:0;font-size:1.08rem;line-height:1.5;color:#14211c}
.excerpt{margin:0;color:#4b5551;font-size:.94rem}
.more{margin-top:auto;font-weight:600;color:#155e47;font-size:.9rem}
.load{margin:28px auto 0;display:block;padding:11px 22px;border-radius:999px;border:1px solid #0d3b2e;background:#0d3b2e;color:#fff;cursor:pointer}
.muted{color:#5d6763}
</style>
