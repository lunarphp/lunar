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
import KpiCard from '../../components/KpiCard.vue';
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
    { label: t('nav.products'), current: true },
];

interface ProductRow {
    id: number;
    name: string;
    thumbnail: string | null;
    status: 'published' | 'draft' | 'archived';
    status_label: string;
    brand: string | null;
    sku: string | null;
    extra_sku_count: number;
    stock: number;
    product_type: string;
    tags: string[];
    created_at: string;
    edit_url: string;
    // Extension-contributed columns land here under their own key.
    [key: string]: unknown;
}

interface ProductColumn {
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
    products: Paginated<ProductRow>;
    columns: ProductColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    totalCount: number;
    kpis: { total: number; published: number; draft: number; outOfStock: number };
    brandOptions: FilterOption[];
    typeOptions: FilterOption[];
    tagOptions: FilterOption[];
    filters: {
        q?: string;
        status?: string;
        brand?: string;
        type?: string;
        tag?: string;
        stock_state?: string;
        sort?: string;
        direction?: string;
    };
    urls: { index: string; create: string };
}>();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

// Sort options fold the backend's sort + direction pair into a single dropdown value,
// matching the prototype's one-control sort.
const sortOptions: { value: string; label: string; sort: string; direction: string }[] = [
    { value: 'recent', label: t('products.sort_recent'), sort: 'created_at', direction: 'desc' },
    { value: 'oldest', label: t('products.sort_oldest'), sort: 'created_at', direction: 'asc' },
    { value: 'name', label: t('products.sort_name'), sort: 'name', direction: 'asc' },
    { value: 'stock', label: t('products.sort_stock'), sort: 'stock', direction: 'desc' },
];

const q = ref(props.filters.q ?? '');
const statusFilter = ref<string>(props.filters.status ?? 'all');
const brandFilter = ref<string>(props.filters.brand ?? 'all');
const typeFilter = ref<string>(props.filters.type ?? 'all');
const tagFilter = ref<string>(props.filters.tag ?? 'all');
const stockFilter = ref<string>(props.filters.stock_state ?? 'all');

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
    { value: 'all', label: t('products.filter_all_statuses') },
    { value: 'published', label: t('products.status_published') },
    { value: 'draft', label: t('products.status_draft') },
    { value: 'archived', label: t('products.status_archived') },
];

const stockOptions: FilterOption[] = [
    { value: 'all', label: t('products.filter_all_stock') },
    { value: 'in', label: t('products.stock_in') },
    { value: 'out', label: t('products.stock_out') },
];

const withAll = (label: string, options: FilterOption[]): FilterOption[] => [
    { value: 'all', label },
    ...options,
];

