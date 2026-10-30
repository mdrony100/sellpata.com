<template>
    <footer class="site-footer">
        <div class="container site-footer__inner">
            <div class="site-footer__brand">
                <RouterLink :to="routes.home" class="wordmark wordmark--light">Sell<span>Pata</span></RouterLink>
                <p class="site-footer__text">{{ footerCopy.description }}</p>
            </div>

            <nav class="site-footer__nav" aria-label="ফুটার মেনু">
                <RouterLink v-for="item in navigation" :key="item.to" :to="item.to" class="site-footer__link">
                    {{ item.label }}
                </RouterLink>
            </nav>

            <nav class="site-footer__legal" aria-label="আইনি">
                <RouterLink to="/blog" class="site-footer__link">ব্লগ</RouterLink>
                <RouterLink to="/privacy-policy" class="site-footer__link">প্রাইভেসি পলিসি</RouterLink>
                <RouterLink to="/terms" class="site-footer__link">শর্তাবলি</RouterLink>
            </nav>
            <p v-if="contactLinks.length" class="site-footer__contact">
                <a v-for="link in contactLinks" :key="link.href" :href="link.href">{{ link.label }}</a>
            </p>
        </div>

        <div class="container site-footer__legal">
            <p>© {{ year }} {{ config.brand.name }}। সর্বস্বত্ব সংরক্ষিত।</p>
        </div>
    </footer>
</template>

<script setup>
import { navigation, footer as footerCopy } from '../../data/site';
import config, { hasContactChannel } from '../../lib/config';

const routes = config.routes;
const year = new Date().getFullYear();

const contactLinks = [
    hasContactChannel('phone') && { label: hasContactChannel('phone'), href: `tel:${hasContactChannel('phone')}` },
    hasContactChannel('email') && { label: hasContactChannel('email'), href: `mailto:${hasContactChannel('email')}` },
].filter(Boolean);
</script>
