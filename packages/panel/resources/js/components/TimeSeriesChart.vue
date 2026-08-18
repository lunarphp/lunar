<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

export interface ChartPoint {
    label: string;
    /** Numeric value in major units; drives the geometry. */
    value: number;
    /** Preformatted value for the tooltip (e.g. money); falls back to formatValue/compact. */
    display?: string;
}

const props = withDefaults(
    defineProps<{
        points: ChartPoint[];
        height?: number;
        /** Fixed pixel width; when omitted the chart tracks its container. */
        width?: number;
        /** Formats y-axis tick values (and tooltip values without a display). */
        formatValue?: (value: number) => string;
        ariaLabel: string;
    }>(),
    { height: 180 },
);

// The chart tracks its container width so the SVG renders at real pixel size
// (no preserveAspectRatio scaling, which would distort text).
const container = ref<HTMLElement | null>(null);
const measuredWidth = ref(0);
let observer: ResizeObserver | undefined;

onMounted(() => {
    measuredWidth.value = container.value?.clientWidth ?? 0;

    if (typeof ResizeObserver !== 'undefined' && container.value && !props.width) {
        observer = new ResizeObserver((entries) => {
            measuredWidth.value = entries[0]?.contentRect.width ?? measuredWidth.value;
        });
        observer.observe(container.value);
    }
});

onBeforeUnmount(() => observer?.disconnect());

const width = computed(() => props.width ?? measuredWidth.value);

const margin = { top: 8, right: 8, bottom: 22, left: 48 };
const innerWidth = computed(() => Math.max(0, width.value - margin.left - margin.right));
const innerHeight = computed(() => Math.max(0, props.height - margin.top - margin.bottom));

// Round the axis maximum up to a "nice" 1/2/2.5/5 step so ticks land on
// readable values; a flat zero series still gets a scale.
const axisMax = computed(() => {
    const max = Math.max(...props.points.map((point) => point.value), 0);

    if (max <= 0) {
        return 1;
    }

    const power = Math.pow(10, Math.floor(Math.log10(max)));

    for (const step of [1, 2, 2.5, 5, 10]) {
        if (max <= step * power) {
            return step * power;
        }
    }

    return 10 * power;
});

const xAt = (index: number): number =>
    props.points.length > 1 ? (index / (props.points.length - 1)) * innerWidth.value : innerWidth.value / 2;

const yAt = (value: number): number => innerHeight.value - (value / axisMax.value) * innerHeight.value;

const linePath = computed(() =>
    props.points.map((point, i) => `${i === 0 ? 'M' : 'L'}${xAt(i).toFixed(1)},${yAt(point.value).toFixed(1)}`).join(' '));

const areaPath = computed(() => {
    if (!props.points.length) {
        return '';
    }

    return `${linePath.value} L${xAt(props.points.length - 1).toFixed(1)},${innerHeight.value} L${xAt(0).toFixed(1)},${innerHeight.value} Z`;
});

const compact = (value: number): string =>
    new Intl.NumberFormat(undefined, { notation: 'compact', maximumFractionDigits: 1 }).format(value);

const tickLabel = (value: number): string => (props.formatValue ? props.formatValue(value) : compact(value));

// Two gridlines plus the baseline keep the grid recessive, and halves of a
// 1/2/2.5/5 axis maximum always land on readable values.
const ticks = computed(() => [0.5, 1].map((fraction) => fraction * axisMax.value));

// Thin x labels so they never collide; always keep first and last, and drop
// a stepped label that would land right next to the final one.
const labelStep = computed(() => Math.max(1, Math.ceil(props.points.length / 8)));
const showLabel = (index: number): boolean => {
    const last = props.points.length - 1;

    if (index === last) {
        return true;
    }

    return index % labelStep.value === 0 && last - index >= labelStep.value;
};

// Remounting the series paths (via :key) restarts their draw-in animation
// whenever the data changes, e.g. when a range switcher swaps the buckets.
// Compared by value: partial reloads hand over a fresh but identical array
// (e.g. after a draft commit), which must not replay the animation.
const animationKey = ref(0);
watch(() => props.points, (points, previous) => {
    if (JSON.stringify(points) !== JSON.stringify(previous)) {
        animationKey.value++;
    }
});

// Hover/focus: a crosshair snaps to the nearest bucket; the same readout is
// reachable by keyboard (arrow keys).
const activeIndex = ref<number | null>(null);

const indexFromClientX = (clientX: number): number => {
    const rect = container.value?.getBoundingClientRect();

    if (!rect || props.points.length === 0 || innerWidth.value === 0) {
        return 0;
    }

    const position = (clientX - rect.left - margin.left) / innerWidth.value;

    return Math.min(props.points.length - 1, Math.max(0, Math.round(position * (props.points.length - 1))));
};

const onPointerMove = (event: PointerEvent): void => {
    activeIndex.value = indexFromClientX(event.clientX);
};

const onKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
        event.preventDefault();
        const delta = event.key === 'ArrowRight' ? 1 : -1;
        const current = activeIndex.value ?? (delta === 1 ? -1 : props.points.length);
        activeIndex.value = Math.min(props.points.length - 1, Math.max(0, current + delta));
    } else if (event.key === 'Escape') {
        activeIndex.value = null;
    }
};

