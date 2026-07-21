<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import FieldLabel from './FieldLabel.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import TextInput from './TextInput.vue';

export interface PriceRow {
    id: number;
    currency_id: number;
    customer_group_id: number | null;
    min_quantity: number;
    price: number;
    list_price: number | null;
    update_url: string;
    destroy_url: string;
}

export interface CurrencyOption {
    id: number;
    code: string;
    decimal_places: number;
    default: boolean;
}

const props = defineProps<{
    prices: PriceRow[];
    currencies: CurrencyOption[];
    customerGroups: { id: number; name: string }[];
    storeUrl: string;
}>();

const { t } = useI18n();

const currencyById = (id: number): CurrencyOption | undefined =>
    props.currencies.find((currency) => currency.id === id);

const defaultCurrency = computed(() => props.currencies.find((currency) => currency.default) ?? props.currencies[0]);

// Amounts render and edit in major units; the server stores integer minor
// units, converted by the currency's decimal places.
const toMajor = (minor: number | null, decimals: number): string =>
    minor === null ? '' : (minor / 10 ** decimals).toFixed(decimals);

const toMinor = (major: string, decimals: number): number | null => {
    const value = major.trim();

    if (value === '') {
        return null;
    }

    const parsed = Number(value);

    return Number.isFinite(parsed) ? Math.round(parsed * 10 ** decimals) : null;
};

// Editable working rows. Base rows are keyed per currency; group and tier
// rows keep their own identity (saved id or a local key while pending).
interface WorkingRow {
    key: string;
    saved: PriceRow | null;
    currency_id: number;
    customer_group_id: number | null;
    min_quantity: number;
    amount: string;
    compare: string;
}

let pendingKey = 0;

const rowFrom = (saved: PriceRow): WorkingRow => {
    const decimals = currencyById(saved.currency_id)?.decimal_places ?? 2;

    return {
        key: `saved-${saved.id}`,
        saved,
        currency_id: saved.currency_id,
        customer_group_id: saved.customer_group_id,
        min_quantity: saved.min_quantity,
        amount: toMajor(saved.price, decimals),
        compare: toMajor(saved.list_price, decimals),
    };
};

const isBase = (row: { customer_group_id: number | null; min_quantity: number }): boolean =>
    row.customer_group_id === null && row.min_quantity === 1;

const isGroup = (row: { customer_group_id: number | null; min_quantity: number }): boolean =>
    row.customer_group_id !== null && row.min_quantity === 1;

const buildRows = (): { base: WorkingRow[]; groups: WorkingRow[]; tiers: WorkingRow[] } => {
    const base = props.currencies.map((currency) => {
        const saved = props.prices.find((price) => isBase(price) && price.currency_id === currency.id);

        return saved
            ? rowFrom(saved)
            : {
                key: `base-${currency.id}`,
                saved: null,
                currency_id: currency.id,
                customer_group_id: null,
                min_quantity: 1,
                amount: '',
                compare: '',
            };
    });

    return {
        base,
        groups: props.prices.filter(isGroup).map(rowFrom),
        tiers: props.prices.filter((price) => price.min_quantity > 1).map(rowFrom),
    };
};

const rows = reactive(buildRows());

// Server responses refresh the prices prop; rebuild while keeping any
// pending (unsaved) rows staff are still filling in.
watch(() => props.prices, () => {
    const next = buildRows();

    rows.base = next.base;
    rows.groups = [...next.groups, ...rows.groups.filter((row) => !row.saved)];
    rows.tiers = [...next.tiers, ...rows.tiers.filter((row) => !row.saved)];
});

const timers = new Map<string, ReturnType<typeof setTimeout>>();

const save = (row: WorkingRow): void => {
    const decimals = currencyById(row.currency_id)?.decimal_places ?? 2;
    const price = toMinor(row.amount, decimals);

    if (price === null) {
        return;
    }

    const payload = {
        currency_id: row.currency_id,
        customer_group_id: row.customer_group_id,
        min_quantity: row.min_quantity,
        price,
        list_price: toMinor(row.compare, decimals),
    };

    const options = { preserveScroll: true, preserveState: true };

    if (row.saved) {
        router.put(row.saved.update_url, payload, options);
    } else {
        router.post(props.storeUrl, payload, options);
    }
};