const reload = (): void => {
    const sortOption = sortOptions.find((o) => o.value === sortKey.value) ?? sortOptions[0];

    const extensionFilters = activeExtensionFilters();

    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value,
            brand: brandFilter.value === 'all' ? undefined : brandFilter.value,
            type: typeFilter.value === 'all' ? undefined : typeFilter.value,
            tag: tagFilter.value === 'all' ? undefined : tagFilter.value,
            stock_state: stockFilter.value === 'all' ? undefined : stockFilter.value,
            sort: sortOption.sort,
            direction: sortOption.direction,
            filter: Object.keys(extensionFilters).length ? extensionFilters : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

// Dropdowns reload immediately; the search box debounces so a reload only fires
// once typing settles (no Search button).
watch([statusFilter, brandFilter, typeFilter, tagFilter, stockFilter, sortKey], reload);
watch(extensionFilterValues, reload);

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const hasActiveFilters = computed(
    () => !!q.value.trim()
        || statusFilter.value !== 'all'
        || brandFilter.value !== 'all'
        || typeFilter.value !== 'all'
        || tagFilter.value !== 'all'
        || stockFilter.value !== 'all'
        || Object.keys(activeExtensionFilters()).length > 0,
);
const clearFilters = (): void => {
    q.value = '';
    statusFilter.value = 'all';
    brandFilter.value = 'all';
    typeFilter.value = 'all';
    tagFilter.value = 'all';
    stockFilter.value = 'all';
    Object.keys(extensionFilterValues).forEach((key) => {
        extensionFilterValues[key] = '';
    });
    reload();
};

// KPI strip; the dismissed state persists locally. Each card doubles as a
// filter shortcut, per the prototype.
const KPI_STORAGE_KEY = 'lunar.panel.products.kpisDismissed';
const kpisDismissed = ref(localStorage.getItem(KPI_STORAGE_KEY) === '1');
watch(kpisDismissed, (value) => localStorage.setItem(KPI_STORAGE_KEY, value ? '1' : '0'));

type KpiKey = 'total' | 'published' | 'draft' | 'out';

const activeKpi = computed<KpiKey | null>(() => {
    if (stockFilter.value === 'out' && statusFilter.value === 'all') return 'out';
    if (statusFilter.value === 'published' && stockFilter.value === 'all') return 'published';
    if (statusFilter.value === 'draft' && stockFilter.value === 'all') return 'draft';
    if (!hasActiveFilters.value) return 'total';
    return null;
});

// Resets every filter and applies the card's own in one watcher flush, so a
// single reload fires rather than clear-then-filter navigating twice.
const focusKpi = (which: KpiKey): void => {
    q.value = '';
    brandFilter.value = 'all';
    typeFilter.value = 'all';
    tagFilter.value = 'all';
    statusFilter.value = which === 'published' || which === 'draft' ? which : 'all';
    stockFilter.value = which === 'out' ? 'out' : 'all';
    Object.keys(extensionFilterValues).forEach((key) => {
        extensionFilterValues[key] = '';
    });
};

const kpiCards = computed(() => [
    { key: 'total' as const, label: t('products.kpi_total_label'), value: props.kpis.total, hint: t('products.kpi_total_hint'), tone: 'neutral' as const, icon: 'box' },
    { key: 'published' as const, label: t('products.kpi_published_label'), value: props.kpis.published, hint: t('products.kpi_published_hint'), tone: 'sage' as const, icon: 'check' },
    { key: 'draft' as const, label: t('products.kpi_draft_label'), value: props.kpis.draft, hint: t('products.kpi_draft_hint'), tone: 'warn' as const, icon: 'edit' },
    { key: 'out' as const, label: t('products.kpi_out_of_stock_label'), value: props.kpis.outOfStock, hint: t('products.kpi_out_of_stock_hint'), tone: 'danger' as const, icon: 'alert' },
]);

const statusTone = (status: string): 'sage' | 'warn' | 'archived' =>
    status === 'published' ? 'sage' : status === 'draft' ? 'warn' : 'archived';
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Products" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader
                :title="t('products.title')"
                :description="t('products.description')"
                icon="box"
            >
                <template #actions>
                    <Link :href="urls.create">
                        <Button variant="primary" icon="plus">{{ t('products.new_product') }}</Button>
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <!-- KPI strip; dismissable, restored via "Show KPIs". Cards apply
                     their matching filter on click. -->
                <div v-if="!kpisDismissed" class="mb-5 relative">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <KpiCard
                            v-for="kpi in kpiCards"
                            :key="kpi.key"
                            :label="kpi.label"
                            :value="kpi.value"
                            :hint="kpi.hint"
                            :tone="kpi.tone"
                            :icon="kpi.icon"
                            :active="activeKpi === kpi.key"
                            @click="focusKpi(kpi.key)"
                        />
                    </div>
                    <button
                        type="button"
                        class="absolute -top-2 -right-2 w-[22px] h-[22px] rounded-full bg-paper border border-line-strong shadow-sm grid place-items-center text-ink-500 hover:bg-surface-2 hover:text-ink-900 hover:border-ink-300 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35"
                        :aria-label="t('common.hide_kpis')"
                        :title="t('common.hide_kpis')"
                        @click="kpisDismissed = true"
                    >
                        <Icon name="x" cls="sm" />
                    </button>
                </div>

                <!-- Toolbar: filters, replaced in place by the bulk-action bar while rows
                     are selected so the table below never shifts. The min-height keeps the
                     row a constant height across both states. -->
                <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
                    <template v-if="!(hasBulkActions && selected.length)">
                        <div class="flex-1 max-w-[280px] min-w-[180px]">
                            <TextInput v-model="q" clearable :placeholder="t('products.search_placeholder')">
                                <template #prefix><Icon name="search" cls="sm" /></template>
                            </TextInput>
                        </div>
                        <FilterDropdown v-model="brandFilter" :label="t('products.filter_brand')" :options="withAll(t('products.filter_all_brands'), brandOptions)" default-value="all" />
                        <FilterDropdown v-model="typeFilter" :label="t('products.filter_type')" :options="withAll(t('products.filter_all_types'), typeOptions)" default-value="all" />
                        <FilterDropdown v-model="statusFilter" :label="t('products.filter_status')" :options="statusOptions" default-value="all" />
                        <FilterDropdown v-model="stockFilter" :label="t('products.filter_stock')" :options="stockOptions" default-value="all" />
                        <FilterDropdown v-model="tagFilter" :label="t('products.filter_tag')" icon="tag" :options="withAll(t('products.filter_all_tags'), tagOptions)" default-value="all" />
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
                        >{{ t('products.clear_filters') }}</button>
                        <div class="flex-1" />
                        <span class="text-[11.5px] text-ink-500 whitespace-nowrap">{{ t('products.count_of', { shown: products.total, total: totalCount }) }}</span>
                        <Button
                            v-if="kpisDismissed"
                            icon="chart"
                            @click="kpisDismissed = false"
                        >
                            <span class="hidden sm:inline">{{ t('products.show_kpis') }}</span>
                        </Button>
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
                    :rows="products.data"
                    :row-to="(row) => row.edit_url as string"
                    :row-actions="props.tableActions"
                    :selectable="hasBulkActions"
                    :selected="selected"
                    @update:selected="selected = $event"
                >
                    <template #empty>
                        <PageEmpty :title="hasActiveFilters ? t('products.empty_title') : t('products.empty_none_title')">
                            {{ hasActiveFilters ? t('products.empty_description') : t('products.empty_none_description') }}
                            <div v-if="hasActiveFilters" class="mt-3">
                                <Button @click="clearFilters">{{ t('products.clear_filters') }}</Button>
                            </div>
                        </PageEmpty>
                    </template>

                    <template #cell-name="{ row }">
                        <div class="min-w-0 flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-md shrink-0 border border-line overflow-hidden bg-surface-2 grid place-items-center text-ink-700">
                                <img
                                    v-if="row.thumbnail"
                                    :src="row.thumbnail as string"
                                    :alt="String(row.name)"
                                    class="w-full h-full object-cover block"
                                    loading="lazy"
                                >
                                <Icon v-else name="box" cls="sm" />
                            </div>
                            <div class="min-w-0 text-[12.5px] font-medium text-ink-900 truncate">{{ row.name }}</div>
                        </div>
                    </template>

                    <template #cell-status="{ row }">
                        <StatusBadge :tone="statusTone(row.status as string)" dot>
                            {{ row.status_label }}
                        </StatusBadge>
                    </template>

                    <template #cell-brand="{ value }">
                        <span v-if="value" class="text-xs text-ink-700 truncate">{{ value }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>

                    <template #cell-sku="{ row }">
                        <div class="min-w-0 flex items-center gap-1.5">
                            <span v-if="row.sku" class="text-[11.5px] font-mono text-ink-700 truncate">{{ row.sku }}</span>
                            <span v-else class="text-[12.5px] text-ink-400">—</span>
                            <StatusBadge v-if="(row.extra_sku_count as number) > 0" tone="neutral" class="shrink-0">
                                {{ t('products.extra_sku_count', { count: row.extra_sku_count }) }}
                            </StatusBadge>
                        </div>
                    </template>

                    <template #cell-stock="{ value }">
                        <span :class="['text-[12.5px] font-medium [font-variant-numeric:tabular-nums]', (value as number) > 0 ? 'text-ink-900' : 'text-ink-400']">
                            {{ value }}
                        </span>
                    </template>

                    <template #cell-product_type="{ value }">
                        <span class="text-xs text-ink-700 truncate">{{ value }}</span>
                    </template>

                    <template #cell-tags="{ row }">
                        <span v-if="(row.tags as string[]).length" class="text-xs text-ink-500 truncate">{{ (row.tags as string[]).join(', ') }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>
                </DataTable>

                <div class="mt-4">
                    <Pagination :meta="products" />
                </div>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
