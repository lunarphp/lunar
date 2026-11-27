<script setup lang="ts">
import { computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FieldLabel from './FieldLabel.vue';
import Section from './Section.vue';
import TextInput from './TextInput.vue';
import Toggle from './Toggle.vue';

export interface StockLevelRow {
    location_id: number;
    location_name: string;
    default: boolean;
    on_hand: number;
    incoming: number;
    committed: number;
    unavailable: number;
}

export interface StockAggregate {
    on_hand: number;
    incoming: number;
    committed: number;
    reserved: number;
    unavailable: number;
    available: number;
}

const props = defineProps<{
    // The page's draft values object; field keys are prefixed on the simple
    // shape (variant:continue_selling) and bare on the variant page.
    values: Record<string, unknown>;
    fieldPrefix?: string;
    stock: { aggregate: StockAggregate; levels: StockLevelRow[] };
    adjustUrl: string;
    // Null while a purchasing add-on owns incoming stock; the figure is then read-only.
    incomingUrl?: string | null;
    errors?: Record<string, string>;
}>();

const { t } = useI18n();

const key = (field: string): string => `${props.fieldPrefix ?? ''}${field}`;

const field = (name: string) => computed({
    get: () => props.values[key(name)] as string | number | boolean | null,
    set: (value: string | number | boolean | null) => {
        // eslint-disable-next-line vue/no-mutating-props
        props.values[key(name)] = value;
    },
});

const continueSelling = field('continue_selling');
const sellAgainstIncoming = field('sell_against_incoming');
const unitQuantity = field('unit_quantity');
const minQuantity = field('min_quantity');
const quantityIncrement = field('quantity_increment');

// Inline stock edits post immediately rather than being drafted: on-hand as an
// adjustment movement (stock is a ledger), incoming as an absolute figure.
type StockColumn = 'on_hand' | 'incoming';

const stockEdits = reactive<Record<StockColumn, Record<number, string>>>({ on_hand: {}, incoming: {} });

// The figure each cell is posting, so Enter followed by blur posts once.
const stockSaving = reactive<Record<StockColumn, Record<number, number>>>({ on_hand: {}, incoming: {} });

const stockValue = (column: StockColumn, row: StockLevelRow): string =>
    stockEdits[column][row.location_id] ?? String(row[column]);

const commitStock = (column: StockColumn, row: StockLevelRow, url: string | null | undefined): void => {
    const edited = stockEdits[column][row.location_id];

    if (edited === undefined || !url) {
        return;
    }

    const next = Math.max(0, Number(edited) || 0);

    if (stockSaving[column][row.location_id] === next) {
        return;
    }

    if (next === row[column]) {
        delete stockEdits[column][row.location_id];

        return;
    }

    // Keep showing the entered figure until the reloaded props carry it,
    // instead of flashing the old value while the request is in flight.
    stockEdits[column][row.location_id] = String(next);
    stockSaving[column][row.location_id] = next;

    router.post(url, {
        location_id: row.location_id,
        [column]: next,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            delete stockSaving[column][row.location_id];

            // A figure typed while this request ran stays for its own commit.
            if (stockEdits[column][row.location_id] === String(next)) {
                delete stockEdits[column][row.location_id];
            }
        },
    });
};

const levelAvailable = (row: StockLevelRow): number => row.on_hand - row.committed - row.unavailable;

const quantityField = (name: string, label: string, hint: string, min: number) =>
    ({ name, label, hint, min });

const quantityFields = computed(() => [
    quantityField('unit_quantity', t('products.field_unit_quantity'), t('products.field_unit_quantity_hint'), 1),
    quantityField('min_quantity', t('products.field_min_quantity'), t('products.field_min_quantity_hint'), 1),
    quantityField('quantity_increment', t('products.field_quantity_increment'), t('products.field_quantity_increment_hint'), 1),
]);

const quantityModels: Record<string, ReturnType<typeof field>> = {
    unit_quantity: unitQuantity,
    min_quantity: minQuantity,
    quantity_increment: quantityIncrement,
};
</script>

