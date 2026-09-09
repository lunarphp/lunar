<script setup lang="ts">
// The panel form for the ShippingDiscount type: a list of rules, each naming a
// method (or none for a catch-all) and either a percentage off or the price
// shipping becomes, per currency. Fixed-type inputs are labelled as the
// resulting price: the type sets the breakdown item's price to the stored
// value rather than subtracting it.
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, FieldLabel, Select, TextInput } from '@lunarphp/panel';
import MoneyInputs from './MoneyInputs.vue';
import type { CurrencyOption } from '../lib/types';

type Rule = {
    shipping_method_id: number | null;
    type: 'fixed' | 'percentage';
    percentage: string | number | null;
    prices: Record<string, string | number | null>;
};

type MethodOption = { id: number; name: string };

const props = defineProps<{
    modelValue: Record<string, unknown>;
    currencies: CurrencyOption[];
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [Record<string, unknown>] }>();

const { t } = useI18n();

// Shared by the panel's Inertia middleware for every page while the shipping
// section is registered, so the type form needs no endpoint of its own.
const methods = computed<MethodOption[]>(() => ((usePage().props.shippingMethods as MethodOption[] | undefined) ?? []));

const rules = computed<Rule[]>(() => ((props.modelValue.methods as Rule[] | undefined) ?? []));

const setRules = (next: Rule[]): void => {
    emit('update:modelValue', { ...props.modelValue, methods: next });
};

const update = (index: number, patch: Partial<Rule>): void => {
    setRules(rules.value.map((rule, i) => (i === index ? { ...rule, ...patch } : rule)));
};

const blankPrices = (): Record<string, string | number | null> => Object.fromEntries(props.currencies.map((currency) => [currency.code, null]));

const add = (): void => {
    setRules([...rules.value, { shipping_method_id: null, type: 'fixed', percentage: null, prices: blankPrices() }]);
};

const remove = (index: number): void => {
    setRules(rules.value.filter((_, i) => i !== index));
};

const errorFor = (index: number, field: string): string | undefined => props.errors?.[`data.methods.${index}.${field}`];
</script>

<template>
    <div>
        <div class="text-xs text-ink-500 mb-3">{{ t('shipping::discounts.shipping_discount.panel.rules_description') }}</div>

        <div v-if="!rules.length" class="px-6 py-6 text-center text-xs text-ink-500 border border-dashed border-line rounded-md">
            {{ t('shipping::discounts.shipping_discount.panel.no_rules') }}
        </div>

        <div v-for="(rule, index) in rules" :key="index" class="mb-3 p-4 rounded-xl border border-line bg-surface">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel>{{ t('shipping::discounts.shipping_discount.panel.method') }}</FieldLabel>
                    <Select :model-value="rule.shipping_method_id ?? ''" :invalid="!!errorFor(index, 'shipping_method_id')" @update:model-value="update(index, { shipping_method_id: $event === '' || $event === null ? null : Number($event) })">
                        <option value="">{{ t('shipping::discounts.shipping_discount.panel.method_any') }}</option>
                        <option v-for="method in methods" :key="method.id" :value="method.id">{{ method.name }}</option>
                    </Select>
                    <div v-if="errorFor(index, 'shipping_method_id')" class="mt-1 text-[11px] text-danger">{{ errorFor(index, 'shipping_method_id') }}</div>
                </div>
                <div>
                    <FieldLabel>{{ t('shipping::discounts.shipping_discount.panel.type') }}</FieldLabel>
                    <Select :model-value="rule.type" @update:model-value="update(index, { type: $event as 'fixed' | 'percentage' })">
                        <option value="fixed">{{ t('shipping::discounts.shipping_discount.panel.type_fixed') }}</option>
                        <option value="percentage">{{ t('shipping::discounts.shipping_discount.panel.type_percentage') }}</option>
                    </Select>
                </div>
            </div>

            <div v-if="rule.type === 'percentage'" class="mt-3 max-w-[200px]">
                <FieldLabel required>{{ t('shipping::discounts.shipping_discount.panel.percentage') }}</FieldLabel>
                <TextInput :model-value="rule.percentage ?? ''" type="number" min="0" max="100" step="0.01" :invalid="!!errorFor(index, 'percentage')" @update:model-value="update(index, { percentage: $event })">
                    <template #suffix><span class="text-[11px] text-ink-500">%</span></template>
                </TextInput>
                <div v-if="errorFor(index, 'percentage')" class="mt-1 text-[11px] text-danger">{{ errorFor(index, 'percentage') }}</div>
            </div>

            <div v-else class="mt-3">
                <FieldLabel>{{ t('shipping::discounts.shipping_discount.panel.prices') }}</FieldLabel>
                <MoneyInputs :model-value="rule.prices ?? {}" :currencies="currencies" :errors="errors" :error-prefix="`data.methods.${index}.prices.`" @update:model-value="update(index, { prices: $event })" />
                <div class="mt-1 text-[11.5px] text-ink-500">{{ t('shipping::discounts.shipping_discount.panel.prices_hint') }}</div>
            </div>

            <div class="mt-3 flex justify-end">
                <Button variant="ghost" size="sm" icon="trash" class="text-ink-700 hover:text-danger" @click="remove(index)">{{ t('shipping::discounts.shipping_discount.panel.remove_rule') }}</Button>
            </div>
        </div>

        <Button size="sm" icon="plus" @click="add">{{ t('shipping::discounts.shipping_discount.panel.add_rule') }}</Button>
    </div>
</template>
