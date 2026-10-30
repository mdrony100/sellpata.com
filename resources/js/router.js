import { createRouter, createWebHistory } from 'vue-router';
import HomeView from './views/HomeView.vue';
import ClientsView from './views/ClientsView.vue';
import ContactView from './views/ContactView.vue';
import NotFoundView from './views/NotFoundView.vue';
import { applySeo } from './lib/seo';

const routes = [
    { path: '/', name: 'home', component: HomeView, meta: { seo: 'home' } },
    { path: '/clients', name: 'clients', component: ClientsView, meta: { seo: 'clients' } },
    { path: '/contact', name: 'contact', component: ContactView, meta: { seo: 'contact' } },
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
});

export default router;