const queueSave = (row: WorkingRow): void => {
    clearTimeout(timers.get(row.key));
    timers.set(row.key, setTimeout(() => save(row), 600));
};

const removeRow = (list: WorkingRow[], row: WorkingRow): void => {
    clearTimeout(timers.get(row.key));

    if (row.saved) {
        router.delete(row.saved.destroy_url, { preserveScroll: true, preserveState: true });

        return;
    }

    list.splice(list.indexOf(row), 1);
};

const addGroupRow = (): void => {
    const group = props.customerGroups[0];

    if (!group || !defaultCurrency.value) {
        return;
    }

    rows.groups.push({
        key: `pending-${++pendingKey}`,
        saved: null,
        currency_id: defaultCurrency.value.id,
        customer_group_id: group.id,
        min_quantity: 1,
        amount: '',
        compare: '',
    });
};

const addTierRow = (): void => {
    if (!defaultCurrency.value) {
        return;
    }

    const last = rows.tiers[rows.tiers.length - 1];

    rows.tiers.push({
        key: `pending-${++pendingKey}`,
        saved: null,
        currency_id: defaultCurrency.value.id,
        customer_group_id: null,
        min_quantity: last ? last.min_quantity + 5 : 2,
        amount: '',
        compare: '',
    });
};

const currencyCode = (id: number): string => currencyById(id)?.code ?? '';

// Narrow currency symbol derived from the ISO code (no symbol column exists);
// falls back to the code itself for non-standard codes Intl can't resolve.
const symbolCache = new Map<string, string>();
const currencySymbol = (id: number): string => {
    const code = currencyCode(id);

    if (!code) {
        return '';
    }

    const cached = symbolCache.get(code);

    if (cached !== undefined) {
        return cached;
    }

    let symbol = code;

    try {
        const parts = new Intl.NumberFormat(undefined, {
            style: 'currency',
            currency: code,
            currencyDisplay: 'narrowSymbol',
        }).formatToParts(0);

        symbol = parts.find((part) => part.type === 'currency')?.value ?? code;
    } catch {
        symbol = code;
    }

    symbolCache.set(code, symbol);

    return symbol;
};
</script>

