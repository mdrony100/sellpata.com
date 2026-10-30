/**
 * Static marketing copy. Bengali text lives here (not inside components) so
 * wording changes never touch layout code.
 */

export const navigation = [
    { label: 'হোম', to: '/' },
    { label: 'আমাদের ক্লায়েন্ট', to: '/clients' },
    { label: 'যোগাযোগ', to: '/contact' },
];

export const primaryCtaLabel = 'ল্যান্ডিং পেজ নিন';

export const hero = {
    eyebrow: 'ফেসবুক উদ্যোক্তাদের জন্য',
    heading: ['আপনার পণ্য, আপনার ব্র্যান্ড—', 'নিজের ল্যান্ডিং পেজে বিক্রি করুন।'],
    body:
        'রেডিমেড ডিজাইন বেছে নিন, পণ্যের ছবি ও তথ্য যোগ করুন, নিজের ব্র্যান্ডের রঙে সাজান। কোডিংয়ের ঝামেলা ছাড়াই শুরু করুন।',
    primaryCta: primaryCtaLabel,
    secondaryLink: 'কীভাবে কাজ করে দেখুন',
    benefits: 'রেডিমেড ডিজাইন • নিজের ব্র্যান্ড • সহজ কাস্টমাইজেশন',
    videoTitle: 'SellPata কীভাবে ব্যবহার করবেন?',
};

export const showcase = {
    heading: 'আপনার ব্যবসার জন্য মানানসই ডিজাইন',
    body: 'যে পণ্যই বিক্রি করুন, বেছে নিন আপনার ক্যাটাগরির ল্যান্ডিং পেজ।',
    allLabel: 'সব',
    sampleBadge: 'নমুনা ডিজাইন',
    sectionCta: 'আপনার ল্যান্ডিং পেজ নিন',
    defaultCountLabel: 'রেডিমেড ল্যান্ডিং পেজের সংগ্রহ',
};

export const benefits = [
    {
        key: 'ready',
        title: 'রেডিমেড ল্যান্ডিং পেজ',
        body: 'শুরু থেকে ডিজাইন করার ঝামেলা ছাড়াই পছন্দের ডিজাইন বেছে নিন।',
    },
    {
        key: 'category',
        title: 'ক্যাটাগরি অনুযায়ী ডিজাইন',
        body: 'আপনার পণ্যের ধরন অনুযায়ী সাজানো টেমপ্লেট।',
    },
    {
        key: 'custom',
        title: 'সহজ কাস্টমাইজেশন',
        body: 'লোগো, রঙ, ছবি ও লেখা বদলে নিজের ব্র্যান্ডে সাজান।',
    },
    {
        key: 'payments',
        title: 'পেমেন্ট গেটওয়ে',
        body: 'অনলাইন পেমেন্ট নেওয়ার সুবিধা।',
        feature: 'payments',
    },
    {
        key: 'shipping',
        title: 'শিপিং সার্ভিস',
        body: 'ডেলিভারি ব্যবস্থাপনার সুবিধা।',
        feature: 'shipping',
    },
];

export const steps = {
    heading: 'বেছে নিন। সাজান। শুরু করুন।',
    items: [
        {
            number: '১',
            title: 'ডিজাইন বেছে নিন',
            body: 'আপনার পণ্যের ক্যাটাগরি থেকে পছন্দের টেমপ্লেট নির্বাচন করুন।',
            art: 'choose',
        },
        {
            number: '২',
            title: 'নিজের মতো সাজান',
            body: 'পণ্যের ছবি, দাম, বিবরণ, লোগো ও ব্র্যান্ডের রঙ যোগ করুন।',
            art: 'customize',
        },
        {
            number: '৩',
            title: 'লিংক শেয়ার করুন',
            body: 'আপনার ল্যান্ডিং পেজের লিংক ফেসবুক পোস্ট, বিজ্ঞাপন ও মেসেঞ্জারে দিন।',
            art: 'share',
        },
    ],
    cta: 'আমার ল্যান্ডিং পেজ চাই',
};

export const domainSection = {
    heading: 'লিংকেও থাকুক আপনার ব্র্যান্ডের পরিচয়',
    body: 'SellPata সাবডোমেইন অথবা নিজের ডোমেইনে আপনার ব্র্যান্ডের ল্যান্ডিং পেজ।',
    note: 'ঠিকানাগুলো কেবল উদাহরণ।',
    instantMessage: 'এক ক্লিকে টেমপ্লেট দিয়ে শুরু করুন',
    options: [
        { id: 'subdomain', label: 'SellPata সাবডোমেইন', value: 'yourbrand.sellpata.com' },
        { id: 'custom', label: 'নিজস্ব ডোমেইন', value: 'www.yourbrand.com' },
    ],
};

