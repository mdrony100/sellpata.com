<template>
    <div class="video" :class="{ 'video--empty': !embedUrl }">
        <div class="video__frame">
            <iframe
                v-if="embedUrl && playing"
                class="video__iframe"
                :src="`${embedUrl}&autoplay=1`"
                :title="title"
                loading="lazy"
                allow="accelerometer; encrypted-media; gyroscope; picture-in-picture"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
            />

            <button
                v-else-if="embedUrl"
                ref="playButton"
                type="button"
                class="video__facade"
                :aria-label="`${title} — ভিডিও চালু করুন`"
                @click="playing = true"
            >
                <span class="video__play" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 7l9 5-9 5z" /></svg>
                </span>
                <span class="video__caption">{{ title }}</span>
            </button>

            <div v-else class="video__placeholder" role="group" :aria-label="title">
                <span class="video__placeholder-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">
                        <rect x="3" y="5" width="18" height="14" rx="3" />
                        <path d="M10 9.5l5 2.5-5 2.5z" />
                    </svg>
                </span>
                <p class="video__placeholder-title">টিউটোরিয়াল ভিডিও শিগগিরই যুক্ত হবে</p>
                <p class="video__placeholder-body">
                    এই জায়গায় SellPata ব্যবহারের ধাপে ধাপে ভিডিও দেখা যাবে।
                </p>
            </div>
        </div>
        <p class="video__title">{{ title }}</p>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import config from '../../lib/config';
import { youtubeEmbedUrl } from '../../lib/youtube';
import { hero } from '../../data/site';

const props = defineProps({
    url: { type: String, default: '' },
});

const title = hero.videoTitle;
const playing = ref(false);
const playButton = ref(null);

const embedUrl = computed(() => youtubeEmbedUrl(props.url || config.youtubeUrl));

defineExpose({ focusPlay: () => (playing.value ? null : playButton.value?.focus()) });
</script>
