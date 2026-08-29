<script setup lang="ts">
import { computed, ref, useSlots } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number;
        type?: string;
        /**
         * Bound explicitly rather than left to attribute fallthrough: the
         * grouped variant's root is the wrapper div, so these would land there
         * instead of on the input — breaking label association and dropping
         * numeric constraints on any input with a prefix or suffix.
         */
        id?: string;
        min?: string | number;
        max?: string | number;
        step?: string | number;
        placeholder?: string;
        invalid?: boolean;
        mono?: boolean;
        disabled?: boolean;
        autocomplete?: string;
        ariaLabel?: string;
        /** Shows an inline clear button while the input holds a value. */
        clearable?: boolean;
    }>(),
    { modelValue: '', type: 'text' },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const { t } = useI18n();

const slots = useSlots();
// The clear button lives inside the grouped wrapper, so clearable inputs
// always render as a group even without affix slots.
const grouped = computed(() => !!slots.prefix || !!slots.suffix || props.clearable);

const showClear = computed(() => props.clearable && String(props.modelValue ?? '').length > 0 && !props.disabled);

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

const inputRef = ref<HTMLInputElement | null>(null);

const clear = (): void => {
    emit('update:modelValue', '');
    inputRef.value?.focus();
};

defineExpose({ focus: () => inputRef.value?.focus() });
</script>

<template>
    <div v-if="grouped" :class="wrapperCls">
        <span
            v-if="$slots.prefix"
            class="flex items-center px-2.5 bg-surface-2 border-r border-line text-ink-500 font-mono text-xs whitespace-nowrap"
        ><slot name="prefix" /></span>
        <input
            ref="inputRef"
            :id="id"
            :type="type"
            :class="groupedInputCls"
            :value="modelValue"
            :min="min"
            :max="max"
            :step="step"
            :placeholder="placeholder"
            :disabled="disabled"
            :autocomplete="autocomplete"
            :aria-label="ariaLabel"
            @input="onInput"
        />
        <button
            v-if="showClear"
            type="button"
            class="flex items-center px-2 text-ink-400 hover:text-ink-700 focus-visible:outline-none focus-visible:text-ink-700"
            :aria-label="t('common.clear')"
            @click="clear"
        >
            <Icon name="x" cls="sm" />
        </button>
        <span
            v-if="$slots.suffix"
            class="flex items-center px-2.5 bg-surface-2 border-l border-line text-ink-500 font-mono text-xs whitespace-nowrap"
        ><slot name="suffix" /></span>
    </div>
    <input
        v-else
        ref="inputRef"
        :id="id"
        :type="type"
        :class="standaloneInputCls"
        :value="modelValue"
        :min="min"
        :max="max"
        :step="step"
        :placeholder="placeholder"
        :disabled="disabled"
        :autocomplete="autocomplete"
        :aria-label="ariaLabel"
        @input="onInput"
    />
</template>