export const proof = {
    heading: 'উদ্যোক্তাদের ব্র্যান্ড, SellPata-তে',
    intro:
        'আমাদের ক্লায়েন্টদের ল্যান্ডিং পেজ আর তাদের অভিজ্ঞতা এখানে দেখা যাবে। এখন যেগুলো দেখছেন, সেগুলো নমুনা প্রিভিউ।',
    sampleLabel: 'নমুনা প্রিভিউ',
    emptyReviews: 'উদ্যোক্তাদের অভিজ্ঞতা শিগগিরই এখানে যুক্ত হবে।',
    clientsLink: 'আমাদের ক্লায়েন্ট',
    cta: 'আপনার ব্র্যান্ডও শুরু করুন',
};

export const finalCta = {
    heading: 'আপনার ফেসবুক ব্যবসাকে দিন নিজের ঠিকানা',
    body: 'পছন্দের ডিজাইন দিয়ে শুরু করুন আপনার ব্র্যান্ডের ল্যান্ডিং পেজ।',
    cta: primaryCtaLabel,
};

export const footer = {
    description: 'ফেসবুক উদ্যোক্তাদের জন্য সহজ ব্র্যান্ডেড ল্যান্ডিং পেজ।',
};

export const ctaDialog = {
    title: 'আপনার ল্যান্ডিং পেজের শুরু এখানেই',
    body: 'ল্যান্ডিং পেজ চালু করার বিস্তারিত প্রক্রিয়া শিগগিরই যুক্ত হবে। আপাতত আমাদের সঙ্গে যোগাযোগ করুন।',
    contact: 'যোগাযোগ করুন',
    close: 'বন্ধ করুন',
};

export const clientsPage = {
    heading: 'আমাদের ক্লায়েন্ট',
    intro:
        'SellPata দিয়ে নিজের ব্র্যান্ডের ল্যান্ডিং পেজ তৈরি করেছেন যেসব উদ্যোক্তা। তাদের প্রতিটি পেজ আলাদা, প্রতিটি ব্র্যান্ড আলাদা।',
    emptyTitle: 'ক্লায়েন্ট তালিকা এখনো যুক্ত হয়নি',
    emptyBody: 'প্রথম উদ্যোক্তাদের পেজ চালু হলেই এখানে তাদের ব্র্যান্ড ও লাইভ লিংক দেখা যাবে।',
    sampleAreaTitle: 'নমুনা ডিজাইন',
    sampleAreaBody: 'ক্লায়েন্ট তালিকা যুক্ত হওয়ার আগে আমাদের নমুনা ডিজাইনগুলো দেখে নিতে পারেন।',
    cta: primaryCtaLabel,
};

export const contactPage = {
    heading: 'আপনার ব্যবসা নিয়ে কথা বলুন',
    intro:
        'ফর্মটি পূরণ করুন, আমরা আপনার পণ্য ও ব্র্যান্ড অনুযায়ী মানানসই ল্যান্ডিং পেজ বেছে নিতে সাহায্য করব।',
    form: {
        name: 'আপনার নাম',
        namePlaceholder: 'যেমন: রুমানা আক্তার',
        phone: 'মোবাইল নম্বর',
        phonePlaceholder: '০১৭XXXXXXXX',
        pageUrl: 'ফেসবুক পেজের লিংক',
        pageUrlOptional: '(ঐচ্ছিক)',
        pageUrlPlaceholder: 'https://facebook.com/yourpage',
        message: 'আপনার বার্তা',
        messagePlaceholder: 'আপনার পণ্য কী, কী ধরনের ল্যান্ডিং পেজ চান—সংক্ষেপে লিখুন।',
        submit: 'বার্তা পাঠান',
        submitting: 'পাঠানো হচ্ছে…',
        successTitle: 'বার্তা পৌঁছেছে',
        successBody: 'ধন্যবাদ! খুব শিগগিরই আমরা যোগাযোগ করব।',
        errorTitle: 'বার্তা পাঠানো যায়নি',
        errorBody: 'একটু পরে আবার চেষ্টা করুন। সমস্যা থাকলে সরাসরি যোগাযোগ করুন।',
        throttleBody: 'একটু বেশি বার চেষ্টা হয়েছে। এক মিনিট পর আবার পাঠান।',
    },
};
