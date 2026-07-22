<script setup lang="ts">
import { computed } from 'vue';

export interface PreviewValue {
    name?: string;
    colour?: string | null;
    swatch?: string | null;
}

const props = withDefaults(
    defineProps<{
        // Parent option's type — drives which preview shape to render.
        type?: string;
        value?: PreviewValue;
        // 24px in list cells, 32px in edit-page rows.
        size?: number;
    }>(),
    { type: 'text', value: () => ({}), size: 24 },
);

// Perceived luminance close to 1 means a light colour that needs a visible
// border so it doesn't disappear against the surface.
const isLight = (hex?: string | null): boolean => {
    if (!hex) {
        return false;
    }

    const value = hex.replace('#', '');

    if (value.length !== 6 && value.length !== 3) {
        return false;
    }

    const expanded = value.length === 3 ? value.split('').map((c) => c + c).join('') : value;
    const r = parseInt(expanded.slice(0, 2), 16);
    const g = parseInt(expanded.slice(2, 4), 16);
    const b = parseInt(expanded.slice(4, 6), 16);

    return (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.85;
};

const dim = computed(() => `${props.size}px`);
</script>

<template>
    <!-- text -->
    <span
        v-if="type === 'text'"
        :class="[
            'inline-flex items-center rounded-full bg-canvas border border-line text-ink-700 font-medium whitespace-nowrap',
            size <= 24 ? 'h-[22px] px-2 text-[11px]' : 'h-[26px] px-2.5 text-[12px]',
        ]"
    >{{ value?.name || '—' }}</span>

    <!-- colour -->
    <span
        v-else-if="type === 'colour'"
        :class="[
            'inline-block rounded-md shrink-0',
            isLight(value?.colour) ? 'border border-line-strong' : 'border border-transparent',
        ]"
        :style="{ width: dim, height: dim, backgroundColor: value?.colour || '#e5e5e5' }"
        :title="value?.name"
    />

    <!-- swatch -->
    <span
        v-else-if="type === 'swatch'"
        :class="[
            'inline-block rounded-md shrink-0 bg-cover bg-center',
            value?.swatch ? 'border border-line' : 'border border-dashed border-line-strong bg-canvas',
        ]"
        :style="{ width: dim, height: dim, backgroundImage: value?.swatch ? `url('${value.swatch}')` : undefined }"
        :title="value?.name"
    />

    <!-- unknown / future -->
    <span
        v-else
        :class="[
            'inline-flex items-center rounded-full bg-warn-soft border border-dashed border-warn-border text-warn-ink font-medium whitespace-nowrap',
            size <= 24 ? 'h-[22px] px-2 text-[10.5px]' : 'h-[26px] px-2.5 text-[11.5px]',
        ]"
        :title="`Unknown type: ${type}`"
    >{{ value?.name || type }}</span>
</template>
