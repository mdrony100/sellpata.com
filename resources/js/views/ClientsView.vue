<template>
    <div class="page">
        <section class="page-hero">
            <div class="container">
                <p class="eyebrow">ক্লায়েন্ট</p>
                <h1 class="page-hero__title">{{ copy.heading }}</h1>
                <p class="page-hero__body">{{ copy.intro }}</p>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div v-if="!clients.length" class="empty-state empty-state--lg">
                    <p class="empty-state__title">{{ copy.emptyTitle }}</p>
                    <p class="empty-state__body">{{ copy.emptyBody }}</p>
                </div>
                <div v-else class="client-grid">
                    <ClientCard v-for="client in clients" :key="client.id" :client="client" />
                </div>

                <div class="samples">
                    <SectionHeading :title="copy.sampleAreaTitle" :body="copy.sampleAreaBody" />
                    <div class="template-grid">
                        <TemplateCard
                            v-for="template in samples"
                            :key="template.id"
                            :template="template"
                            sample-badge="নমুনা ডিজাইন"
                            @preview="previewTemplate = $event"
                        />
                    </div>
                </div>

                <div class="section__cta">
                    <AppButton size="lg" @click="openCta('clients-page')">{{ copy.cta }}</AppButton>
                </div>
            </div>
        </section>

        <TemplatePreviewModal :template="previewTemplate" @close="previewTemplate = null" />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import AppButton from '../components/ui/AppButton.vue';
import SectionHeading from '../components/ui/SectionHeading.vue';
import TemplateCard from '../components/home/TemplateCard.vue';
import TemplatePreviewModal from '../components/home/TemplatePreviewModal.vue';
import ClientCard from '../components/clients/ClientCard.vue';
import { clientsPage as copy } from '../data/site';
import { clients } from '../data/clients';
import { templates } from '../data/templates';
import { openCta } from '../lib/cta';

const samples = templates.slice(0, 6);
const previewTemplate = ref(null);
</script>
