<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useFormSlice } from '../composables/useFormSlice';
import Button from './Button.vue';
import FieldLabel from './FieldLabel.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import TextInput from './TextInput.vue';

/** One row of a currency's price list, in integer minor units. */
export interface PriceValue {
    customer_group_id: number | null;
    min_quantity: number;
    price: number | null;
    list_price: number | null;
}

export interface CurrencyOption {
    id: number;
    code: string;
    decimal_places: number;
    default: boolean;
}

const props = withDefaults(defineProps<{
    currencies: CurrencyOption[];
    customerGroups: { id: number; name: string }[];
    /**
     * The form slice holding the prices: `price` on the variant page, or the
     * product page's `variant` slice with a `price:` field prefix.
     */
    namespace?: string;
    fieldPrefix?: string;
}>(), { namespace: 'price', fieldPrefix: '' });

const { t } = useI18n();

// Each currency's price list is a draft value (`{prefix}{code}`): editing,
// adding and removing rows dirty the page form and land with Save.
const slice = useFormSlice<Record<string, PriceValue[]>>(props.namespace);

const fieldFor = (code: string): string => `${props.fieldPrefix}${code}`;

const currencyById = (id: number): CurrencyOption | undefined =>
    props.currencies.find((currency) => currency.id === id);

const defaultCurrency = computed(() => props.currencies.find((currency) => currency.default) ?? props.currencies[0]);

// Amounts render and edit in major units; the draft holds integer minor
// units, converted by the currency's decimal places.
const toMajor = (minor: number | null, decimals: number): string =>
    minor === null ? '' : (minor / 10 ** decimals).toFixed(decimals);

const toMinor = (major: string, decimals: number): number | null => {
    const value = String(major ?? '').trim();

    if (value === '') {
        return null;
    }

    const parsed = Number(value);

    return Number.isFinite(parsed) ? Math.round(parsed * 10 ** decimals) : null;
};

// Editable working rows: the typed text stays as typed, and every edit
// writes the converted lists back to the slice.
interface WorkingRow {
    key: string;
    currency_id: number;
    customer_group_id: number | null;
    min_quantity: number;
    amount: string;
    compare: string;
}

let rowKey = 0;

const isBase = (row: { customer_group_id: number | null; min_quantity: number }): boolean =>
    row.customer_group_id === null && row.min_quantity === 1;

const isGroup = (row: { customer_group_id: number | null; min_quantity: number }): boolean =>
    row.customer_group_id !== null && row.min_quantity === 1;

const readLists = (): Record<string, PriceValue[]> =>
    Object.fromEntries(props.currencies.map((currency) => [currency.code, slice.values[fieldFor(currency.code)] ?? []]));

const buildRows = (): { base: WorkingRow[]; groups: WorkingRow[]; tiers: WorkingRow[] } => {
    const all = props.currencies.flatMap((currency) =>
        (slice.values[fieldFor(currency.code)] ?? []).map((value): WorkingRow => ({
            key: `row-${++rowKey}`,
            currency_id: currency.id,
            customer_group_id: value.customer_group_id,
            min_quantity: value.min_quantity,
            amount: toMajor(value.price, currency.decimal_places),
            compare: toMajor(value.list_price, currency.decimal_places),
        })));

    return {
        base: props.currencies.map((currency) =>
            all.find((row) => isBase(row) && row.currency_id === currency.id) ?? {
                key: `row-${++rowKey}`,
                currency_id: currency.id,
                customer_group_id: null,
                min_quantity: 1,
                amount: '',
                compare: '',
            }),
        groups: all.filter(isGroup),
        tiers: all.filter((row) => row.min_quantity > 1),
    };
};

const rows = reactive(buildRows());

// The server lists rows in this order, so an edit undone leaves the draft
// clean: base first, then any-customer tiers, then group prices and tiers.
const rank = (row: WorkingRow): number[] => [
    row.customer_group_id === null ? 0 : 1,
    row.min_quantity > 1 ? 1 : 0,
    row.min_quantity,
    row.customer_group_id ?? 0,
];

const compareRows = (a: WorkingRow, b: WorkingRow): number => {
    const left = rank(a);
    const right = rank(b);

    return left.reduce((result, value, index) => result || value - right[index], 0);
};

// Where each working row sits in its currency's list, so commit errors
// (keyed `{code}.{index}.{field}`) land on the row that caused them.
const positions = reactive(new Map<string, string>());

let lastWritten = JSON.stringify(readLists());

const write = (): void => {
    const lists: Record<string, PriceValue[]> = {};

    positions.clear();

    for (const currency of props.currencies) {
        const decimals = currency.decimal_places;

        // A blank base row is no base price; a blank group or tier row is
        // kept, so the commit says the amount is missing on that row.
        const listed = [
            ...rows.base.filter((row) => row.currency_id === currency.id && row.amount.trim() !== ''),
            ...rows.groups.filter((row) => row.currency_id === currency.id),
            ...rows.tiers.filter((row) => row.currency_id === currency.id),
        ].sort(compareRows);

        lists[currency.code] = listed.map((row, index) => {
            positions.set(row.key, `${currency.code}.${index}`);

            return {
                customer_group_id: row.customer_group_id,
                min_quantity: row.min_quantity,
                price: toMinor(row.amount, decimals),
                list_price: toMinor(row.compare, decimals),
            };
        });

        slice.values[fieldFor(currency.code)] = lists[currency.code];
    }

    lastWritten = JSON.stringify(lists);
};