<template>
    <Section :title="t('products.section_inventory')">
        <template #desc>{{ t('products.section_inventory_description') }}</template>

        <div class="mb-4 flex flex-col gap-3">
            <div class="flex items-start gap-2.5">
                <Toggle
                    :on="!!continueSelling"
                    :aria-label="t('products.field_continue_selling')"
                    @toggle="continueSelling = !continueSelling"
                />
                <div>
                    <div class="text-[12.5px] text-ink-900">{{ t('products.field_continue_selling') }}</div>
                    <div class="text-[11.5px] text-ink-500 mt-0.5">{{ t('products.field_continue_selling_help') }}</div>
                </div>
            </div>
            <!-- Incoming stock only matters when stock is binding. -->
            <div v-if="!continueSelling" class="flex items-start gap-2.5">
                <Toggle
                    :on="!!sellAgainstIncoming"
                    :aria-label="t('products.field_sell_against_incoming')"
                    @toggle="sellAgainstIncoming = !sellAgainstIncoming"
                />
                <div>
                    <div class="text-[12.5px] text-ink-900">{{ t('products.field_sell_against_incoming') }}</div>
                    <div class="text-[11.5px] text-ink-500 mt-0.5">{{ t('products.field_sell_against_incoming_help') }}</div>
                </div>
            </div>
        </div>

        <!-- Per-location stock; one row collapses naturally with one location. -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1.5">
                <FieldLabel>{{ t('products.stock_by_location') }}</FieldLabel>
                <div class="flex items-center gap-1.5 [font-variant-numeric:tabular-nums]">
                    <span class="text-[11px] text-ink-500">{{ t('products.stock_aggregate_available') }}</span>
                    <span class="text-[13px] font-semibold text-ink-900">{{ stock.aggregate.available }}</span>
                </div>
            </div>
            <div class="bg-surface border border-line rounded-md overflow-hidden">
                <div class="grid grid-cols-[minmax(0,1fr)_82px_82px_70px_82px] items-center gap-3 px-3 py-2 bg-surface-2 border-b border-line text-[10.5px] uppercase tracking-[0.06em] text-ink-500 font-medium">
                    <div>{{ t('products.stock_location') }}</div>
                    <div class="text-right">{{ t('products.stock_on_hand') }}</div>
                    <div class="text-right">{{ t('products.stock_available') }}</div>
                    <div class="text-right">{{ t('products.stock_committed') }}</div>
                    <div class="text-right">{{ t('products.stock_incoming') }}</div>
                </div>
                <div
                    v-for="row in stock.levels"
                    :key="row.location_id"
                    class="grid grid-cols-[minmax(0,1fr)_82px_82px_70px_82px] items-center gap-3 px-3 py-2 border-b border-line last:border-b-0"
                >
                    <div class="min-w-0">
                        <div class="text-[12.5px] text-ink-900 truncate">{{ row.location_name }}</div>
                        <div v-if="row.default" class="text-[10.5px] text-ink-500">{{ t('products.stock_default_location') }}</div>
                    </div>
                    <div>
                        <TextInput
                            :model-value="stockValue('on_hand', row)"
                            type="number"
                            min="0"
                            :aria-label="`${t('products.stock_on_hand')} — ${row.location_name}`"
                            @update:model-value="(value) => { stockEdits.on_hand[row.location_id] = String(value); }"
                            @blur="commitStock('on_hand', row, adjustUrl)"
                            @keydown.enter.prevent="commitStock('on_hand', row, adjustUrl)"
                        />
                    </div>
                    <div class="text-right text-[12.5px] font-medium [font-variant-numeric:tabular-nums] text-ink-900">{{ levelAvailable(row) }}</div>
                    <div class="text-right text-[12.5px] [font-variant-numeric:tabular-nums] text-ink-700">{{ row.committed }}</div>
                    <div v-if="incomingUrl">
                        <TextInput
                            :model-value="stockValue('incoming', row)"
                            type="number"
                            min="0"
                            :aria-label="`${t('products.stock_incoming')} — ${row.location_name}`"
                            @update:model-value="(value) => { stockEdits.incoming[row.location_id] = String(value); }"
                            @blur="commitStock('incoming', row, incomingUrl)"
                            @keydown.enter.prevent="commitStock('incoming', row, incomingUrl)"
                        />
                    </div>
                    <div v-else :class="['text-right text-[12.5px] [font-variant-numeric:tabular-nums]', row.incoming > 0 ? 'text-warn-ink font-medium' : 'text-ink-400']">{{ row.incoming }}</div>
                </div>
            </div>
            <div class="text-[11px] text-ink-500 mt-1.5">{{ t('products.stock_inline_hint') }}</div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-3.5 gap-y-3">
            <div v-for="entry in quantityFields" :key="entry.name">
                <FieldLabel>{{ entry.label }}</FieldLabel>
                <TextInput
                    :model-value="String(quantityModels[entry.name].value ?? entry.min)"
                    type="number"
                    :min="String(entry.min)"
                    :invalid="!!(errors ?? {})[key(entry.name)]"
                    @update:model-value="(value) => { quantityModels[entry.name].value = Math.max(entry.min, Number(value) || entry.min); }"
                />
                <div class="text-[11px] text-ink-400 mt-1.5 leading-snug">{{ entry.hint }}</div>
            </div>
        </div>
    </Section>
</template>
