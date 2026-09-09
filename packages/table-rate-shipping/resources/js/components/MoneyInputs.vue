<script setup lang="ts">
// One numeric input per enabled currency, in major units. The step follows
// the currency's decimal places so a zero-decimal currency takes whole units.
import { TextInput } from '@lunarphp/panel';
import { type CurrencyOption, stepFor } from '../lib/types';

const props = defineProps<{
    modelValue: Record<string, string | number | null>;
    currencies: CurrencyOption[];
    errors?: Record<string, string>;
    errorPrefix?: string;
    requireDefault?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: Record<string, string | number | null>] }>();

const errorFor = (code: string): string | undefined => props.errors?.[`${props.errorPrefix ?? ''}${code}`];

const set = (code: string, value: string): void => {
    emit('update:modelValue', { ...props.modelValue, [code]: value === '' ? null : value });
};
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <div v-for="currency in currencies" :key="currency.code">
            <TextInput
                :model-value="modelValue[currency.code] ?? ''"
                type="number"
                min="0"
                :step="stepFor(currency)"
                :invalid="!!errorFor(currency.code)"
                :aria-label="currency.name"
                @update:model-value="set(currency.code, $event)"
            >
                <template #prefix>
                    <span class="text-[11px] font-mono">{{ currency.code }}</span>
                </template>
                <template v-if="requireDefault && currency.default" #suffix>
                    <span class="text-[10px] text-ink-500">*</span>
                </template>
            </TextInput>
            <div v-if="errorFor(currency.code)" class="mt-1 text-[11px] text-danger">{{ errorFor(currency.code) }}</div>
        </div>
    </div>
</template>