// A restore, conflict resolution or commit reload replaces the lists from
// outside; rebuild the rows from them then, never from this editor's writes.
watch(() => JSON.stringify(readLists()), (next) => {
    if (next === lastWritten) {
        return;
    }

    lastWritten = next;
    positions.clear();
    Object.assign(rows, buildRows());
});

const rowError = (row: WorkingRow): string | undefined => {
    const position = positions.get(row.key);

    if (!position) {
        return undefined;
    }

    const errors = slice.errors.value;
    const field = `${props.fieldPrefix}${position}`;

    return errors[`${field}.price`] ?? errors[`${field}.list_price`] ?? errors[`${field}.min_quantity`]
        ?? errors[`${field}.customer_group_id`] ?? errors[field];
};

const currencyErrors = computed(() =>
    props.currencies.map((currency) => slice.errors.value[fieldFor(currency.code)]).filter(Boolean));

const removeRow = (list: WorkingRow[], row: WorkingRow): void => {
    list.splice(list.indexOf(row), 1);
    write();
};

const addGroupRow = (): void => {
    const group = props.customerGroups[0];

    if (!group || !defaultCurrency.value) {
        return;
    }

    rows.groups.push({
        key: `row-${++rowKey}`,
        currency_id: defaultCurrency.value.id,
        customer_group_id: group.id,
        min_quantity: 1,
        amount: '',
        compare: '',
    });
    write();
};

const addTierRow = (): void => {
    if (!defaultCurrency.value) {
        return;
    }

    const last = rows.tiers[rows.tiers.length - 1];

    rows.tiers.push({
        key: `row-${++rowKey}`,
        currency_id: defaultCurrency.value.id,
        customer_group_id: null,
        min_quantity: last ? last.min_quantity + 5 : 2,
        amount: '',
        compare: '',
    });
    write();
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
                            @update:model-value="write()"
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
                            @update:model-value="write()"
                        >
                            <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                        </TextInput>
                    </div>
                    <div class="text-[12px] font-mono text-ink-500 pb-2">{{ currencyCode(row.currency_id) }}</div>
                    <div v-if="rowError(row)" class="col-span-3 -mt-1 text-[11px] text-danger">{{ rowError(row) }}</div>
                </div>
                <div v-for="message in currencyErrors" :key="message" class="text-[11px] text-danger">{{ message }}</div>
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
                    <Select v-model="row.customer_group_id" :aria-label="t('pricing.group_column')" @update:model-value="write()">
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </Select>
                    <TextInput v-model="row.amount" type="number" min="0" step="any" :placeholder="t('pricing.amount')" @update:model-value="write()">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <TextInput v-model="row.compare" type="number" min="0" step="any" :placeholder="t('pricing.compare_at')" @update:model-value="write()">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <Select v-model="row.currency_id" :aria-label="t('pricing.currency')" @update:model-value="write()">
                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.code }}</option>
                    </Select>
                    <button
                        type="button"
                        class="h-8 w-8 grid place-items-center rounded-md text-ink-500 hover:text-danger hover:bg-danger-soft transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/25"
                        :aria-label="t('pricing.remove_row')"
                        @click="removeRow(rows.groups, row)"
                    ><Icon name="trash" cls="sm" /></button>
                    <div v-if="rowError(row)" class="col-span-5 text-[11px] text-danger">{{ rowError(row) }}</div>
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
                        @update:model-value="(value) => { row.min_quantity = Math.max(2, Number(value) || 2); write(); }"
                    >
                        <template #prefix>{{ t('pricing.tier_min_prefix') }}</template>
                    </TextInput>
                    <Select
                        :model-value="row.customer_group_id ?? ''"
                        :aria-label="t('pricing.group_column')"
                        @update:model-value="(value) => { row.customer_group_id = value === '' ? null : Number(value); write(); }"
                    >
                        <option value="">{{ t('pricing.tier_any_customer') }}</option>
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </Select>
                    <TextInput v-model="row.amount" type="number" min="0" step="any" :placeholder="t('pricing.amount')" @update:model-value="write()">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <TextInput v-model="row.compare" type="number" min="0" step="any" :placeholder="t('pricing.compare_at')" @update:model-value="write()">
                        <template #prefix>{{ currencySymbol(row.currency_id) }}</template>
                    </TextInput>
                    <Select v-model="row.currency_id" :aria-label="t('pricing.currency')" @update:model-value="write()">
                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.code }}</option>
                    </Select>
                    <button
                        type="button"
                        class="h-8 w-8 grid place-items-center rounded-md text-ink-500 hover:text-danger hover:bg-danger-soft transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/25"
                        :aria-label="t('pricing.remove_row')"
                        @click="removeRow(rows.tiers, row)"
                    ><Icon name="trash" cls="sm" /></button>
                    <div v-if="rowError(row)" class="col-span-6 text-[11px] text-danger">{{ rowError(row) }}</div>
                </div>
                <div class="px-3 py-2 bg-surface-2 border-t border-line">
                    <Button variant="ghost" size="sm" icon="plus" @click="addTierRow">{{ t('pricing.tier_add') }}</Button>
                </div>
            </div>
        </div>
    </Section>
</template>
