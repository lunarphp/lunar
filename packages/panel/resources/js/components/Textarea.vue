<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        invalid?: boolean;
        disabled?: boolean;
        rows?: string | number;
        ariaLabel?: string;
    }>(),
    { modelValue: '', rows: 3 },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
    input: [event: Event];
}>();

const cls = computed(() => [
    'w-full px-2.5 py-2 border rounded-md bg-surface text-[13px] text-ink-900 leading-snug resize-y transition-[border-color,box-shadow] duration-100 hover:border-ink-300 focus:outline-none focus:ring-3',
    props.invalid
        ? 'border-danger focus:border-danger focus:ring-danger/25'
        : 'border-line-strong focus:border-sage focus:ring-sage/35',
]);

const onInput = (e: Event) => {
    emit('update:modelValue', (e.target as HTMLTextAreaElement).value);
    emit('input', e);
};
</script>

<template>
    <textarea
        :class="cls"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :rows="rows"
        :aria-label="ariaLabel"
        @input="onInput"
    />
</template>
