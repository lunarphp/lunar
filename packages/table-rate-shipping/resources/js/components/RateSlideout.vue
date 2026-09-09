<script setup lang="ts">
// Add or edit one of a zone's rates. Posts to the rate's own endpoint rather
// than the zone form: a rate carries per-currency base prices and a tier list.
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, FieldLabel, Select, Slideout, Toggle } from '@lunarphp/panel';
import MoneyInputs from './MoneyInputs.vue';
import RateTiersEditor, { type Tier } from './RateTiersEditor.vue';
import type { CurrencyOption, NamedOption } from '../lib/types';

export interface MethodOption {
    id: number;
    name: string;
    charge_by: 'cart_total' | 'weight';
    weight_unit: string;
}

export interface RateRow {
    id: number;
    shipping_method_id: number;
    method_name: string | null;
    enabled: boolean;
    base_price: string | null;
    tiers_count: number;
    base_prices: Record<string, number | null>;
    tiers: Tier[];
    urls: { update: string; destroy: string };
}

const props = defineProps<{
    open: boolean;
    rate: RateRow | null;
    storeUrl: string;
    methods: MethodOption[];
    currencies: CurrencyOption[];
    customerGroups: NamedOption[];
    pricesIncludeTax: boolean;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const blankPrices = (): Record<string, string | number | null> =>
    Object.fromEntries(props.currencies.map((currency) => [currency.code, null]));

const form = useForm<{
    shipping_method_id: number | '';
    enabled: boolean;
    base_prices: Record<string, string | number | null>;
    tiers: Tier[];
}>({
    shipping_method_id: '',
    enabled: true,
    base_prices: blankPrices(),
    tiers: [],
});

watch(() => props.open, (open) => {
    if (!open) return;

    form.clearErrors();
    form.shipping_method_id = props.rate?.shipping_method_id ?? props.methods[0]?.id ?? '';
    form.enabled = props.rate?.enabled ?? true;
    form.base_prices = { ...blankPrices(), ...(props.rate?.base_prices ?? {}) };
    form.tiers = (props.rate?.tiers ?? []).map((tier) => ({ ...tier }));
});

const method = computed(() => props.methods.find((option) => option.id === Number(form.shipping_method_id)));

const submit = (): void => {
    const options = { preserveScroll: true, onSuccess: () => emit('update:open', false) };

    if (props.rate) {
        form.put(props.rate.urls.update, options);
    } else {
        form.post(props.storeUrl, options);
    }
};
</script>

<template>
    <Slideout :open="open" :title="rate ? t('shipping::zones.edit_rate') : t('shipping::zones.add_rate')" size="lg" @update:open="emit('update:open', $event)">
        <div class="flex flex-col gap-6">
            <div class="text-xs text-ink-500">
                {{ pricesIncludeTax ? t('shipping::zones.prices_incl_tax') : t('shipping::zones.prices_excl_tax') }}
            </div>

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('shipping::zones.field_method') }}</FieldLabel>
                    <Select v-model="form.shipping_method_id" :invalid="!!form.errors.shipping_method_id">
                        <option v-for="option in methods" :key="option.id" :value="option.id">{{ option.name }}</option>
                    </Select>
                    <div v-if="form.errors.shipping_method_id" class="mt-1 text-[11px] text-danger">{{ form.errors.shipping_method_id }}</div>
                </div>
                <label class="flex items-center gap-3 cursor-pointer sm:mt-6">
                    <Toggle :on="form.enabled" @toggle="form.enabled = !form.enabled" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('shipping::zones.field_enabled') }}</div>
                        <div class="text-[11px] text-ink-500">{{ t('shipping::zones.rate_enabled_hint') }}</div>
                    </div>
                </label>
            </div>

            <div>
                <div class="text-sm font-semibold text-ink-900 mb-1">{{ t('shipping::zones.section_base_prices') }}</div>
                <div class="text-xs text-ink-500 mb-3">{{ t('shipping::zones.base_prices_desc') }}</div>
                <MoneyInputs v-model="form.base_prices" :currencies="currencies" :errors="form.errors" error-prefix="base_prices." require-default />
            </div>

            <div>
                <div class="text-sm font-semibold text-ink-900 mb-1">{{ t('shipping::zones.section_tiers') }}</div>
                <div class="text-xs text-ink-500 mb-3">
                    {{ method?.charge_by === 'weight'
                        ? t('shipping::zones.tiers_desc_weight', { unit: method.weight_unit })
                        : t('shipping::zones.tiers_desc_spend') }}
                </div>
                <RateTiersEditor
                    v-model="form.tiers"
                    :currencies="currencies"
                    :customer-groups="customerGroups"
                    :charge-by="method?.charge_by ?? 'cart_total'"
                    :weight-unit="method?.weight_unit ?? 'kg'"
                    :errors="form.errors"
                />
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" @click="emit('update:open', false)">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>
    </Slideout>
</template>