<template>
    <Section :title="t('pricing.title')">
        <template #desc>{{ t('pricing.description') }}</template>

        <!-- Base price: one row per enabled currency -->
        <div class="mb-5">
            <FieldLabel required>
                {{ t('pricing.base_price') }}
                <span v-if="currencies.length > 1" class="ml-1 text-ink-400 font-normal">{{ t('pricing.base_price_per_currency') }}</span>
            </FieldLabel>
            <div class="flex flex-col gap-2.5 p-3 border border-line rounded-md bg-surface">
                <div v-for="row in rows.base" :key="row.key" class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)_64px] gap-2.5 items-end">
                    <div>
                        <FieldLabel>{{ t('pricing.amount') }}</FieldLabel>
                        <TextInput
                            v-model="row.amount"
                            type="number"
                            min="0"
                            step="any"
                            :aria-label="`${t('pricing.amount')} ${currencyCode(row.currency_id)}`"
                            @update:model-value="queueSave(row)"
                        >
                            <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                        </TextInput>
                    </div>
                    <div>
                        <FieldLabel :hint="t('pricing.compare_at_hint')">{{ t('pricing.compare_at') }}</FieldLabel>
                        <TextInput
                            v-model="row.compare"
                            type="number"
                            min="0"
                            step="any"
                            @update:model-value="queueSave(row)"
                        >
                            <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                        </TextInput>
                    </div>
                    <div class="text-[12px] font-mono text-ink-500 pb-2">{{ currencyCode(row.currency_id) }}</div>
                </div>
            </div>
        </div>

        <!-- Customer-group pricing -->
        <div class="mb-5">
            <FieldLabel :hint="t('pricing.group_hint')">{{ t('pricing.group_title') }}</FieldLabel>
            <div v-if="rows.groups.length === 0" class="border border-dashed border-line-strong rounded-lg bg-surface-2 px-4 py-3 flex items-center justify-between gap-3">
                <span class="text-[11.5px] text-ink-500">{{ t('pricing.group_empty') }}</span>
                <Button size="sm" icon="plus" :disabled="!customerGroups.length" @click="addGroupRow">{{ t('pricing.group_add') }}</Button>
            </div>
            <div v-else class="border border-line rounded-lg bg-surface overflow-x-auto">
                <div
                    v-for="row in rows.groups"
                    :key="row.key"
                    class="grid grid-cols-[minmax(120px,1fr)_minmax(132px,1fr)_minmax(132px,1fr)_90px_36px] gap-2.5 items-center px-3 py-2 border-b border-line last:border-b-0"
                >
                    <Select v-model="row.customer_group_id" :aria-label="t('pricing.group_column')" @update:model-value="queueSave(row)">
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </Select>
                    <TextInput v-model="row.amount" type="number" min="0" step="any" :placeholder="t('pricing.amount')" @update:model-value="queueSave(row)">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <TextInput v-model="row.compare" type="number" min="0" step="any" :placeholder="t('pricing.compare_at')" @update:model-value="queueSave(row)">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <Select v-model="row.currency_id" :aria-label="t('pricing.currency')" @update:model-value="queueSave(row)">
                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.code }}</option>
                    </Select>
                    <button
                        type="button"
                        class="h-8 w-8 grid place-items-center rounded-md text-ink-500 hover:text-danger hover:bg-danger-soft transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/25"
                        :aria-label="t('pricing.remove_row')"
                        @click="removeRow(rows.groups, row)"
                    ><Icon name="trash" cls="sm" /></button>
                </div>
                <div class="px-3 py-2 bg-surface-2 border-t border-line">
                    <Button variant="ghost" size="sm" icon="plus" @click="addGroupRow">{{ t('pricing.group_add') }}</Button>
                </div>
            </div>
        </div>

        <!-- Tier pricing -->
        <div>
            <FieldLabel :hint="t('pricing.tier_hint')">{{ t('pricing.tier_title') }}</FieldLabel>
            <div v-if="rows.tiers.length === 0" class="border border-dashed border-line-strong rounded-lg bg-surface-2 px-4 py-3 flex items-center justify-between gap-3">
                <span class="text-[11.5px] text-ink-500">{{ t('pricing.tier_empty') }}</span>
                <Button size="sm" icon="plus" @click="addTierRow">{{ t('pricing.tier_add') }}</Button>
            </div>
            <div v-else class="border border-line rounded-lg bg-surface overflow-x-auto">
                <div
                    v-for="row in rows.tiers"
                    :key="row.key"
                    class="grid grid-cols-[90px_minmax(120px,1fr)_minmax(132px,1fr)_minmax(132px,1fr)_90px_36px] gap-2.5 items-center px-3 py-2 border-b border-line last:border-b-0"
                >
                    <TextInput
                        :model-value="String(row.min_quantity)"
                        type="number"
                        min="2"
                        :aria-label="t('pricing.tier_min_qty')"
                        @update:model-value="(value) => { row.min_quantity = Math.max(2, Number(value) || 2); queueSave(row); }"
                    >
                        <template #prefix>{{ t('pricing.tier_min_prefix') }}</template>
                    </TextInput>
                    <Select
                        :model-value="row.customer_group_id ?? ''"
                        :aria-label="t('pricing.group_column')"
                        @update:model-value="(value) => { row.customer_group_id = value === '' ? null : Number(value); queueSave(row); }"
                    >
                        <option value="">{{ t('pricing.tier_any_customer') }}</option>
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </Select>
                    <TextInput v-model="row.amount" type="number" min="0" step="any" :placeholder="t('pricing.amount')" @update:model-value="queueSave(row)">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <TextInput v-model="row.compare" type="number" min="0" step="any" :placeholder="t('pricing.compare_at')" @update:model-value="queueSave(row)">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <Select v-model="row.currency_id" :aria-label="t('pricing.currency')" @update:model-value="queueSave(row)">
                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.code }}</option>
                    </Select>
                    <button
                        type="button"
                        class="h-8 w-8 grid place-items-center rounded-md text-ink-500 hover:text-danger hover:bg-danger-soft transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/25"
                        :aria-label="t('pricing.remove_row')"
                        @click="removeRow(rows.tiers, row)"
                    ><Icon name="trash" cls="sm" /></button>
                </div>
                <div class="px-3 py-2 bg-surface-2 border-t border-line">
                    <Button variant="ghost" size="sm" icon="plus" @click="addTierRow">{{ t('pricing.tier_add') }}</Button>
                </div>
            </div>
        </div>
    </Section>
</template>
