<template>
    <section id="how" class="section how" aria-labelledby="how-title">
        <div class="container">
            <SectionHeading center :title="copy.heading">
                <template #title><span id="how-title">{{ copy.heading }}</span></template>
            </SectionHeading>

            <ol class="steps">
                <li v-for="step in copy.items" :key="step.number" class="step">
                    <div class="step__art" aria-hidden="true">
                        <svg v-if="step.art === 'choose'" viewBox="0 0 160 110" fill="none">
                            <rect x="14" y="16" width="56" height="78" rx="8" class="step__shape-soft" />
                            <rect x="52" y="8" width="56" height="78" rx="8" class="step__shape" />
                            <rect x="62" y="20" width="36" height="26" rx="4" fill="var(--brand-100)" />
                            <rect x="62" y="54" width="26" height="5" rx="2.5" class="step__line" />
                            <rect x="62" y="66" width="36" height="5" rx="2.5" class="step__line" />
                            <circle cx="122" cy="66" r="13" class="step__accent" />
                            <path d="M116 66l4 4 8-9" stroke="var(--brand-900)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg v-else-if="step.art === 'customize'" viewBox="0 0 160 110" fill="none">
                            <rect x="18" y="14" width="124" height="82" rx="10" class="step__shape-soft" />
                            <rect x="30" y="26" width="46" height="34" rx="6" fill="var(--brand-100)" />
                            <rect x="30" y="68" width="60" height="6" rx="3" class="step__line" />
                            <rect x="30" y="80" width="40" height="6" rx="3" class="step__line" />
                            <circle cx="112" cy="38" r="9" class="step__accent" />
                            <circle cx="128" cy="52" r="9" fill="var(--brand-500)" />
                            <circle cx="112" cy="66" r="9" fill="var(--ink-200)" />
                        </svg>
                        <svg v-else viewBox="0 0 160 110" fill="none">
                            <rect x="20" y="20" width="120" height="72" rx="10" class="step__shape-soft" />
                            <rect x="34" y="34" width="52" height="8" rx="4" class="step__line" />
                            <rect x="34" y="50" width="70" height="8" rx="4" class="step__line" />
                            <rect x="34" y="68" width="44" height="12" rx="6" class="step__accent" />
                            <path d="M112 46l16 16m0-16l-16 16" stroke="var(--brand-700)" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                    <p class="step__number" aria-hidden="true">{{ step.number }}</p>
                    <h3 class="step__title">{{ step.title }}</h3>
                    <p class="step__body">{{ step.body }}</p>
                </li>
            </ol>

            <DomainVisual />

            <div class="section__cta">
                <AppButton size="lg" @click="openCta('how-it-works')">{{ copy.cta }}</AppButton>
            </div>
        </div>
    </section>
</template>

<script setup>
import AppButton from '../ui/AppButton.vue';
import SectionHeading from '../ui/SectionHeading.vue';
import DomainVisual from './DomainVisual.vue';
import { steps as copy } from '../../data/site';
import { openCta } from '../../lib/cta';
</script>
