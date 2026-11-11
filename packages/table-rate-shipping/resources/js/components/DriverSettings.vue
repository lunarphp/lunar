<script setup lang="ts">
// The settings block for the selected driver, keyed on the driver key. Drivers
// the panel knows nothing about get no block; their data is left untouched.
import { useI18n } from 'vue-i18n';
import { FieldLabel, Select, Toggle } from '@lunarphp/panel';
import MoneyInputs from './MoneyInputs.vue';
import type { CurrencyOption } from '../lib/types';

export interface DriverData {
    charge_by: 'cart_total' | 'weight';
    use_discount_amount: boolean;
    minimum_spend: Record<string, string | number | null>;
}

const props = defineProps<{
    driver: string;
    modelValue: DriverData;
    currencies: CurrencyOption[];
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: DriverData] }>();

const { t } = useI18n();

const update = (patch: Partial<DriverData>): void => {
    emit('update:modelValue', { ...props.modelValue, ...patch });
};
</script>

<template>
    <div v-if="driver === 'ship-by'" class="max-w-[340px]">
        <FieldLabel>{{ t('shipping::methods.field_charge_by') }}</FieldLabel>
        <Select :model-value="modelValue.charge_by" @update:model-value="update({ charge_by: $event as 'cart_total' | 'weight' })">
            <option value="cart_total">{{ t('shipping::methods.charge_by_cart_total') }}</option>
            <option value="weight">{{ t('shipping::methods.charge_by_weight') }}</option>
        </Select>
    </div>

    <div v-else-if="driver === 'free-shipping'" class="flex flex-col gap-4">
        <div>
            <FieldLabel>{{ t('shipping::methods.field_minimum_spend') }}</FieldLabel>
            <MoneyInputs :model-value="modelValue.minimum_spend" :currencies="currencies" :errors="errors" error-prefix="data.minimum_spend." @update:model-value="update({ minimum_spend: $event })" />
            <div class="mt-1 text-[11.5px] text-ink-500">{{ t('shipping::methods.minimum_spend_hint') }}</div>
        </div>
        <label class="flex items-center gap-3 cursor-pointer">
            <Toggle :on="modelValue.use_discount_amount" @toggle="update({ use_discount_amount: !modelValue.use_discount_amount })" />
            <span class="text-[12.5px] text-ink-900 font-medium">{{ t('shipping::methods.field_use_discount_amount') }}</span>
        </label>
    </div>

    <div v-else class="text-xs text-ink-500">{{ t('shipping::methods.driver_desc_none') }}</div>
</template>
