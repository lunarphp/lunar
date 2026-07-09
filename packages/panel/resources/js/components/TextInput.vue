<script setup lang="ts">
import { computed, useSlots } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number;
        type?: string;
        placeholder?: string;
        invalid?: boolean;
        mono?: boolean;
        disabled?: boolean;
        autocomplete?: string;
        ariaLabel?: string;
    }>(),
    { modelValue: '', type: 'text' },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const slots = useSlots();
const grouped = computed(() => !!slots.prefix || !!slots.suffix);

const wrapperCls = computed(() => [
    'flex border rounded-md bg-surface overflow-hidden focus-within:ring-3',
    props.invalid
        ? 'border-danger focus-within:border-danger focus-within:ring-danger/25'
        : 'border-line-strong focus-within:border-sage focus-within:ring-sage/35',
]);

const standaloneInputCls = computed(() => [
    'w-full h-8 px-2.5 border rounded-md bg-surface text-[13px] text-ink-900 transition-[border-color,box-shadow] duration-100 hover:border-ink-300 focus:outline-none focus:ring-3',
    props.invalid
        ? 'border-danger focus:border-danger focus:ring-danger/25'
        : 'border-line-strong focus:border-sage focus:ring-sage/35',
    props.mono ? 'font-mono' : '',
]);

const groupedInputCls = computed(() => [
    'flex-1 min-w-0 h-8 px-2.5 bg-transparent text-[13px] text-ink-900 outline-none border-0',
    props.mono ? 'font-mono' : '',
]);

const onInput = (e: Event) => {
    emit('update:modelValue', (e.target as HTMLInputElement).value);
};
</script>

<template>
    <div v-if="grouped" :class="wrapperCls">
        <span
            v-if="$slots.prefix"
            class="flex items-center px-2.5 bg-surface-2 border-r border-line text-ink-500 font-mono text-xs whitespace-nowrap"
        ><slot name="prefix" /></span>
        <input
            :type="type"
            :class="groupedInputCls"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :aria-label="ariaLabel"
            @input="onInput"
        />
        <span
            v-if="$slots.suffix"
            class="flex items-center px-2.5 bg-surface-2 border-l border-line text-ink-500 font-mono text-xs whitespace-nowrap"
        ><slot name="suffix" /></span>
    </div>
    <input
        v-else
        :type="type"
        :class="standaloneInputCls"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :autocomplete="autocomplete"
        :aria-label="ariaLabel"
        @input="onInput"
    />
</template>
