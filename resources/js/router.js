import { createRouter, createWebHistory } from 'vue-router';
import HomeView from './views/HomeView.vue';
import ClientsView from './views/ClientsView.vue';
import ContactView from './views/ContactView.vue';
import NotFoundView from './views/NotFoundView.vue';
import DiscoveryView from './views/DiscoveryView.vue';
import BuilderView from './views/BuilderView.vue';
import DemoView from './views/DemoView.vue';
import { applyRobots, applySeo } from './lib/seo';

const routes = [
    { path: '/', name: 'home', component: HomeView, meta: { seo: 'home' } },
    { path: '/clients', name: 'clients', component: ClientsView, meta: { seo: 'clients' } },
    { path: '/contact', name: 'contact', component: ContactView, meta: { seo: 'contact' } },
    {
        path: '/start',
        name: 'start',
        component: DiscoveryView,
        meta: { bare: true, title: 'শুরু করুন — SellPata', description: 'ক্যাটাগরি বেছে নিন এবং আপনার ব্যবসার জন্য মানানসই ল্যান্ডিং পেজ ডিজাইন খুঁজে নিন।' },
    },
    {
        path: '/builder/:publicId',
        name: 'builder',
        component: BuilderView,
        meta: { bare: true, title: 'ল্যান্ডিং পেজ সাজান — SellPata', robots: 'noindex, nofollow' },
    },
    {
        path: '/demo/:slug',
        name: 'demo',
        component: DemoView,
        meta: { bare: true, title: 'নমুনা ডিজাইন — SellPata', robots: 'noindex, nofollow' },
    },
    { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundView, meta: { seo: 'notFound' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        if (to.hash) return { el: to.hash, behavior: 'smooth', top: 88 };
        return { top: 0 };
    },
});

router.afterEach((to) => {
    applySeo(to.meta?.seo ?? 'home');
    applyRobots(to);
});

export default router;
