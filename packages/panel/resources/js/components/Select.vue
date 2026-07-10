<script setup lang="ts">
defineProps<{
    modelValue?: string | number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    change: [event: Event];
}>();

const cls =
    'w-full h-8 px-2.5 pr-7 border border-line-strong rounded-md bg-surface text-[13px] text-ink-900 appearance-none bg-no-repeat transition-[border-color,box-shadow] duration-100 hover:border-ink-300 focus:outline-none focus:border-sage focus:ring-3 focus:ring-sage/35';
const style =
    "background-image: url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>\"); background-position: right 10px center";

const onChange = (e: Event) => {
    emit('update:modelValue', (e.target as HTMLSelectElement).value);
    emit('change', e);
};
</script>

<template>
    <select :class="cls" :style="style" :value="modelValue" @change="onChange">
        <slot />
    </select>
</template>
