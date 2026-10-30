export const landingKey = Symbol('landing');

export const LANDING_DESIGNS = ['prakritik', 'abhijatya', 'smart', 'komol', 'sorasari'];

export function isLandingDesign(design) {
    return LANDING_DESIGNS.includes(design);
}

const BENGALI = '০১২৩৪৫৬৭৮৯';

export function parseMoney(value) {
    if (value === null || value === undefined || value === '') return 0;
    if (typeof value === 'number') return Math.max(0, Math.round(value));
    const ascii = String(value).replace(/[০-৯]/g, (digit) => String(BENGALI.indexOf(digit)));
    const digits = ascii.replace(/[^0-9]/g, '');
    return digits ? Number(digits) : 0;
}

export function formatMoney(value) {
    return `৳${Number(value || 0).toLocaleString('en-IN')}`;
}

export function isAvailable(row) {
    return !(row?.available === false || row?.available === 0 || row?.available === '0' || row?.available === 'false');
}

export function pointsOf(bundle) {
    if (Array.isArray(bundle?.points)) return bundle.points.filter(Boolean);
    return String(bundle?.points || '').split(/[,،]/).map((part) => part.trim()).filter(Boolean);
}

export function youtubeId(url) {
    if (!url) return null;
    const match = String(url).match(/(?:youtube\.com\/watch\?(?:.*&)?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([\w-]{11})/);
    return match ? match[1] : null;
}

export function luminance(hex) {
    const raw = String(hex || '').replace('#', '');
    if (!/^[0-9a-fA-F]{6}$/.test(raw)) return 0;
    const channels = [0, 2, 4].map((index) => {
        const value = parseInt(raw.slice(index, index + 2), 16) / 255;
        return value <= 0.03928 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
}

export function buttonColors(hex, fallback) {
    const color = /^#[0-9a-fA-F]{6}$/.test(hex || '') ? hex : fallback;
    return { bg: color, fg: luminance(color) > 0.62 ? '#1c1917' : '#ffffff' };
}

const FLOWS = {
    prakritik: ['hero', 'benefits', 'details', 'gallery', 'selection', 'reviews', 'policies', 'faq', 'order'],
    abhijatya: ['hero', 'gallery', 'benefits', 'selection', 'details', 'reviews', 'policies', 'faq', 'order'],
    smart: ['hero', 'details', 'selection', 'benefits', 'gallery', 'policies', 'faq', 'reviews', 'order'],
    komol: ['hero', 'details', 'gallery', 'benefits', 'selection', 'reviews', 'policies', 'faq', 'order'],
    sorasari: ['hero', 'benefits', 'gallery', 'selection', 'details', 'policies', 'faq', 'order'],
};

const ALLOWED = new Set(['hero', 'benefits', 'details', 'gallery', 'selection', 'reviews', 'policies', 'faq', 'order']);

export function sectionFlow(content, design) {
    const incoming = Array.isArray(content?.section_order) ? content.section_order.filter((key) => ALLOWED.has(key)) : [];
    const flow = [...(incoming.length ? incoming : (FLOWS[design] || FLOWS.prakritik))];
    if (!flow.includes('hero')) flow.unshift('hero');
    if (!flow.includes('order')) flow.push('order');
    return flow;
}

export function sectionShown(content, key) {
    if (key === 'hero' || key === 'order') return true;
    if (content?.sections && content.sections[key] === false) return false;
    if (key === 'selection') {
        const mode = content?.selection?.mode;
        if (mode === 'variants') return (content.variants || []).length > 0;
        if (mode === 'bundles') return (content.bundles || []).length > 0;
        return false;
    }
    if (key === 'reviews') return true;
    if (key === 'gallery') return (content?.gallery || []).some((item) => item?.src);
    return true;
}

export function deliveryAreas(content) {
    const areas = content?.delivery?.areas;
    if (!Array.isArray(areas)) return [];
    return areas
        .filter((area) => area && area.id && area.name)
        .map((area) => ({ id: String(area.id), name: String(area.name), charge: parseMoney(area.charge) }));
}

export function visibleReviews(content, { demo, editable }) {
    const rows = Array.isArray(content?.reviews) ? content.reviews.filter((row) => row && (row.text || row.name)) : [];
    if (demo || editable) return rows;
    return rows.filter((row) => !row.sample && String(row.sample) !== '1');
}

export function faqAnswer(item, ctx) {
    const bound = boundAnswer(item?.bind, ctx);
    if (item?.bind && bound) return bound;
    return item?.a || '';
}

function boundAnswer(bind, ctx) {
    const areas = ctx.areas || [];
    if (bind === 'how_to_order') {
        return 'নিচের ফর্মে নাম, মোবাইল নম্বর, সম্পূর্ণ ঠিকানা ও ডেলিভারি এলাকা দিন। তারপর «অর্ডার নিশ্চিত করুন» চাপুন।';
    }
    if (bind === 'delivery_charge') {
        if (areas.length) return areas.map((area) => `${area.name}: ৳${area.charge.toLocaleString('en-IN')}`).join(' · ');
        if (ctx.fallbackDelivery?.enabled) return `ডেলিভারি চার্জ ৳${Number(ctx.fallbackDelivery.charge || 0).toLocaleString('en-IN')}।`;
        return 'ডেলিভারি চার্জ এখনো সেট করা হয়নি।';
    }
    if (bind === 'delivery_areas') {
        return areas.length ? `${areas.map((area) => area.name).join(', ')}।` : 'ডেলিভারি এলাকা এখনো যোগ করা হয়নি।';
    }
    if (bind === 'payment') {
        const methods = ctx.payments || [];
        if (!methods.length) return 'এই মুহূর্তে কোনো পেমেন্ট পদ্ধতি চালু নেই।';
        return `${methods.map((method) => method.label).join(', ')}। যে পদ্ধতি পেমেন্ট নিতে পারে না, সেটি দেখানো হয় না।`;
    }
    if (bind === 'returns') {
        const text = String(ctx.policies?.returns || '').trim();
        return text || 'বিক্রেতা আলাদা কোনো পরিবর্তন বা ফেরতের নিয়ম যোগ করেননি।';
    }
    return '';
}

export function colorGroups(variants) {
    const groups = [];
    for (const variant of variants) {
        const name = variant.color || '';
        if (!name) continue;
        let group = groups.find((item) => item.name === name);
        if (!group) {
            group = { name, swatch: variant.swatch || '#ccc', image: variant.image || '', items: [] };
            groups.push(group);
        }
        group.items.push(variant);
    }
    return groups;
}
