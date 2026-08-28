<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import FieldLabel from './FieldLabel.vue';
import TextInput from './TextInput.vue';

const { t } = useI18n();

interface CurrencyOption {
    id: number;
    code: string;
    decimal_places: number;
    default: boolean;
}

const props = defineProps<{
    /** The whole `data` payload; this block owns only its min_prices entry. */
    modelValue: Record<string, unknown>;
    currencies: CurrencyOption[];
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [Record<string, unknown>] }>();

const minPrices = (): Record<string, unknown> => ({ ...(props.modelValue.min_prices as Record<string, unknown> ?? {}) });

const minPriceFor = (code: string): string | number => (minPrices()[code] as string | number | null) ?? '';

const setMinPrice = (code: string, value: unknown): void => {
    emit('update:modelValue', { ...props.modelValue, min_prices: { ...minPrices(), [code]: value } });
};

const stepFor = (currency: CurrencyOption): string =>
    (currency.decimal_places > 0 ? `0.${'0'.repeat(currency.decimal_places - 1)}1` : '1');
</script>

<template>
    <div>
        <FieldLabel>{{ t('discounts.field_min_spend') }}</FieldLabel>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
            <div v-for="currency in currencies" :key="currency.code">
                <TextInput
                    :id="`discount-min-spend-${currency.code}`"
                    :model-value="minPriceFor(currency.code)"
                    type="number"
                    min="0"
                    :step="stepFor(currency)"
                    :invalid="!!errors?.[`data.min_prices.${currency.code}`]"
                    @update:model-value="setMinPrice(currency.code, $event)"
                >
                    <template #prefix><span class="text-[11px] font-mono">{{ currency.code }}</span></template>
                </TextInput>
                <div v-if="errors?.[`data.min_prices.${currency.code}`]" class="mt-1 text-[11px] text-danger">
                    {{ errors[`data.min_prices.${currency.code}`] }}
                </div>
            </div>
        </div>
        <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_min_spend_hint') }}</div>
    </div>
</template>
