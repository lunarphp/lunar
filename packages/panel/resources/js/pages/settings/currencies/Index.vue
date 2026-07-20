<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BulkActionsToolbar, { type BulkAction } from '../../../components/BulkActionsToolbar.vue';
import Button from '../../../components/Button.vue';
import DataTable from '../../../components/DataTable.vue';
import FilterDropdown, { type FilterOption } from '../../../components/FilterDropdown.vue';
import { type RowAction } from '../../../components/RowActions.vue';
import Dialog from '../../../components/Dialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Pagination from '../../../components/Pagination.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Currency = {
    id: number;
    code: string;
    name: string;
    exchange_rate: number;
    decimal_places: number;
    enabled: boolean;
    default: boolean;
    urls: { edit: string };
};

type CurrencyColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

type ExtensionFilter = { key: string; label: string; component: string | null; options: Record<string, string> };

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{
    currencies: Paginated<Currency>;
    columns: CurrencyColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    urls: { index: string; store: string };
}>();

const { t } = useI18n();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

// Add-on filter state, seeded from the server's current values ('' = off).
const extensionFilterValues = reactive<Record<string, string>>({ ...props.tableFilterValues });

const renderableExtensionFilters = computed(() =>
    props.tableFilters.filter((filter) => Object.keys(filter.options).length > 0));

const extensionFilterOptions = (filter: ExtensionFilter): FilterOption[] => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];

watch(extensionFilterValues, () => {
    const active = Object.fromEntries(Object.entries(extensionFilterValues).filter(([, value]) => value !== ''));

    router.get(
        props.urls.index,
        { filter: Object.keys(active).length ? active : undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const rowTo = (row: Record<string, unknown>): string => (row as unknown as Currency).urls.edit;

const formatRate = (currency: Currency): string =>
    currency.exchange_rate.toFixed(Math.max(currency.decimal_places, 2));

const creating = ref(false);
const createForm = useForm({
    code: '',
    name: '',
    exchange_rate: '1',
    decimal_places: '2',
    enabled: true as boolean,
    default: false as boolean,
});

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    creating.value = true;
};

const submitCreate = (): void => {
    createForm.post(props.urls.store, {
        onSuccess: () => {
            createForm.reset();
            creating.value = false;
        },
    });
};
</script>

<template>
    <SettingsShell :title="t('currencies.title')" :description="t('currencies.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('currencies.create_currency') }}</Button>
        </template>

        <!-- Toolbar: add-on filters, replaced in place by the bulk-action bar while
             rows are selected so the table below never shifts. -->
        <div
            v-if="renderableExtensionFilters.length || (hasBulkActions && selected.length)"
            class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]"
        >
            <template v-if="!(hasBulkActions && selected.length)">
                <FilterDropdown
                    v-for="filter in renderableExtensionFilters"
                    :key="filter.key"
                    v-model="extensionFilterValues[filter.key]"
                    :label="filter.label"
                    :options="extensionFilterOptions(filter)"
                    default-value=""
                />
            </template>

            <BulkActionsToolbar
                v-else
                :actions="props.tableBulkActions"
                :selected="selected"
                @clear="selected = []"
                @done="selected = []"
            />
        </div>

        <DataTable
            :columns="props.columns"
            :rows="currencies.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-code="{ row }">
                <span class="font-mono text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as Currency).code }}</span>
            </template>
            <template #cell-name="{ row }">
                <span class="text-[12.5px] text-ink-900">{{ (row as unknown as Currency).name }}</span>
            </template>
            <template #cell-exchange_rate="{ row }">
                <span class="font-mono text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ formatRate(row as unknown as Currency) }}</span>
            </template>
            <template #cell-flags="{ row }">
                <div class="flex gap-1.5">
                    <StatusBadge v-if="(row as unknown as Currency).default" tone="sage" size="sm">{{ t('currencies.default_badge') }}</StatusBadge>
                    <StatusBadge :tone="(row as unknown as Currency).enabled ? 'sage' : 'archived'" size="sm" dot>
                        {{ (row as unknown as Currency).enabled ? t('common.enabled') : t('common.disabled') }}
                    </StatusBadge>
                </div>
            </template>
            <template #empty>
                <PageEmpty :title="t('currencies.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="currencies" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('currencies.create_currency')"
        :description="t('currencies.create_description')"
    >
        <div class="flex flex-col gap-3">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required :hint="t('currencies.code_hint')">{{ t('currencies.field_code') }}</FieldLabel>
                    <TextInput v-model="createForm.code" mono :invalid="!!createForm.errors.code" placeholder="GBP" />
                    <div v-if="createForm.errors.code" class="mt-1 text-[11px] text-danger">{{ createForm.errors.code }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('currencies.field_name') }}</FieldLabel>
                    <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('currencies.name_placeholder')" />
                    <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('currencies.field_exchange_rate') }}</FieldLabel>
                    <TextInput v-model="createForm.exchange_rate" type="number" step="0.0001" min="0" :invalid="!!createForm.errors.exchange_rate" />
                    <div v-if="createForm.errors.exchange_rate" class="mt-1 text-[11px] text-danger">{{ createForm.errors.exchange_rate }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('currencies.field_decimal_places') }}</FieldLabel>
                    <TextInput v-model="createForm.decimal_places" type="number" min="0" max="4" :invalid="!!createForm.errors.decimal_places" />
                    <div v-if="createForm.errors.decimal_places" class="mt-1 text-[11px] text-danger">{{ createForm.errors.decimal_places }}</div>
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <Toggle :on="createForm.enabled" @toggle="createForm.enabled = !createForm.enabled" />
                <span class="text-[12.5px] text-ink-900 font-medium">{{ t('common.enabled') }}</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <Toggle :on="createForm.default" @toggle="createForm.default = !createForm.default" />
                <span class="text-[12.5px] text-ink-900 font-medium">{{ t('currencies.default_currency') }}</span>
            </label>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
