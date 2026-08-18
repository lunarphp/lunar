<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        points: number[];
        width?: number;
        height?: number;
        filled?: boolean;
        /** Announced description; omitted, the sparkline is decorative (the adjacent value carries the data). */
        ariaLabel?: string;
    }>(),
    { width: 96, height: 28, filled: true, ariaLabel: undefined },
);

const PAD = 2;

const innerWidth = computed(() => props.width - PAD * 2);
const innerHeight = computed(() => props.height - PAD * 2);

const max = computed(() => Math.max(...props.points, 0));
const min = computed(() => Math.min(...props.points, 0));
const span = computed(() => max.value - min.value || 1);

const xAt = (index: number): number =>
    PAD + (props.points.length > 1 ? (index / (props.points.length - 1)) * innerWidth.value : innerWidth.value / 2);

const yAt = (value: number): number => PAD + innerHeight.value - ((value - min.value) / span.value) * innerHeight.value;

const linePath = computed(() =>
    props.points.map((value, i) => `${i === 0 ? 'M' : 'L'}${xAt(i).toFixed(1)},${yAt(value).toFixed(1)}`).join(' '));

const areaPath = computed(() => {
    if (!props.points.length) {
        return '';
    }

    const baseline = PAD + innerHeight.value;

    return `${linePath.value} L${xAt(props.points.length - 1).toFixed(1)},${baseline} L${xAt(0).toFixed(1)},${baseline} Z`;
});
</script>

<template>
    <svg
        v-if="points.length"
        :width="width"
        :height="height"
        class="block shrink-0"
        :role="ariaLabel ? 'img' : undefined"
        :aria-label="ariaLabel"
        :aria-hidden="ariaLabel ? undefined : 'true'"
    >
        <path v-if="filled" :d="areaPath" style="fill: var(--color-chart-1-soft)" fill-opacity="0.6" />
        <path
            :d="linePath"
            fill="none"
            style="stroke: var(--color-chart-1)"
            stroke-width="1.5"
            stroke-linejoin="round"
            stroke-linecap="round"
        />
    </svg>
</template>
