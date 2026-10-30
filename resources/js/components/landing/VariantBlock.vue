<template>
    <section class="block picker">
        <h2>{{ title }}</h2>

        <div v-if="landing.mode === 'bundles'" class="picker__bundles" role="radiogroup" aria-label="প্যাকেজ">
            <button
                v-for="bundle in landing.bundles"
                :key="bundle.id"
                type="button"
                role="radio"
                class="picker__card"
                :class="{ 'is-on': String(landing.selectedBundleId) === String(bundle.id), 'is-off': !available(bundle) }"
                :aria-checked="String(landing.selectedBundleId) === String(bundle.id)"
                :disabled="!available(bundle)"
                @click="landing.chooseBundle(bundle.id)"
            >
                <img v-if="bundle.image" :src="bundle.image" alt="" />
                <strong>{{ bundle.name }}</strong>
                <span>{{ bundle.detail }}</span>
                <em>{{ landing.formatMoney(money(bundle.price)) }}</em>
                <ul v-if="points(bundle).length">
                    <li v-for="point in points(bundle)" :key="point">{{ point }}</li>
                </ul>
                <small v-if="!available(bundle)">এখন নেই</small>
            </button>
        </div>

        <template v-else>
            <div v-if="groups.length" class="picker__swatches" role="radiogroup" aria-label="রঙ">
                <button
                    v-for="group in groups"
                    :key="group.name"
                    type="button"
                    class="swatch"
                    :class="{ 'is-on': group.name === activeColor }"
                    :aria-pressed="group.name === activeColor"
                    @click="pickColor(group)"
                >
                    <i :style="{ background: group.swatch }" />
                    {{ group.name }}
                </button>
            </div>
            <div class="picker__sizes" role="radiogroup" :aria-label="sizeLabel">
                <button
                    v-for="variant in sizeRows"
                    :key="variant.id"
                    type="button"
                    role="radio"
                    class="size"
                    :class="{ 'is-on': String(landing.selectedVariantId) === String(variant.id), 'is-off': !available(variant) }"
                    :aria-checked="String(landing.selectedVariantId) === String(variant.id)"
                    :disabled="!available(variant)"
                    @click="landing.chooseVariant(variant.id)"
                >
                    <span>{{ variant.size || variant.name }}</span>
                    <em>{{ landing.formatMoney(money(variant.price)) }}</em>
                    <small v-if="!available(variant)">এখন নেই</small>
                </button>
            </div>
        </template>

        <label class="qty">
            পরিমাণ
            <input :value="landing.quantity" type="number" min="1" max="99" inputmode="numeric" @input="landing.setQuantity($event.target.value)" />
        </label>
    </section>
</template>

<script setup>
import { computed, inject } from 'vue';
import { colorGroups, isAvailable, landingKey, parseMoney, pointsOf } from '../../landing/helpers';

const landing = inject(landingKey);
const groups = computed(() => colorGroups(landing.variants));
const activeColor = computed(() => landing.selectedVariant?.color || groups.value[0]?.name || '');
const sizeRows = computed(() => {
    if (!groups.value.length) return landing.variants;
    return groups.value.find((group) => group.name === activeColor.value)?.items || landing.variants;
});
const title = computed(() => landing.mode === 'bundles' ? 'প্যাকেজ বেছে নিন' : 'পছন্দ করুন');
const sizeLabel = computed(() => landing.design === 'abhijatya' ? 'হাতের মাপ' : 'অপশন');

function available(row) {
    return isAvailable(row);
}
function money(value) {
    return parseMoney(value);
}
function points(bundle) {
    return pointsOf(bundle);
}
function pickColor(group) {
    const next = group.items.find((item) => isAvailable(item)) || group.items[0];
    if (next && isAvailable(next)) landing.chooseVariant(next.id);
}
</script>
