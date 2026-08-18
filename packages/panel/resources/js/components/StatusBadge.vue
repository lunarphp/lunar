<script setup lang="ts">
import { computed } from 'vue';
import Icon from './Icon.vue';

type Tone = 'sage' | 'warn' | 'danger' | 'archived' | 'neutral';

const props = withDefaults(
    defineProps<{
        tone?: Tone;
        size?: 'sm' | 'md';
        dot?: boolean;
        mono?: boolean;
        icon?: string;
    }>(),
    { tone: 'neutral', size: 'md', icon: '' },
);

const TONE: Record<Tone, { wrap: string; dot: string }> = {
    sage: { wrap: 'bg-sage-soft border-sage-border text-sage-ink', dot: 'bg-current' },
    warn: { wrap: 'bg-warn-soft border-warn-border text-warn-ink', dot: 'bg-current' },
    danger: { wrap: 'bg-danger-soft border-danger-border text-danger', dot: 'bg-current' },
    archived: { wrap: 'bg-surface border-line text-ink-700', dot: 'bg-ink-400' },
    neutral: { wrap: 'bg-surface-2 border-line text-ink-700', dot: 'bg-ink-400' },
};

const tone = computed(() => TONE[props.tone] ?? TONE.neutral);
const sizeCls = computed(() => (props.size === 'sm' ? 'h-[18px] px-1.5 text-[10px]' : 'h-[22px] px-2 text-[11px]'));
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-full font-medium border whitespace-nowrap',
            sizeCls,
            tone.wrap,
            mono ? 'font-mono tracking-[-0.01em]' : '',
        ]"
    >
        <span v-if="dot" class="w-1.5 h-1.5 rounded-full" :class="tone.dot" />
        <Icon v-if="icon" :name="icon" cls="sm" />
        <slot />
    </span>
</template>
