<template>
    <form class="contact-form" novalidate @submit.prevent="submit">
        <div class="field">
            <label for="name">{{ copy.name }}</label>
            <input
                id="name"
                v-model="form.name"
                name="name"
                type="text"
                autocomplete="name"
                :placeholder="copy.namePlaceholder"
                :aria-invalid="Boolean(errors.name)"
                :aria-describedby="errors.name ? 'name-error' : undefined"
                @blur="touch('name')"
            />
            <p v-if="errors.name" id="name-error" class="field__error">{{ errors.name }}</p>
        </div>

        <div class="field">
            <label for="phone">{{ copy.phone }}</label>
            <input
                id="phone"
                v-model="form.phone"
                name="phone"
                type="tel"
                inputmode="tel"
                autocomplete="tel"
                :placeholder="copy.phonePlaceholder"
                :aria-invalid="Boolean(errors.phone)"
                :aria-describedby="errors.phone ? 'phone-error' : undefined"
                @blur="touch('phone')"
            />
            <p v-if="errors.phone" id="phone-error" class="field__error">{{ errors.phone }}</p>
        </div>

        <div class="field">
            <label for="page_url">
                {{ copy.pageUrl }} <span class="field__optional">{{ copy.pageUrlOptional }}</span>
            </label>
            <input
                id="page_url"
                v-model="form.page_url"
                name="page_url"
                type="url"
                autocomplete="url"
                :placeholder="copy.pageUrlPlaceholder"
                :aria-invalid="Boolean(errors.page_url)"
                :aria-describedby="errors.page_url ? 'page_url-error' : undefined"
                @blur="touch('page_url')"
            />
            <p v-if="errors.page_url" id="page_url-error" class="field__error">{{ errors.page_url }}</p>
        </div>

        <div class="field">
            <label for="message">{{ copy.message }}</label>
            <textarea
                id="message"
                v-model="form.message"
                name="message"
                rows="5"
                :placeholder="copy.messagePlaceholder"
                :aria-invalid="Boolean(errors.message)"
                :aria-describedby="errors.message ? 'message-error' : undefined"
                @blur="touch('message')"
            />
            <p v-if="errors.message" id="message-error" class="field__error">{{ errors.message }}</p>
        </div>

        <p v-if="status === 'error'" class="form-alert form-alert--error" role="alert">
            <strong>{{ copy.errorTitle }}</strong> {{ errorBody }}
        </p>

        <div v-if="status === 'success'" class="form-alert form-alert--success" role="status">
            <strong>{{ copy.successTitle }}</strong> {{ successMessage }}
        </div>

        <AppButton type="submit" size="lg" :disabled="status === 'sending'">
            {{ status === 'sending' ? copy.submitting : copy.submit }}
        </AppButton>
    </form>
</template>

<script setup>
import { reactive, ref } from 'vue';
import AppButton from '../ui/AppButton.vue';
import { contactPage } from '../../data/site';
import config from '../../lib/config';

const copy = contactPage.form;

const form = reactive({ name: '', phone: '', page_url: '', message: '' });
const errors = reactive({ name: '', phone: '', page_url: '', message: '' });
const touched = reactive({ name: false, phone: false, page_url: false, message: false });

const status = ref('idle'); // idle | sending | success | error
const successMessage = ref('');
const errorBody = ref(copy.errorBody);

const phonePattern = /^(?:\+?88)?01[3-9]\d{8}$/;

function validateField(field) {
    const value = form[field].trim();

    if (field === 'name') errors.name = value.length < 2 ? 'আপনার নাম লিখুন।' : '';
    if (field === 'phone') errors.phone = phonePattern.test(value.replace(/[\s\-()]/g, ''))
        ? ''
        : 'সঠিক মোবাইল নম্বর লিখুন, যেমন ০১৭XXXXXXXX।';
    if (field === 'page_url') {
        errors.page_url = !value || /^https?:\/\/.+\..+/.test(value)
            ? ''
            : 'লিংকটি সঠিকভাবে লিখুন (https:// দিয়ে শুরু করুন)।';
    }
    if (field === 'message') errors.message = value.length < 5 ? 'আপনার বার্তা লিখুন।' : '';
}

function touch(field) {
    touched[field] = true;
    validateField(field);
}

function validateAll() {
    ['name', 'phone', 'page_url', 'message'].forEach((field) => {
        touched[field] = true;
        validateField(field);
    });
    return !Object.values(errors).some(Boolean);
}

async function submit() {
    if (!validateAll()) {
        status.value = 'idle';
        return;
    }

    status.value = 'sending';
    errorBody.value = copy.errorBody;

    try {
        const response = await fetch(config.routes.contact_endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({
                name: form.name.trim(),
                phone: form.phone.trim(),
                page_url: form.page_url.trim() || null,
                message: form.message.trim(),
            }),
        });

        if (response.status === 201) {
            const payload = await response.json();
            successMessage.value = payload.message ?? copy.successBody;
            status.value = 'success';
            form.name = '';
            form.phone = '';
            form.page_url = '';
            form.message = '';
            Object.keys(touched).forEach((key) => (touched[key] = false));
            return;
        }

        if (response.status === 422) {
            const payload = await response.json();
            const fieldErrors = payload.errors ?? {};
            Object.keys(errors).forEach((key) => {
                errors[key] = Array.isArray(fieldErrors[key]) ? fieldErrors[key][0] : '';
            });
            errorBody.value = payload.message ?? copy.errorBody;
            status.value = 'error';
            return;
        }

        if (response.status === 429) {
            errorBody.value = copy.throttleBody;
            status.value = 'error';
            return;
        }

        status.value = 'error';
    } catch {
        status.value = 'error';
    }
}
</script>
