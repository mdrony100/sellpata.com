const SITE_NAME = 'SellPata';

/**
 * Bengali titles and descriptions per route. Kept beside the router so the
 * document head always matches the visible page.
 */
export const pages = {
    home: {
        title: 'SellPata — ফেসবুকের ব্যবসার জন্য নিজের ব্র্যান্ডের ল্যান্ডিং পেজ',
        description:
            'ফেসবুক উদ্যোক্তাদের জন্য রেডিমেড ল্যান্ডিং পেজ। পছন্দের ডিজাইন বেছে নিন, পণ্যের ছবি ও তথ্য যোগ করুন, নিজের ব্র্যান্ডের রঙে সাজান—কোডিং ছাড়াই।',
    },
    clients: {
        title: 'আমাদের ক্লায়েন্ট — SellPata',
        description:
            'SellPata দিয়ে নিজের ব্র্যান্ডের ল্যান্ডিং পেজ তৈরি করা উদ্যোক্তাদের তালিকা ও নমুনা ডিজাইন।',
    },
    contact: {
        title: 'যোগাযোগ — SellPata',
        description: 'আপনার ফেসবুক ব্যবসার জন্য ব্র্যান্ডেড ল্যান্ডিং পেজ নিয়ে কথা বলুন SellPata টিমের সঙ্গে।',
    },
    notFound: {
        title: 'পেজটি খুঁজে পাওয়া যায়নি — SellPata',
        description: 'আপনি যে পেজটি খুঁজছেন সেটি নেই। হোম পেজে ফিরে যান।',
    },
};

export function applySeo(key) {
    const page = pages[key] ?? pages.home;
    document.title = page.title;
    setMeta('name', 'description', page.description);
    setMeta('property', 'og:title', page.title);
    setMeta('property', 'og:description', page.description);
    document.documentElement.setAttribute('lang', 'bn');
}

function setMeta(attribute, name, content) {
    let tag = document.head.querySelector(`meta[${attribute}="${name}"]`);
    if (!tag) {
        tag = document.createElement('meta');
        tag.setAttribute(attribute, name);
        document.head.appendChild(tag);
    }
    tag.setAttribute('content', content);
}

export { SITE_NAME };


/** Robots directive per route (builder and demo pages stay out of search). */
export function applyRobots(route) {
    const value = route?.meta?.robots ?? 'index, follow';
    let tag = document.querySelector('meta[name="robots"]');
    if (!tag) {
        tag = document.createElement('meta');
        tag.setAttribute('name', 'robots');
        document.head.appendChild(tag);
    }
    tag.setAttribute('content', value);
}
