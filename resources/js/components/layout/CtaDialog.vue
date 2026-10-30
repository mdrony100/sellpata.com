<template>
    <AppModal :open="ctaState.isOpen" :title="copy.title" @close="closeCta">
        <p class="modal__text">{{ copy.body }}</p>
        <template #actions>
            <AppButton :to="routes.contact" @click="closeCta">{{ copy.contact }}</AppButton>
            <AppButton variant="ghost" @click="closeCta">{{ copy.close }}</AppButton>
        </template>
    </AppModal>
</template>

<script setup>
import AppModal from '../ui/AppModal.vue';
import AppButton from '../ui/AppButton.vue';
import { ctaDialog as copy } from '../../data/site';
import { closeCta, ctaState } from '../../lib/cta';
import config from '../../lib/config';

const routes = config.routes;
</script>
