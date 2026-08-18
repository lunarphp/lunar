<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        disabled?: boolean;
        ariaLabel?: string;
    }>(),
    { modelValue: '', disabled: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const HEX_RE = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/;

const isValid = computed(() => !props.modelValue || HEX_RE.test((props.modelValue ?? '').trim()));

// Native <input type="color"> only accepts #RRGGBB, so coerce 3-digit hex.
const swatchValue = computed(() => {
    const value = (props.modelValue ?? '').trim();

    if (/^#[0-9a-fA-F]{6}$/.test(value)) {
        return value;
    }

    if (/^#[0-9a-fA-F]{3}$/.test(value)) {
        const [, a, b, c] = value;

        return `#${a}${a}${b}${b}${c}${c}`;
    }

    return '#000000';
});

const onText = (event: Event): void => {
    let next = (event.target as HTMLInputElement).value.trim();

    if (next && !next.startsWith('#')) {
        next = `#${next}`;
    }

    emit('update:modelValue', next.toUpperCase());
};

const onPicker = (event: Event): void => {
    emit('update:modelValue', (event.target as HTMLInputElement).value.toUpperCase());
};
</script>

<template>
    <div
        :class="[
            'flex border rounded-md bg-surface overflow-hidden focus-within:ring-3',
            isValid
                ? 'border-line-strong focus-within:border-sage focus-within:ring-sage/35'
                : 'border-danger focus-within:border-danger focus-within:ring-danger/25',
        ]"
    >
        <label
            class="relative flex items-center justify-center w-8 h-8 shrink-0 border-r border-line cursor-pointer"
            :title="modelValue || ariaLabel"
        >
            <span class="block w-4 h-4 rounded-sm border border-line" :style="{ backgroundColor: swatchValue }" />
            <input
                type="color"
                class="absolute inset-0 opacity-0 cursor-pointer"
                :value="swatchValue"
                :disabled="disabled"
                :aria-label="ariaLabel"
                @input="onPicker"
            />
        </label>
        <input
            type="text"
            class="flex-1 min-w-0 h-8 px-2.5 bg-transparent text-[13px] font-mono text-ink-900 outline-none border-0 uppercase tracking-[0.02em] disabled:opacity-50"
            placeholder="#000000"
            :value="modelValue"
            :disabled="disabled"
            @input="onText"
        />
    </div>
</template>
