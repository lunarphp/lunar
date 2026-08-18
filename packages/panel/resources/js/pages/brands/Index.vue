<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../../components/Button.vue';
import DataTable from '../../components/DataTable.vue';
import { type RowAction } from '../../components/RowActions.vue';
import BulkActionsToolbar, { type BulkAction } from '../../components/BulkActionsToolbar.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import Breadcrumbs from '../../components/Breadcrumbs.vue';
import Icon from '../../components/Icon.vue';
import Pagination from '../../components/Pagination.vue';
import PageEmpty from '../../components/PageEmpty.vue';
import FilterDropdown, { type FilterOption } from '../../components/FilterDropdown.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import TextInput from '../../components/TextInput.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import type { BreadcrumbItem } from '../../components/Breadcrumbs.vue';

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    { label: t('nav.catalog') },
    { label: t('nav.brands'), current: true },
];

interface BrandRow {
    id: number;
    name: string;
    handle: string;
    thumbnail: string | null;
    short_description: string | null;
    collections_count: number;
    products_count: number;
    status: 'active' | 'draft';
    status_label: string;
    created_at: string;
    edit_url: string;
    // Extension-contributed columns land here under their own key.
    [key: string]: unknown;
}

interface BrandColumn {
    key: string;
    label: string;
    width?: string;
    align?: 'left' | 'right' | 'center';
}

// Add-on filter definitions shared by the table extension resolver; options
// map submitted value => label. Filters without options are skipped by the
// generic dropdown rendering.
interface ExtensionFilter {
    key: string;
    label: string;
    component: string | null;
    options: Record<string, string>;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    brands: Paginated<BrandRow>;
    columns: BrandColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    totalCount: number;
    filters: { q?: string; status?: string; sort?: string; direction?: string };
    urls: { index: string; create: string };
}>();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

// Sort options fold the backend's sort + direction pair into a single dropdown value,
// matching the prototype's one-control sort.
const sortOptions: { value: string; label: string; sort: string; direction: string }[] = [
    { value: 'recent', label: t('brands.sort_recent'), sort: 'created_at', direction: 'desc' },
    { value: 'oldest', label: t('brands.sort_oldest'), sort: 'created_at', direction: 'asc' },
    { value: 'name', label: t('brands.sort_name'), sort: 'name', direction: 'asc' },
    { value: 'products', label: t('brands.sort_products'), sort: 'products_count', direction: 'desc' },
];

const q = ref(props.filters.q ?? '');
const statusFilter = ref<string>(props.filters.status ?? 'all');

// Add-on filter state, seeded from the server's current values ('' = off).
// Submitted as nested filter[key] params, matching what applyFilters() reads.
const extensionFilterValues = reactive<Record<string, string>>({ ...props.tableFilterValues });

const renderableExtensionFilters = computed(() =>
    props.tableFilters.filter((filter) => Object.keys(filter.options).length > 0));

const extensionFilterOptions = (filter: ExtensionFilter): FilterOption[] => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];

const activeExtensionFilters = (): Record<string, string> =>
    Object.fromEntries(Object.entries(extensionFilterValues).filter(([, value]) => value !== ''));

const sortKey = ref<string>(
    sortOptions.find((o) => o.sort === props.filters.sort && o.direction === (props.filters.direction ?? 'desc'))?.value ?? 'recent',
);

const statusOptions: FilterOption[] = [
    { value: 'all', label: t('brands.filter_all_statuses') },
    { value: 'active', label: t('brands.status_active') },
    { value: 'draft', label: t('brands.status_draft') },
];

