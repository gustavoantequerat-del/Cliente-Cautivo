<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 20 },
    stroke: { type: [Number, String], default: 1.8 },
});

// Iconos de trazo (mismos trazos del diseño)
const PATHS = {
    home: 'M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z',
    projects: 'M3 7h18v13H3zM8 7V4h8v3',
    vault: 'M3 4h18v5H3zM5 9v11h14V9M10 13h4',
    billing: 'M6 2h12v20l-3-2-3 2-3-2-3 2zM9 7h6M9 11h6M9 15h4',
    clock: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 6v6l4 2',
    research: 'M4 20V10M10 20V4M16 20v-7M2 20h20',
    play: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM10 8l6 4-6 4z',
    credential: 'M12 2l8 4.5v9L12 20l-8-4.5v-9zM9 11l2 2 4-4',
    user: 'M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10z',
    logout: 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',
    check: 'M20 6L9 17l-5-5',
    download: 'M12 3v12M7 10l5 5 5-5M4 21h16',
    external: 'M14 4h6v6M20 4l-9 9M18 14v6H4V6h6',
    calendar: 'M4 5h16v16H4zM4 10h16M9 3v4M15 3v4',
    lock: 'M6 11h12v10H6zM8 11V7a4 4 0 0 1 8 0v4',
    shield: 'M12 2l8 4.5v9L12 20l-8-4.5v-9z',
    file: 'M6 3h9l4 4v14H6zM14 3v5h5',
    menu: 'M4 6h16M4 12h16M4 18h16',
    close: 'M6 6l12 12M18 6L6 18',
    arrow: 'M5 12h14M13 6l6 6-6 6',
    plus: 'M12 5v14M5 12h14',
    search: 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-5-5',
    grid: 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
    log: 'M4 6h16M4 12h10M4 18h7',
    alert: 'M12 3l10 18H2zM12 10v5M12 18h.01',
    fingerprint: 'M12 11v4M8 21c1-2 1.5-4 1.5-7a2.5 2.5 0 0 1 5 0c0 1.5-.2 3-.6 4.4M5 17c.7-1.6 1-3.3 1-5a6 6 0 0 1 12 0c0 .8 0 1.6-.1 2.3M17 20c.3-.8.6-1.7.8-2.6',
};

const d = computed(() => PATHS[props.name] ?? PATHS.file);
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         :stroke-width="stroke" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path :d="d" />
    </svg>
</template>
