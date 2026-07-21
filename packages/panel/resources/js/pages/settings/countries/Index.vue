<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BulkActionsToolbar, { type BulkAction } from '../../../components/BulkActionsToolbar.vue';
import DataTable from '../../../components/DataTable.vue';
import FilterDropdown, { type FilterOption } from '../../../components/FilterDropdown.vue';
import Flag from '../../../components/Flag.vue';
import { type RowAction } from '../../../components/RowActions.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Pagination from '../../../components/Pagination.vue';
import TextInput from '../../../components/TextInput.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Country = {
    id: number;
    name: string;
    iso2: string | null;
    iso3: string;
    emoji: string;
    states_count: number;
    urls: { edit: string };
};

type CountryColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

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
    countries: Paginated<Country>;
    columns: CountryColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    filters: { q?: string };
    urls: { index: string };
}>();

const { t } = useI18n();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

const q = ref(props.filters.q ?? '');

// Add-on filter state, seeded from the server's current values ('' = off).
const extensionFilterValues = reactive<Record<string, string>>({ ...props.tableFilterValues });

const renderableExtensionFilters = computed(() =>
    props.tableFilters.filter((filter) => Object.keys(filter.options).length > 0));

const extensionFilterOptions = (filter: ExtensionFilter): FilterOption[] => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];

const reload = (): void => {
    const active = Object.fromEntries(Object.entries(extensionFilterValues).filter(([, value]) => value !== ''));

    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            filter: Object.keys(active).length ? active : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(extensionFilterValues, reload);

// The search box debounces so a reload only fires once typing settles.
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const rowTo = (row: Record<string, unknown>): string => (row as unknown as Country).urls.edit;
</script>

<template>
    <SettingsShell :title="t('countries.title')" :description="t('countries.description')" wide>
        <!-- Toolbar: search + add-on filters, replaced in place by the bulk-action
             bar while rows are selected so the table below never shifts. -->
        <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
            <template v-if="!(hasBulkActions && selected.length)">
                <div class="max-w-[300px] w-full">
                    <TextInput v-model="q" :placeholder="t('countries.search_placeholder')" />
                </div>
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
            :rows="countries.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-iso2="{ row }">
                <span class="font-mono text-[12.5px] text-ink-900">{{ (row as unknown as Country).iso2 || '—' }}</span>
            </template>
            <template #cell-iso3="{ row }">
                <span class="font-mono text-xs text-ink-500">{{ (row as unknown as Country).iso3 }}</span>
            </template>
            <template #cell-name="{ row }">
                <span class="inline-flex items-center gap-2 text-[12.5px] text-ink-900">
                    <Flag :code="(row as unknown as Country).iso2" />
                    {{ (row as unknown as Country).name }}
                </span>
            </template>
            <template #cell-states_count="{ row }">
                <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ (row as unknown as Country).states_count }}</span>
            </template>
            <template #empty>
                <PageEmpty :title="t('countries.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="countries" />
        </div>
    </SettingsShell>
</template>
