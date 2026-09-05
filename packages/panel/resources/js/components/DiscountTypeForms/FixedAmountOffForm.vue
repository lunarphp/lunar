<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import FieldLabel from '../FieldLabel.vue';
import TextInput from '../TextInput.vue';

const { t } = useI18n();

interface CurrencyOption {
    id: number;
    code: string;
    decimal_places: number;
    default: boolean;
}

const props = defineProps<{
    modelValue: Record<string, unknown>;
    currencies: CurrencyOption[];
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [Record<string, unknown>] }>();

const amounts = (): Record<string, unknown> => ({ ...(props.modelValue.amounts as Record<string, unknown> ?? {}) });

const amountFor = (code: string): string | number => (amounts()[code] as string | number | null) ?? '';

const setAmount = (code: string, value: unknown): void => {
    emit('update:modelValue', { ...props.modelValue, amounts: { ...amounts(), [code]: value } });
};

// The input's step follows the currency's own decimal places, so a zero-decimal
// currency takes whole units and a three-decimal one takes thousandths.
const stepFor = (currency: CurrencyOption): string =>
    (currency.decimal_places > 0 ? `0.${'0'.repeat(currency.decimal_places - 1)}1` : '1');
</script>

<template>
    <div>
        <FieldLabel required>{{ t('discounts.field_amount') }}</FieldLabel>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
            <div v-for="currency in currencies" :key="currency.code">
                <TextInput
                    :id="`discount-amount-${currency.code}`"
                    :model-value="amountFor(currency.code)"
                    type="number"
                    min="0"
                    :step="stepFor(currency)"
                    :invalid="!!errors?.[`data.amounts.${currency.code}`]"
                    @update:model-value="setAmount(currency.code, $event)"
                >
                    <template #prefix><span class="text-[11px] font-mono">{{ currency.code }}</span></template>
                </TextInput>
                <div v-if="errors?.[`data.amounts.${currency.code}`]" class="mt-1 text-[11px] text-danger">
                    {{ errors[`data.amounts.${currency.code}`] }}
                </div>
            </div>
        </div>
        <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_amounts_hint') }}</div>
    </div>
</template>
