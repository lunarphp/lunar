<script setup lang="ts">
export interface StatusOption {
    value: string;
    label: string;
    tone?: 'sage' | 'warn' | 'danger' | 'neutral';
}

defineProps<{
    modelValue: string;
    options: StatusOption[];
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

// Mirrors the StatusBadge tone tokens so a status keeps one colour language
// across badges and this control.
const SELECTED_TONES: Record<string, string> = {
    sage: 'bg-sage-soft border-sage-border text-sage-ink',
    warn: 'bg-warn-soft border-warn-border text-warn-ink',
    danger: 'bg-danger-soft border-danger-border text-danger',
    neutral: 'bg-surface border-line-strong text-ink-900',
};

const selectedClass = (option: StatusOption): string =>
    `${SELECTED_TONES[option.tone ?? 'neutral']} border shadow-sm`;
</script>

<template>
    <div class="grid grid-flow-col auto-cols-fr gap-1 p-1 bg-surface-2 border border-line rounded-lg" role="radiogroup">
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            role="radio"
            :aria-checked="option.value === modelValue"
            :class="[
                'h-[28px] px-2 rounded-md text-[12px] font-medium transition-[background-color,color,box-shadow] duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35',
                option.value === modelValue
                    ? selectedClass(option)
                    : 'text-ink-500 hover:text-ink-900 hover:bg-surface/60',
            ]"
            @click="emit('update:modelValue', option.value)"
        >{{ option.label }}</button>
    </div>
</template>
