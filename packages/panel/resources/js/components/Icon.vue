<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ name: string; cls?: string }>();

const ICONS: Record<string, string> = {
    alertTriangle: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    arrowLeft: '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
    check: '<polyline points="20 6 9 17 4 12"/>',
    copy: '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
    eye: '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/>',
    lock: '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    mail: '<rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2 6 12 13 22 6"/>',
    refresh: '<path d="M3 12a9 9 0 0 1 15.18-6.49L21 8"/><polyline points="21 3 21 8 16 8"/><path d="M21 12a9 9 0 0 1-15.18 6.49L3 16"/><polyline points="3 21 3 16 8 16"/>',
    shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
};

const icon = computed(() => ICONS[props.name] ?? '');

const sizeClass = computed(() => {
    const cls = props.cls ?? '';

    if (cls.includes('lg')) {
        return 'w-5 h-5';
    }

    if (cls.includes('sm')) {
        return 'w-3.5 h-3.5';
    }

    return 'w-4 h-4';
});
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        :class="[sizeClass, 'shrink-0 stroke-current fill-none', cls]"
        v-html="icon"
    />
</template>
