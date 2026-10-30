/**
 * Reads the JSON bootstrap object Laravel prints into the Blade shell.
 * Everything that a non-developer may want to change (video, feature
 * availability, client data, contact details) comes from here.
 */
const raw = window.__SELLPATA__ ?? {};

export const config = {
    brand: raw.brand ?? {
        name: 'SellPata',
        domain: 'sellpata.com',
        tagline: 'ফেসবুক উদ্যোক্তাদের জন্য সহজ ব্র্যান্ডেড ল্যান্ডিং পেজ।',
    },
    youtubeUrl: (raw.youtube_url ?? '').trim(),
    templateCountLabel: (raw.template_count_label ?? '').trim(),
    features: {
        payments: raw.features?.payments ?? { available: false, coming_soon_label: 'শিগগিরই আসছে' },
        shipping: raw.features?.shipping ?? { available: false, coming_soon_label: 'শিগগিরই আসছে' },
    },
    contact: raw.contact ?? {},
    // Templates, benefits, steps, clients and reviews managed in the admin panel.
    content: raw.content ?? {},
    routes: normaliseRoutes(raw.routes),
};

/**
 * Laravel hands over absolute URLs (route() output). Internal navigation has to
 * stay relative so vue-router resolves it inside the SPA; the API endpoint stays
 * absolute because fetch() uses it directly.
 */
function normaliseRoutes(routes = {}) {
    const toPath = (value = '') => {
        if (!value) return '';
        try {
            const url = new URL(value, window.location.origin);
            return url.pathname + url.search;
        } catch {
            return value;
        }
    };

    return {
        home: toPath(routes.home) || '/',
        clients: toPath(routes.clients) || '/clients',
        contact: toPath(routes.contact) || '/contact',
        contact_endpoint: routes.contact_endpoint || '/api/contact',
    };
}

export function hasContactChannel(key) {
    const value = (config.contact?.[key] ?? '').trim();
    return value.length > 0 ? value : '';
}

export default config;