const activePoint = computed(() => (activeIndex.value === null ? null : props.points[activeIndex.value] ?? null));

const activeDisplay = computed(() => {
    const point = activePoint.value;

    if (!point) {
        return '';
    }

    return point.display ?? tickLabel(point.value);
});

// Keep the tooltip inside the chart bounds.
const tooltipStyle = computed(() => {
    if (activeIndex.value === null) {
        return {};
    }

    const anchor = margin.left + xAt(activeIndex.value);

    return {
        left: `${Math.min(Math.max(anchor, 56), Math.max(width.value - 56, 56))}px`,
        top: `${Math.max(margin.top + yAt(activePoint.value?.value ?? 0) - 12, 0)}px`,
    };
});
</script>

<template>
    <div
        v-if="points.length"
        ref="container"
        class="relative w-full select-none"
        tabindex="0"
        @pointermove="onPointerMove"
        @pointerleave="activeIndex = null"
        @keydown="onKeydown"
        @blur="activeIndex = null"
    >
        <svg :width="width" :height="height" role="img" :aria-label="ariaLabel" class="block">
            <g :transform="`translate(${margin.left},${margin.top})`">
                <!-- Gridlines (recessive) + baseline -->
                <line
                    v-for="tick in ticks"
                    :key="`grid-${tick}`"
                    :x1="0"
                    :x2="innerWidth"
                    :y1="yAt(tick)"
                    :y2="yAt(tick)"
                    style="stroke: var(--color-line)"
                />
                <line :x1="0" :x2="innerWidth" :y1="innerHeight" :y2="innerHeight" style="stroke: var(--color-line-strong)" />

                <!-- Y tick labels -->
                <text
                    v-for="tick in ticks"
                    :key="`tick-${tick}`"
                    :x="-8"
                    :y="yAt(tick) + 3"
                    text-anchor="end"
                    class="text-[10px]"
                    style="fill: var(--color-ink-500)"
                >{{ tickLabel(tick) }}</text>

                <!-- Series; keyed so new data replays the draw-in animation. -->
                <path :key="`area-${animationKey}`" class="tsc-area" :d="areaPath" style="fill: var(--color-chart-1-soft)" fill-opacity="0.6" />
                <path
                    :key="`line-${animationKey}`"
                    class="tsc-line"
                    :d="linePath"
                    pathLength="1"
                    fill="none"
                    style="stroke: var(--color-chart-1)"
                    stroke-width="2"
                    stroke-linejoin="round"
                    stroke-linecap="round"
                />

                <!-- X labels (thinned) -->
                <template v-for="(point, i) in points" :key="`label-${i}`">
                    <text
                        v-if="showLabel(i)"
                        :x="xAt(i)"
                        :y="innerHeight + 15"
                        :text-anchor="i === 0 ? 'start' : i === points.length - 1 ? 'end' : 'middle'"
                        class="text-[10px]"
                        style="fill: var(--color-ink-500)"
                    >{{ point.label }}</text>
                </template>

                <!-- Crosshair + active point -->
                <template v-if="activeIndex !== null && activePoint">
                    <line
                        :x1="xAt(activeIndex)"
                        :x2="xAt(activeIndex)"
                        :y1="0"
                        :y2="innerHeight"
                        style="stroke: var(--color-ink-300)"
                        stroke-dasharray="2,3"
                    />
                    <circle
                        :cx="xAt(activeIndex)"
                        :cy="yAt(activePoint.value)"
                        r="4"
                        style="fill: var(--color-chart-1); stroke: var(--color-surface)"
                        stroke-width="2"
                    />
                </template>
            </g>
        </svg>

        <!-- Tooltip: the value leads, the bucket label follows. -->
        <div
            v-if="activeIndex !== null && activePoint"
            class="pointer-events-none absolute -translate-x-1/2 -translate-y-full rounded-md border border-line bg-surface px-2 py-1 shadow-sm whitespace-nowrap text-center"
            :style="tooltipStyle"
            role="status"
        >
            <div class="text-[12px] font-semibold text-ink-900 [font-variant-numeric:tabular-nums]">{{ activeDisplay }}</div>
            <div class="text-[10.5px] text-ink-500">{{ activePoint.label }}</div>
        </div>
    </div>
</template>

<style scoped>
/* Draw the line left to right (pathLength="1" normalises the dash math),
   then fade the area in under it. */
.tsc-line {
    stroke-dasharray: 1;
    animation: tsc-draw 700ms cubic-bezier(0.33, 0, 0.2, 1) both;
}

.tsc-area {
    animation: tsc-fade 500ms ease-out 250ms both;
}

@keyframes tsc-draw {
    from {
        stroke-dashoffset: 1;
    }
    to {
        stroke-dashoffset: 0;
    }
}

@keyframes tsc-fade {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@media (prefers-reduced-motion: reduce) {
    .tsc-line,
    .tsc-area {
        animation: none;
    }
}
</style>
