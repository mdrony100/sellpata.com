<template>
    <div v-if="open" class="editor theme-switch" role="dialog" aria-modal="true" aria-label="থিম পরিবর্তন" @keydown.esc="$emit('close')">
        <div class="editor__panel theme-switch__panel">
            <header>
                <h2>থিম পরিবর্তন</h2>
                <button type="button" class="btn btn--ghost btn--sm" @click="$emit('close')">বন্ধ করুন</button>
            </header>

            <p class="editor__lede">
                পণ্য, দাম, ছবি, লেখা, ভ্যারিয়েন্ট, প্রশ্ন ও অর্ডার সেটিংস থাকবে।
                সেকশনের ক্রম বা লুকানো সেকশন আপনি বদলে থাকলে সেগুলোও থাকবে।
                না বদলালে নতুন থিমের সাজানো ও হিরোর ধরন লাগবে।
            </p>

            <div class="theme-switch__grid" role="listbox" aria-label="থিম">
                <button
                    v-for="theme in themes"
                    :key="theme.id"
                    type="button"
                    class="theme-switch__card"
                    role="option"
                    :aria-selected="selected === theme.id"
                    :class="{ 'is-on': selected === theme.id }"
                    @click="pick(theme.id)"
                >
                    <img v-if="theme.thumbnail" :src="theme.thumbnail" :alt="`${theme.name} থাম্বনেইল`" />
                    <span v-else class="theme-switch__ph">{{ theme.name }}</span>
                    <strong>{{ theme.name }}</strong>
                    <small v-if="theme.id === currentId">এখনকার থিম</small>
                </button>
            </div>

            <div v-if="selectedTheme" class="theme-switch__preview">
                <p>লাইভ প্রিভিউ — {{ selectedTheme.name }}</p>
                <iframe :key="selectedTheme.slug" :src="`/demo/${selectedTheme.slug}?embed=1`" :title="`${selectedTheme.name} প্রিভিউ`" />
            </div>

            <div v-if="step === 'confirm'" class="theme-switch__confirm">
                <p><strong>{{ selectedTheme?.name }}</strong> থিমে বদল নিশ্চিত করবেন? আপনার কনটেন্ট থাকবে।</p>
                <label v-if="published">
                    <input v-model="republish" type="checkbox" />
                    প্রকাশিত পেজেও এখনই দেখান
                </label>
                <p v-if="published && !republish" class="editor__hint">চেক না করলে খসড়া বদলাবে। লাইভ পেজ আগের থিমেই থাকবে, যতক্ষণ না আবার প্রকাশ করেন।</p>
            </div>

            <p v-if="error" class="editor__error" role="alert">{{ error }}</p>

            <footer>
                <button v-if="step === 'pick'" type="button" class="btn btn--primary" :disabled="!canContinue" @click="step = 'confirm'">এগিয়ে যান</button>
                <button v-else type="button" class="btn btn--primary" :disabled="busy" @click="apply">
                    {{ busy ? 'বদলানো হচ্ছে…' : 'বদল নিশ্চিত করুন' }}
                </button>
                <button type="button" class="btn btn--ghost" @click="back">{{ step === 'confirm' ? 'পেছনে' : 'বাতিল' }}</button>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    themes: { type: Array, default: () => [] },
    currentId: { type: [Number, String], default: null },
    published: { type: Boolean, default: false },
    busy: { type: Boolean, default: false },
    error: { type: String, default: '' },
});

const emit = defineEmits(['close', 'confirm']);

const selected = ref(null);
const step = ref('pick');
const republish = ref(false);

const selectedTheme = computed(() => props.themes.find((theme) => theme.id === selected.value) ?? null);
const canContinue = computed(() => selected.value && Number(selected.value) !== Number(props.currentId));

watch(() => props.open, (isOpen) => {
    if (!isOpen) return;
    selected.value = props.currentId ?? props.themes[0]?.id ?? null;
    step.value = 'pick';
    republish.value = false;
});

function pick(id) {
    selected.value = id;
    step.value = 'pick';
}

function back() {
    if (step.value === 'confirm') {
        step.value = 'pick';
        return;
    }
    emit('close');
}

function apply() {
    emit('confirm', { templateId: selected.value, republish: republish.value });
}
</script>