const reload = (): void => {
    const sortOption = sortOptions.find((o) => o.value === sortKey.value) ?? sortOptions[0];

    const extensionFilters = activeExtensionFilters();

    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value,
            sort: sortOption.sort,
            direction: sortOption.direction,
            filter: Object.keys(extensionFilters).length ? extensionFilters : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

// Dropdowns reload immediately; the search box debounces so a reload only fires
// once typing settles (no Search button).
watch([statusFilter, sortKey], reload);
watch(extensionFilterValues, reload);

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const hasActiveFilters = computed(
    () => !!q.value.trim()
        || statusFilter.value !== 'all'
        || Object.keys(activeExtensionFilters()).length > 0,
);
const clearFilters = (): void => {
    q.value = '';
    statusFilter.value = 'all';
    Object.keys(extensionFilterValues).forEach((key) => {
        extensionFilterValues[key] = '';
    });
    reload();
};

const initials = (name: string): string => name?.trim().slice(0, 1).toUpperCase() || '?';
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Brands" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader
                :title="t('brands.title')"
                :description="t('brands.description')"
                icon="tag"
            >
                <template #actions>
                    <Link :href="urls.create">
                        <Button variant="primary" icon="plus">{{ t('brands.new_brand') }}</Button>
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <!-- Toolbar: filters, replaced in place by the bulk-action bar while rows
                     are selected so the table below never shifts. The min-height keeps the
                     row a constant height across both states. -->
                <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
                    <template v-if="!(hasBulkActions && selected.length)">
                        <div class="flex-1 max-w-[280px] min-w-[180px]">
                            <TextInput v-model="q" clearable :placeholder="t('brands.search_placeholder')">
                                <template #prefix><Icon name="search" cls="sm" /></template>
                            </TextInput>
                        </div>
                        <FilterDropdown v-model="statusFilter" :label="t('brands.filter_status')" :options="statusOptions" default-value="all" />
                        <!-- Add-on filters registered through the table extension resolver. -->
                        <FilterDropdown
                            v-for="filter in renderableExtensionFilters"
                            :key="filter.key"
                            v-model="extensionFilterValues[filter.key]"
                            :label="filter.label"
                            :options="extensionFilterOptions(filter)"
                            default-value=""
                        />
                        <FilterDropdown v-model="sortKey" :label="t('common.sort')" :options="sortOptions" default-value="recent" />
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="text-[12px] text-ink-500 underline underline-offset-2 whitespace-nowrap rounded-sm hover:text-ink-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                            @click="clearFilters"
                        >{{ t('brands.clear_filters') }}</button>
                        <div class="flex-1" />
                        <span class="text-[11.5px] text-ink-500 whitespace-nowrap">{{ t('brands.count_of', { shown: brands.total, total: totalCount }) }}</span>
                        <Link :href="urls.create" class="sm:hidden">
                            <Button variant="primary" icon="plus">{{ t('common.new') }}</Button>
                        </Link>
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
                    :rows="brands.data"
                    :row-to="(row) => row.edit_url as string"
                    :row-actions="props.tableActions"
                    :selectable="hasBulkActions"
                    :selected="selected"
                    @update:selected="selected = $event"
                >
                    <template #empty>
                        <PageEmpty :title="hasActiveFilters ? t('brands.empty_title') : t('brands.empty_none_title')">
                            {{ hasActiveFilters ? t('brands.empty_description') : t('brands.empty_none_description') }}
                            <div v-if="hasActiveFilters" class="mt-3">
                                <Button @click="clearFilters">{{ t('brands.clear_filters') }}</Button>
                            </div>
                        </PageEmpty>
                    </template>

                    <template #cell-name="{ row }">
                        <div class="min-w-0 flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-md overflow-hidden shrink-0 border border-line grid place-items-center bg-surface-2">
                                <img
                                    v-if="row.thumbnail"
                                    :src="row.thumbnail as string"
                                    :alt="row.name as string"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-[11px] font-semibold text-ink-700">{{ initials(row.name as string) }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[12.5px] font-medium text-ink-900 truncate">{{ row.name }}</div>
                                <div class="text-[11px] font-mono text-ink-500 truncate">{{ row.handle }}</div>
                            </div>
                        </div>
                    </template>

                    <template #cell-short_description="{ value }">
                        <span v-if="value" class="text-xs text-ink-500 truncate">{{ value }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>

                    <template #cell-collections_count="{ value }">
                        <span class="text-[12.5px] text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ value }}</span>
                    </template>

                    <template #cell-products_count="{ value }">
                        <span class="text-[12.5px] text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ value }}</span>
                    </template>

                    <template #cell-status="{ row }">
                        <StatusBadge :tone="row.status === 'active' ? 'sage' : 'warn'" dot>
                            {{ row.status_label }}
                        </StatusBadge>
                    </template>
                </DataTable>

                <div class="mt-4">
                    <Pagination :meta="brands" />
                </div>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
