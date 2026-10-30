<template>
    <section v-if="landing.reviews.length" class="block reviews">
        <EditableField field="headings.reviews" label="রিভিউর শিরোনাম" :editable="landing.editable" @edit="landing.edit">
            <h2>{{ landing.content.headings?.reviews || 'ক্রেতাদের কথা' }}</h2>
        </EditableField>
        <p v-if="landing.demo || landing.editable" class="reviews__note">নমুনা চিহ্নিত মতামত প্রকাশিত পেজে দেখা যায় না।</p>
        <EditableField field="reviews" label="রিভিউ" :editable="landing.editable" as="div" @edit="landing.edit">
            <ul>
                <li v-for="(review, index) in landing.reviews" :key="index">
                    <img v-if="photo(review, index)" :src="photo(review, index)" alt="" loading="lazy" />
                    <blockquote>
                        <p>{{ review.text }}</p>
                        <footer>
                            <span>{{ review.name }}</span>
                            <span v-if="stars(review)" class="reviews__stars" :aria-label="`${stars(review)} তারকা`">{{ '●'.repeat(stars(review)) }}</span>
                            <span v-if="isSample(review)" class="reviews__sample">নমুনা রিভিউ</span>
                        </footer>
                    </blockquote>
                </li>
            </ul>
        </EditableField>
    </section>
</template>

<script setup>
import { inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);

function isSample(review) {
    return review.sample === true || review.sample === 1 || review.sample === '1';
}

function stars(review) {
    const value = Number(review.rating);
    return value >= 1 && value <= 5 ? value : 0;
}

function photo(review, index) {
    return landing.mediaSrc(`reviews.${index}.photo`, review.photo);
}
</script>
