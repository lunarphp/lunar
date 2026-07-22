<script setup lang="ts">
import { ToggleGroupItem, ToggleGroupRoot } from 'reka-ui';
import { useI18n } from 'vue-i18n';

defineProps<{ modelValue: string }>();

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const { t } = useI18n();

const OPTIONS = ['today', '7d', '30d', '90d'];
</script>

<template>
    <ToggleGroupRoot
        type="single"
        :model-value="modelValue"
        class="inline-flex border border-line-strong rounded-md p-0.5 bg-surface-2 gap-0.5 shadow-sm"
        @update:model-value="(value) => { if (value) emit('update:modelValue', value as string); }"
    >
        <ToggleGroupItem
            v-for="option in OPTIONS"
            :key="option"
            :value="option"
            :class="[
                'h-[26px] px-2.5 rounded-sm text-[12px] font-medium transition-[background-color,color,box-shadow] duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35',
                modelValue === option
                    ? 'bg-surface text-ink-900 shadow-[0_1px_0_rgba(0,0,0,0.04),inset_0_0_0_1px_var(--color-line)]'
                    : 'text-ink-500 hover:text-ink-900',
            ]"
        >
            {{ t(`dashboard.range_${option}`) }}
        </ToggleGroupItem>
    </ToggleGroupRoot>
</template>
