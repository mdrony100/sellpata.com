<template>
    <section id="proof" class="section proof" aria-labelledby="proof-title">
        <div class="container">
            <SectionHeading center :title="copy.heading" :body="copy.intro">
                <template #title><span id="proof-title">{{ copy.heading }}</span></template>
            </SectionHeading>

            <div class="proof__grid">
                <article v-for="item in samples" :key="item.id" class="proof__card">
                    <div class="proof__preview">
                        <TemplatePreview :template="item" :palette="item.palette" />
                        <span class="proof__badge">{{ copy.sampleLabel }}</span>
                    </div>
                    <p class="proof__brand">{{ item.brand }}</p>
                    <p class="proof__category">{{ categoryLabel(item.category) }}</p>
                </article>
            </div>

            <div v-if="!reviews.length" class="empty-state">
                <p class="empty-state__title">{{ copy.emptyReviews }}</p>
                <p class="empty-state__body">
                    গ্রাহকের অনুমোদিত রিভিউ পাওয়ার পর এখানেই দেখা যাবে—আমরা নিজেরা কোনো রিভিউ বানাই না।
                </p>
            </div>

            <div class="proof__footer">
                <RouterLink :to="routes.clients" class="proof__link">{{ copy.clientsLink }} →</RouterLink>
                <AppButton size="lg" @click="openCta('proof')">{{ copy.cta }}</AppButton>
            </div>
        </div>
    </section>
</template>

<script setup>
import AppButton from '../ui/AppButton.vue';
import SectionHeading from '../ui/SectionHeading.vue';
import TemplatePreview from './TemplatePreview.vue';
import { proof as copy } from '../../data/site';
import { templates, categoryLabel } from '../../data/templates';
import { reviews } from '../../data/clients';
import config from '../../lib/config';
import { openCta } from '../../lib/cta';

const routes = config.routes;
const samples = templates.slice(0, 3);
</script>
