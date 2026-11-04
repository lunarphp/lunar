<script setup lang="ts">
// The tier rows of a rate. The threshold column is a minimum spend (major
// units, scaled per currency on the server) or a minimum weight in the
// method's unit, according to how the selected method charges.
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button, Select, TextInput } from '@lunarphp/panel';
import { type CurrencyOption, type NamedOption, stepFor } from '../lib/types';

export type Tier = {
    customer_group_id: number | null;
    currency_code: string;
    min_quantity: string | number;
    price: string | number;
};

const props = defineProps<{
    modelValue: Tier[];
    currencies: CurrencyOption[];
    customerGroups: NamedOption[];
    chargeBy: 'cart_total' | 'weight';
    weightUnit: string;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: Tier[]] }>();

const { t } = useI18n();

const byWeight = computed(() => props.chargeBy === 'weight');

const currencyFor = (code: string): CurrencyOption | undefined => props.currencies.find((currency) => currency.code === code);

const update = (index: number, patch: Partial<Tier>): void => {
    emit('update:modelValue', props.modelValue.map((tier, i) => (i === index ? ({ ...tier, ...patch } as Tier) : tier)));
};

const add = (): void => {
    emit('update:modelValue', [
        ...props.modelValue,
        { customer_group_id: null, currency_code: props.currencies[0]?.code ?? '', min_quantity: '', price: '' },
    ]);
};

const remove = (index: number): void => {
    emit('update:modelValue', props.modelValue.filter((_, i) => i !== index));
};

const errorFor = (index: number, field: string): string | undefined => props.errors?.[`tiers.${index}.${field}`];

const gridStyle = { gridTemplateColumns: 'minmax(0, 1.2fr) 110px minmax(0, 1fr) minmax(0, 1fr) 36px' };
</script>

<template>
    <div>
        <div v-if="!modelValue.length" class="px-6 py-6 text-center text-xs text-ink-500 border border-dashed border-line rounded-md">
            {{ t('shipping::zones.no_tiers') }}
        </div>

        <div v-else class="bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
            <div
                class="grid items-center gap-3 px-3.5 py-2.5 bg-surface-2 border-b border-line text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium min-w-max"
                :style="gridStyle"
            >
                <div>{{ t('shipping::zones.tier_customer_group') }}</div>
                <div>{{ t('shipping::zones.tier_currency') }}</div>
                <div>{{ byWeight ? t('shipping::zones.tier_min_weight', { unit: weightUnit }) : t('shipping::zones.tier_min_spend') }}</div>
                <div>{{ t('shipping::zones.tier_price') }}</div>
                <div />
            </div>
            <div
                v-for="(tier, index) in modelValue"
                :key="index"
                class="grid items-start gap-3 px-3.5 py-2 border-b border-line last:border-b-0 min-w-max"
                :style="gridStyle"
            >
                <Select :model-value="tier.customer_group_id ?? ''" @update:model-value="update(index, { customer_group_id: $event === '' || $event === null ? null : Number($event) })">
                    <option value="">{{ t('shipping::zones.tier_any_group') }}</option>
                    <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                </Select>
                <Select :model-value="tier.currency_code" @update:model-value="update(index, { currency_code: String($event) })">
                    <option v-for="currency in currencies" :key="currency.code" :value="currency.code">{{ currency.code }}</option>
                </Select>
                <div>
                    <TextInput
                        :model-value="tier.min_quantity"
                        type="number"
                        min="0"
                        :step="byWeight ? '1' : stepFor(currencyFor(tier.currency_code) ?? { decimal_places: 2 })"
                        :invalid="!!errorFor(index, 'min_quantity')"
                        @update:model-value="update(index, { min_quantity: $event })"
                    >
                        <template v-if="byWeight" #suffix><span class="text-[11px] text-ink-500">{{ weightUnit }}</span></template>
                    </TextInput>
                    <div v-if="errorFor(index, 'min_quantity')" class="mt-1 text-[11px] text-danger">{{ errorFor(index, 'min_quantity') }}</div>
                </div>
                <div>
                    <TextInput
                        :model-value="tier.price"
                        type="number"
                        min="0"
                        :step="stepFor(currencyFor(tier.currency_code) ?? { decimal_places: 2 })"
                        :invalid="!!errorFor(index, 'price')"
                        @update:model-value="update(index, { price: $event })"
                    />
                    <div v-if="errorFor(index, 'price')" class="mt-1 text-[11px] text-danger">{{ errorFor(index, 'price') }}</div>
                </div>
                <Button variant="ghost" size="sm" icon="trash" :aria-label="t('shipping::zones.remove_tier')" class="text-ink-700 hover:text-danger" @click="remove(index)" />
            </div>
        </div>

        <div class="mt-3">
            <Button size="sm" icon="plus" @click="add">{{ t('shipping::zones.add_tier') }}</Button>
        </div>
    </div>
</template>
