<template>
    <div class="domain">
        <div class="domain__head">
            <h3 class="domain__title">{{ copy.heading }}</h3>
            <p class="domain__body">{{ copy.body }}</p>
        </div>

        <ul class="domain__options">
            <li v-for="option in copy.options" :key="option.id" class="domain__option">
                <span class="domain__label">{{ option.label }}</span>
                <code class="domain__value">{{ option.value }}</code>
            </li>
        </ul>

        <p class="domain__note">{{ copy.note }}</p>
        <p class="domain__instant">
            <span aria-hidden="true">⚡</span> {{ copy.instantMessage }}
        </p>
    </div>
</template>

<script setup>
import { domainSection as copy } from '../../data/site';
</script>
