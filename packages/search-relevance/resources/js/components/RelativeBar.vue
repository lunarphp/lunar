<script setup lang="ts">
import { computed } from 'vue';

// The one custom visual: a horizontal bar for a 0..1 relative score.
const props = defineProps<{ value: number | null }>();

const width = computed(() => `${Math.round(Math.max(0, Math.min(1, props.value ?? 0)) * 100)}%`);
</script>

<template>
    <div class="flex items-center gap-2 min-w-0">
        <div class="flex-1 h-1.5 rounded-full bg-surface-2 border border-line overflow-hidden" aria-hidden="true">
            <div class="h-full bg-sage rounded-full" :style="{ width }" />
        </div>
        <span class="text-[12px] text-ink-700 [font-variant-numeric:tabular-nums] w-10 text-right">
            {{ value === null ? '-' : value.toFixed(2) }}
        </span>
    </div>
</template>
