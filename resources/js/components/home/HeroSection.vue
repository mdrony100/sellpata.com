<template>
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__grid">
            <div class="hero__copy">
                <p class="eyebrow">{{ copy.eyebrow }}</p>
                <h1 id="hero-title" class="hero__title">
                    <span>{{ copy.heading[0] }}</span>
                    <span class="hero__title-accent">{{ copy.heading[1] }}</span>
                </h1>
                <p class="hero__body">{{ copy.body }}</p>

                <div class="hero__actions">
                    <AppButton size="lg" @click="openCta('hero')">{{ copy.primaryCta }}</AppButton>
                    <a class="hero__secondary" :href="`#tutorial`" @click.prevent="scrollToVideo">
                        {{ copy.secondaryLink }}
                    </a>
                </div>

                <p class="hero__benefits">{{ copy.benefits }}</p>
            </div>

            <div id="tutorial" class="hero__video" ref="videoArea">
                <VideoEmbed ref="video" />
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import AppButton from '../ui/AppButton.vue';
import VideoEmbed from './VideoEmbed.vue';
import { hero as copy } from '../../data/site';
import { openCta } from '../../lib/cta';

const video = ref(null);
const videoArea = ref(null);

function scrollToVideo() {
    videoArea.value?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    video.value?.focusPlay();
}
</script>
