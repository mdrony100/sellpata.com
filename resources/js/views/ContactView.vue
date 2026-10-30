<template>
    <div class="page">
        <section class="page-hero">
            <div class="container">
                <p class="eyebrow">যোগাযোগ</p>
                <h1 class="page-hero__title">{{ copy.heading }}</h1>
                <p class="page-hero__body">{{ copy.intro }}</p>
            </div>
        </section>

        <section class="section">
            <div class="container contact-layout">
                <ContactForm class="contact-layout__form" />

                <aside v-if="channels.length" class="contact-aside">
                    <h2 class="contact-aside__title">সরাসরি যোগাযোগ</h2>
                    <ul>
                        <li v-for="channel in channels" :key="channel.href">
                            <a :href="channel.href">{{ channel.label }}</a>
                        </li>
                    </ul>
                </aside>
            </div>
        </section>
    </div>
</template>

<script setup>
import ContactForm from '../components/contact/ContactForm.vue';
import { contactPage as copy } from '../data/site';
import { hasContactChannel } from '../lib/config';

const channels = [
    hasContactChannel('phone') && { label: hasContactChannel('phone'), href: `tel:${hasContactChannel('phone')}` },
    hasContactChannel('email') && { label: hasContactChannel('email'), href: `mailto:${hasContactChannel('email')}` },
    hasContactChannel('whatsapp') && {
        label: `WhatsApp: ${hasContactChannel('whatsapp')}`,
        href: `https://wa.me/${hasContactChannel('whatsapp').replace(/\D/g, '')}`,
    },
].filter(Boolean);
</script>
