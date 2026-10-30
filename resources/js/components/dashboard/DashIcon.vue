<script setup>
defineProps({
    name: { type: String, required: true },
});

const paths = {
    grid: '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
    pages: '<path d="M7 3.5h7.5L19 8v12.5a1.5 1.5 0 0 1-1.5 1.5h-10A1.5 1.5 0 0 1 6 20.5v-15A1.5 1.5 0 0 1 7.5 4H7z"/><path d="M14 3.5V8h5"/><path d="M9 12.5h6M9 16h4"/>',
    orders: '<path d="M6 7.5h12l-.8 11.2a1.5 1.5 0 0 1-1.5 1.3H8.3a1.5 1.5 0 0 1-1.5-1.3L6 7.5z"/><path d="M9 7.5V6.2A3 3 0 0 1 12 3.2 3 3 0 0 1 15 6.2v1.3"/>',
    plans: '<path d="M12 3.5 20 7.5 12 11.5 4 7.5 12 3.5z"/><path d="M4 12l8 4 8-4"/><path d="M4 16.5 12 20.5l8-4"/>',
    profile: '<circle cx="12" cy="8" r="3.2"/><path d="M5.5 19.2c1.3-2.8 3.6-4.2 6.5-4.2s5.2 1.4 6.5 4.2"/>',
    download: '<path d="M12 4v10"/><path d="m8 10.5 4 4 4-4"/><path d="M5 18.5h14"/>',
    billing: '<path d="M7 4.5h10a1.5 1.5 0 0 1 1.5 1.5v14l-2.2-1.3-2.3 1.3-2.3-1.3-2.2 1.3V6a1.5 1.5 0 0 1 1.5-1.5z"/><path d="M9 9h6M9 12.5h4"/>',
    logout: '<path d="M10 5.5H7.5A1.5 1.5 0 0 0 6 7v10a1.5 1.5 0 0 0 1.5 1.5H10"/><path d="M11 12h8"/><path d="m16.5 8.5 3.5 3.5-3.5 3.5"/>',
    external: '<path d="M14 5.5h4.5V10"/><path d="M18.5 5.5 11 13"/><path d="M10 6.5H7.2A1.7 1.7 0 0 0 5.5 8.2v8.3a1.7 1.7 0 0 0 1.7 1.7h8.3a1.7 1.7 0 0 0 1.7-1.7V14"/>',
    menu: '<path d="M4.5 7h15M4.5 12h15M4.5 17h15"/>',
    close: '<path d="m7 7 10 10M17 7 7 17"/>',
    plus: '<path d="M12 6v12M6 12h12"/>',
    alert: '<path d="M12 4.5 20 18.5H4L12 4.5z"/><path d="M12 10v3.5"/><path d="M12 16.5h.01"/>',
    check: '<path d="m6.5 12.5 3.5 3.5 7.5-8"/>',
    box: '<path d="M4.5 8.5 12 4.5l7.5 4v9L12 21.5l-7.5-4v-9z"/><path d="M12 12.5 19.5 8.5M12 12.5v9M12 12.5 4.5 8.5"/>',
    search: '<circle cx="11" cy="11" r="5.5"/><path d="m15.5 15.5 3.5 3.5"/>',
    store: '<path d="M4.5 10.5 6 5.5h12l1.5 5"/><path d="M5 10.5h14v8.5H5z"/><path d="M10 19v-4.5h4V19"/>',
    settings: '<circle cx="12" cy="12" r="3"/><path d="M12 3.2v2M12 18.8v2M3.2 12h2M18.8 12h2M5.8 5.8l1.4 1.4M16.8 16.8l1.4 1.4M18.2 5.8l-1.4 1.4M7.2 16.8l-1.4 1.4"/>',
};
</script>

<template>
    <svg
        class="dash-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.75"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        v-html="paths[name] || ''"
    />
</template>
