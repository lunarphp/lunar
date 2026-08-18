<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import Icon from './Icon.vue';

type Tone = 'neutral' | 'sage' | 'warn' | 'danger';

const props = withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        /** Exact value shown on hover when value is abbreviated. */
        valueTitle?: string;
        hint?: string;
        tone?: Tone;
        icon?: string | null;
        delta?: { value: string; tone?: Tone } | null;
        active?: boolean;
    }>(),
    { valueTitle: undefined, hint: '', tone: 'neutral', icon: null, delta: null, active: false },
);

const TONE: Record<Tone, string> = {
    neutral: 'bg-surface-2 border-line text-ink-700',
    sage: 'bg-sage-soft border-sage-border text-sage-ink',
    warn: 'bg-warn-soft border-warn-border text-warn-ink',
    danger: 'bg-danger-soft border-danger-border text-danger',
};

const tileCls = computed(() => TONE[props.tone]);
const deltaCls = computed(() => (props.delta ? TONE[props.delta.tone ?? 'neutral'] : ''));

// A card is only a button — with hover/press affordances — when a click
// handler is actually bound; plain stat tiles render as static divs.
const attrs = useAttrs();
const interactive = computed(() => !!attrs.onClick);
</script>

<template>
    <component
        :is="interactive ? 'button' : 'div'"
        :type="interactive ? 'button' : undefined"
        :class="[
            'group text-left bg-surface border rounded-xl shadow-sm p-3.5 flex flex-col gap-2',
            interactive
                ? 'transition-[background-color,border-color,box-shadow,transform] duration-100 hover:bg-surface-2 hover:border-ink-300 active:translate-y-[0.5px] active:shadow-none focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35'
                : '',
            active ? 'border-ink-900 ring-1 ring-ink-900/10' : 'border-line',
        ]"
    >
        <div class="flex items-center gap-2">
            <div v-if="icon" :class="['w-7 h-7 rounded-md grid place-items-center border shrink-0', tileCls]">
                <Icon :name="icon" cls="sm" />
            </div>
            <div class="text-[10px] uppercase tracking-[0.06em] text-ink-500 font-medium">{{ label }}</div>
        </div>
        <div class="flex items-end gap-2 mt-0.5">
            <div class="text-[26px] leading-none font-semibold tracking-[-0.02em] [font-variant-numeric:tabular-nums] text-ink-900 truncate" :title="valueTitle">{{ value }}</div>
            <div
                v-if="delta"
                :class="['inline-flex items-center h-[18px] px-1.5 rounded-full border text-[10px] font-medium', deltaCls]"
            >{{ delta.value }}</div>
        </div>
        <div v-if="hint" class="text-[11px] text-ink-500">{{ hint }}</div>
        <slot />
    </component>
</template>
