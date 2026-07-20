<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    modelValue?: string | number | null;
    invalid?: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
    change: [event: Event];
}>();

// Proxy to a native select v-model so option values keep their bound type
// (null, number) instead of collapsing to the option's text via target.value.
const model = computed({
    get: () => props.modelValue ?? null,
    set: (value: string | number | null) => emit('update:modelValue', value),
});

const cls = computed(() => [
    'w-full h-8 px-2.5 pr-7 border rounded-md bg-surface text-[13px] text-ink-900 appearance-none bg-no-repeat transition-[border-color,box-shadow] duration-100 hover:border-ink-300 focus:outline-none focus:ring-3',
    props.invalid
        ? 'border-danger focus:border-danger focus:ring-danger/25'
        : 'border-line-strong focus:border-sage focus:ring-sage/35',
]);
const style =
    "background-image: url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>\"); background-position: right 10px center";

const onChange = (e: Event) => {
    emit('change', e);
};
</script>

<template>
    <select :class="cls" :style="style" v-model="model" @change="onChange">
        <slot />
    </select>
</template>
