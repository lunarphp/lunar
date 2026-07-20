<script setup lang="ts">
import type { FocalPoint } from './FocalPointEditor.vue';

defineProps<{
    src: string;
    alt?: string | null;
    focal: FocalPoint;
    ratio: string;
    label?: string;
}>();
</script>

<template>
    <div class="min-w-0">
        <div
            class="rounded-md overflow-hidden border border-line bg-surface-2"
            :style="{ aspectRatio: ratio.replace(':', ' / ') }"
        >
            <img
                :src="src"
                :alt="alt ?? ''"
                class="w-full h-full object-cover block"
                :style="{ objectPosition: `${focal.x}% ${focal.y}%` }"
                loading="lazy"
            />
        </div>
        <div v-if="label" class="mt-1 text-center text-[10.5px] text-ink-500 [font-variant-numeric:tabular-nums]">{{ label }}</div>
    </div>
</template>
