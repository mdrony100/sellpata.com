<template>
    <section class="hero" :class="`hero--${mode}`">
        <div v-if="mode !== 'banner'" class="hero__copy">
            <EditableField field="hero.badge" label="ব্যাজ" :editable="landing.editable" as="div" @edit="landing.edit">
                <p v-if="landing.content.hero?.badge" class="hero__badge">{{ landing.content.hero.badge }}</p>
            </EditableField>
            <EditableField field="hero.headline" label="শিরোনাম" :editable="landing.editable" @edit="landing.edit">
                <h1>{{ landing.content.hero?.headline }}</h1>
            </EditableField>
            <EditableField field="hero.subline" label="সহায়ক লেখা" :editable="landing.editable" @edit="landing.edit">
                <p class="hero__sub">{{ landing.content.hero?.subline }}</p>
            </EditableField>
            <PriceLine />
            <CtaButton />
        </div>

        <div class="hero__media">
            <div v-if="mode === 'video'" class="hero__video">
                <EditableField field="hero.video_url" label="ভিডিওর লিংক" :editable="landing.editable" as="div" @edit="landing.edit">
                    <iframe
                        v-if="embed && playing"
                        :src="embed"
                        title="পণ্যের ভিডিও"
                        loading="lazy"
                        allow="encrypted-media; picture-in-picture"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen
                    />
                    <button v-else-if="embed" type="button" class="hero__play" @click="playing = true">
                        ভিডিও চালু করুন
                    </button>
                    <div v-else class="hero__play hero__play--empty">
                        <strong>ব্যবহারের ভিডিও</strong>
                        <span>ইউটিউব লিংক বসালে এখানে চলবে। নিজে থেকে চালু হয় না।</span>
                    </div>
                </EditableField>
            </div>
            <figure v-else class="hero__shot">
                <EditableField field="product.image" label="পণ্যের ছবি" :editable="landing.editable" as="div" @edit="landing.edit">
                    <img v-if="shot" :src="shot" :alt="landing.content.product?.name || ''" />
                    <span v-else class="hero__empty">পণ্যের ছবি যোগ করুন</span>
                </EditableField>
            </figure>
            <ul v-if="mode === 'split' && thumbs.length" class="hero__thumbs">
                <li v-for="(image, index) in thumbs" :key="image.src">
                    <button type="button" @click="landing.openImage(index)">
                        <img :src="image.src" :alt="image.alt" />
                    </button>
                </li>
            </ul>
        </div>

        <div v-if="mode === 'banner'" class="hero__copy hero__copy--after">
            <EditableField field="hero.badge" label="ব্যাজ" :editable="landing.editable" as="div" @edit="landing.edit">
                <p v-if="landing.content.hero?.badge" class="hero__badge">{{ landing.content.hero.badge }}</p>
            </EditableField>
            <EditableField field="hero.headline" label="শিরোনাম" :editable="landing.editable" @edit="landing.edit">
                <h1>{{ landing.content.hero?.headline }}</h1>
            </EditableField>
            <EditableField field="hero.subline" label="সহায়ক লেখা" :editable="landing.editable" @edit="landing.edit">
                <p class="hero__sub">{{ landing.content.hero?.subline }}</p>
            </EditableField>
            <PriceLine />
            <CtaButton />
        </div>
    </section>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey, youtubeId } from '../../landing/helpers';
import PriceLine from './PriceLine.vue';
import CtaButton from './CtaButton.vue';

const landing = inject(landingKey);
const playing = ref(false);
const mode = computed(() => ['split', 'video', 'banner'].includes(landing.content.hero?.mode) ? landing.content.hero.mode : 'split');
const shot = computed(() => landing.mediaSrc('product.image', landing.selectedVariant?.image || landing.content.product?.image));
const thumbs = computed(() => landing.galleryItems.slice(0, 3));
const embed = computed(() => {
    const id = youtubeId(landing.content.hero?.video_url);
    return id ? `https://www.youtube-nocookie.com/embed/${id}?rel=0` : null;
});
</script>
