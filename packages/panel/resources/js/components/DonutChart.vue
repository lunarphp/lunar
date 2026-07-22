<script setup lang="ts">
import { computed } from 'vue';

export interface DonutSegment {
    label: string;
    /** Numeric value; drives the arc geometry and the legend share. */
    value: number;
    /** Preformatted value for the legend (e.g. money); falls back to the raw value. */
    display?: string;
}

const props = withDefaults(
    defineProps<{
        segments: DonutSegment[];
        size?: number;
        thickness?: number;
        /** Centre readout, e.g. the formatted total. */
        centreLabel?: string;
        centreValue?: string;
        /** Exact value shown on hover when centreValue is abbreviated. */
        centreTitle?: string;
        showLegend?: boolean;
        /** Label for the folded remainder when more than four segments are passed. */
        otherLabel?: string;
        ariaLabel: string;
    }>(),
    { size: 120, thickness: 14, centreLabel: undefined, centreValue: undefined, centreTitle: undefined, showLegend: true, otherLabel: 'Other' },
);

// Four fixed palette slots, assigned in order and never cycled; segments
// beyond the fourth fold into a single neutral "Other".
const SLOT_COLORS = [
    'var(--color-chart-1)',
    'var(--color-chart-2)',
    'var(--color-chart-3)',
    'var(--color-chart-4)',
];
const OTHER_COLOR = 'var(--color-ink-300)';

const displaySegments = computed((): Array<DonutSegment & { color: string }> => {
    const source = props.segments;

    if (source.length <= SLOT_COLORS.length) {
        return source.map((segment, i) => ({ ...segment, color: SLOT_COLORS[i] }));
    }

    const kept = source.slice(0, SLOT_COLORS.length - 1);
    const rest = source.slice(SLOT_COLORS.length - 1);

    return [
        ...kept.map((segment, i) => ({ ...segment, color: SLOT_COLORS[i] })),
        { label: props.otherLabel, value: rest.reduce((sum, s) => sum + s.value, 0), color: OTHER_COLOR },
    ];
});

const total = computed(() => displaySegments.value.reduce((sum, s) => sum + Math.max(0, s.value), 0));

const centre = computed(() => props.size / 2);
const radius = computed(() => (props.size - props.thickness) / 2);

// Annular arc path for a [start, end) fraction of the ring, starting at 12 o'clock.
const arcPath = (startFraction: number, endFraction: number): string => {
    const clamped = Math.min(endFraction, startFraction + 0.9999);
    const angle = (fraction: number): number => fraction * 2 * Math.PI - Math.PI / 2;
    const point = (fraction: number): string => {
        const a = angle(fraction);
        return `${(centre.value + radius.value * Math.cos(a)).toFixed(2)} ${(centre.value + radius.value * Math.sin(a)).toFixed(2)}`;
    };
    const largeArc = clamped - startFraction > 0.5 ? 1 : 0;

    return `M ${point(startFraction)} A ${radius.value} ${radius.value} 0 ${largeArc} 1 ${point(clamped)}`;
};

const arcs = computed(() => {
    if (total.value <= 0) {
        return [];
    }

    const visible = displaySegments.value.filter((segment) => segment.value > 0);

    // A 2px gap between neighbouring segments, as a fraction of the ring;
    // skipped for a single segment so a full ring stays closed.
    const gap = visible.length > 1 ? 2 / (2 * Math.PI * radius.value) : 0;

    let cursor = 0;

    return visible.map((segment) => {
        const start = cursor;
        cursor += segment.value / total.value;

        return { ...segment, path: arcPath(start + gap / 2, cursor - gap / 2) };
    });
});

const share = (segment: DonutSegment): string =>
    total.value > 0 ? `${Math.round((Math.max(0, segment.value) / total.value) * 100)}%` : '0%';
</script>

<template>
    <div class="flex items-center gap-4 min-w-0">
        <div class="relative shrink-0" :style="{ width: `${size}px`, height: `${size}px` }">
            <svg :width="size" :height="size" role="img" :aria-label="ariaLabel" class="block">
                <!-- Empty ring when there is nothing to plot -->
                <circle
                    v-if="!arcs.length"
                    :cx="centre"
                    :cy="centre"
                    :r="radius"
                    fill="none"
                    style="stroke: var(--color-line)"
                    :stroke-width="thickness"
                />
                <path
                    v-for="(arc, i) in arcs"
                    :key="i"
                    :d="arc.path"
                    fill="none"
                    :style="{ stroke: arc.color }"
                    :stroke-width="thickness"
                >
                    <title>{{ arc.label }}: {{ arc.display ?? arc.value }}</title>
                </path>
            </svg>
            <div
                v-if="centreValue || centreLabel"
                class="absolute inset-0 grid place-items-center text-center pointer-events-none"
            >
                <div>
                    <div
                        v-if="centreValue"
                        class="text-[15px] leading-tight font-semibold tracking-[-0.01em] text-ink-900 [font-variant-numeric:tabular-nums] pointer-events-auto"
                        :title="centreTitle"
                    >{{ centreValue }}</div>
                    <div v-if="centreLabel" class="text-[10px] text-ink-500">{{ centreLabel }}</div>
                </div>
            </div>
        </div>

        <ul v-if="showLegend" class="flex-1 min-w-0 flex flex-col gap-1.5 m-0 p-0 list-none">
            <li v-for="(segment, i) in displaySegments" :key="i" class="flex items-center gap-2 min-w-0">
                <span class="w-2.5 h-2.5 rounded-[3px] shrink-0" :style="{ background: segment.color }" aria-hidden="true" />
                <span class="text-[12px] text-ink-700 truncate flex-1">{{ segment.label }}</span>
                <span class="text-[12px] font-medium text-ink-900 [font-variant-numeric:tabular-nums] shrink-0">{{ segment.display ?? segment.value }}</span>
                <span class="text-[11px] text-ink-500 [font-variant-numeric:tabular-nums] w-9 text-right shrink-0">{{ share(segment) }}</span>
            </li>
        </ul>
    </div>
</template>
