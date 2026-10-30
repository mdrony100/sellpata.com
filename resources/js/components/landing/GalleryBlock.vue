<template>
    <section class="block gallery">
        <EditableField field="headings.gallery" label="গ্যালারির শিরোনাম" :editable="landing.editable" @edit="landing.edit">
            <h2>{{ landing.content.headings?.gallery || 'ছবি' }}</h2>
        </EditableField>
        <EditableField field="gallery" label="গ্যালারি" :editable="landing.editable" as="div" @edit="landing.edit">
            <div class="gallery__grid">
                <figure v-for="(image, index) in landing.galleryItems" :key="image.src" :style="{ aspectRatio: image.ratio || '1 / 1' }">
                    <button type="button" @click="landing.openImage(index)" :aria-label="`${image.alt || 'ছবি'} বড় করে দেখুন`">
                        <img :src="image.src" :alt="image.alt" loading="lazy" />
                    </button>
                </figure>
            </div>
        </EditableField>
        <CtaButton />
    </section>
</template>

<script setup>
import { inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';
import CtaButton from './CtaButton.vue';

const landing = inject(landingKey);
</script>
