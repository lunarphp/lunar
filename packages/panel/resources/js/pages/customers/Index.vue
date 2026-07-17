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
import KpiCard from '../../components/KpiCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import TextInput from '../../components/TextInput.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import type { BreadcrumbItem } from '../../components/Breadcrumbs.vue';

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    { label: t('nav.sales') },
    { label: t('nav.customers'), current: true },
];

interface CustomerGroupOption {
    id: number;
    name: string;
}

interface CustomerRow {
    id: number;
    full_name: string;
    first_name: string;
    last_name: string;
    company_name: string | null;
    account_ref: string | null;
    email: string | null;
    created_at: string;
    orders_count: number;
    total_spend: string | null;
    last_order_at: string | null;
    customer_groups: CustomerGroupOption[];
    edit_url: string;
    // Extension-contributed columns land here under their own key.
    [key: string]: unknown;
}

interface CustomerColumn {
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
    customers: Paginated<CustomerRow>;
    columns: CustomerColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    customerGroups: CustomerGroupOption[];
    totalCount: number;
    kpis: {
        total: number;
        newLast30Days: number;
        business: number;
        avgLifetimeValue: string | null;
        avgLifetimeValueDelta: number | null;
    };
    filters: { q?: string; customer_group_id?: string | number; type?: string; sort?: string; direction?: string };
    urls: { index: string; create: string };
}>();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

// Sort options fold the backend's sort + direction pair into a single dropdown value,
// matching the prototype's one-control sort.
const sortOptions: { value: string; label: string; sort: string; direction: string }[] = [
    { value: 'recent', label: t('customers.sort_recent'), sort: 'created_at', direction: 'desc' },
    { value: 'oldest', label: t('customers.sort_oldest'), sort: 'created_at', direction: 'asc' },
    { value: 'name', label: t('customers.sort_name'), sort: 'last_name', direction: 'asc' },
    { value: 'company', label: t('customers.sort_company'), sort: 'company_name', direction: 'asc' },
];

const q = ref(props.filters.q ?? '');
const groupFilter = ref<string | number>(props.filters.customer_group_id ?? 'all');
const typeFilter = ref<string>(props.filters.type ?? 'all');

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

const groupOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('customers.all_groups') },
    ...props.customerGroups.map((group) => ({ value: group.id, label: group.name })),
]);

const typeOptions: FilterOption[] = [
    { value: 'all', label: t('customers.type_all') },
    { value: 'individual', label: t('customers.type_individual') },
    { value: 'business', label: t('customers.type_business') },
];

const reload = (): void => {
    const sortOption = sortOptions.find((o) => o.value === sortKey.value) ?? sortOptions[0];

    const extensionFilters = activeExtensionFilters();

    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            customer_group_id: groupFilter.value === 'all' ? undefined : groupFilter.value,
            type: typeFilter.value === 'all' ? undefined : typeFilter.value,
            sort: sortOption.sort,
            direction: sortOption.direction,
            filter: Object.keys(extensionFilters).length ? extensionFilters : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

// Dropdowns reload immediately; the search box debounces so a reload only fires
// once typing settles (no Search button).
watch([groupFilter, typeFilter, sortKey], reload);
watch(extensionFilterValues, reload);

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const hasActiveFilters = computed(
    () => !!q.value.trim()
        || groupFilter.value !== 'all'
        || typeFilter.value !== 'all'
        || Object.keys(activeExtensionFilters()).length > 0,
);
const clearFilters = (): void => {
    q.value = '';
    groupFilter.value = 'all';
    typeFilter.value = 'all';
    Object.keys(extensionFilterValues).forEach((key) => {
        extensionFilterValues[key] = '';
    });
    reload();
};

// KPI strip; the dismissed state persists locally.
const KPI_STORAGE_KEY = 'lunar.panel.customers.kpisDismissed';
const kpisDismissed = ref(localStorage.getItem(KPI_STORAGE_KEY) === '1');
watch(kpisDismissed, (value) => localStorage.setItem(KPI_STORAGE_KEY, value ? '1' : '0'));

// The lifetime-value delta compares against the average as it stood 30 days
// ago; a drop stays neutral rather than alarming.
const lifetimeValueDelta = computed(() => {
    const delta = props.kpis.avgLifetimeValueDelta;

    if (delta === null) {
        return undefined;
    }

    return { value: `${delta > 0 ? '+' : ''}${delta}%`, tone: delta >= 0 ? ('sage' as const) : ('neutral' as const) };
});

const kpis = computed(() => [
    { label: t('customers.kpi_total_label'), value: props.kpis.total, hint: t('customers.kpi_total_hint'), tone: 'neutral' as const, icon: 'users', delta: undefined },
    { label: t('customers.kpi_new_label'), value: props.kpis.newLast30Days, hint: t('customers.kpi_new_hint'), tone: 'sage' as const, icon: 'userPlus', delta: undefined },
    { label: t('customers.kpi_business_label'), value: props.kpis.business, hint: t('customers.kpi_business_hint'), tone: 'neutral' as const, icon: 'building', delta: undefined },
    { label: t('customers.kpi_ltv_label'), value: props.kpis.avgLifetimeValue ?? '—', hint: t('customers.kpi_ltv_hint'), tone: 'sage' as const, icon: 'chart', delta: lifetimeValueDelta.value },
]);

const initials = (firstName: string, lastName: string): string =>
    ((firstName?.[0] ?? '') + (lastName?.[0] ?? '')).toUpperCase() || '?';

// Soft avatar tints, assigned per customer id so a customer keeps their colour
// across pages and reloads.
const AVATAR_TONES = [
    'bg-sage-soft border-sage-border',
    'bg-warn-soft border-warn-border',
    'bg-danger-soft border-danger-border',
];

const avatarTone = (id: number): string => AVATAR_TONES[id % AVATAR_TONES.length];

const formatShortDate = (value: string): string =>
    new Date(value).toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Customers" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader
                :title="t('customers.title')"
                :description="t('customers.description')"
                icon="users"
            >
                <template #actions>
                    <Link :href="urls.create">
                        <Button variant="primary" icon="plus">{{ t('customers.add_customer') }}</Button>
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <!-- KPI strip; dismissable, restored via "Show KPIs". -->
                <div v-if="!kpisDismissed" class="mb-5 relative">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <KpiCard
                            v-for="kpi in kpis"
                            :key="kpi.label"
                            :label="kpi.label"
                            :value="kpi.value"
                            :hint="kpi.hint"
                            :tone="kpi.tone"
                            :icon="kpi.icon"
                            :delta="kpi.delta"
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
                            <TextInput v-model="q" clearable :placeholder="t('customers.search_placeholder')">
                                <template #prefix><Icon name="search" cls="sm" /></template>
                            </TextInput>
                        </div>
                        <FilterDropdown v-model="groupFilter" :label="t('customers.filter_group')" icon="flag" :options="groupOptions" default-value="all" />
                        <FilterDropdown v-model="typeFilter" :label="t('customers.filter_type')" :options="typeOptions" default-value="all" />
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
                        >{{ t('customers.clear_filters') }}</button>
                        <div class="flex-1" />
                        <span class="text-[11.5px] text-ink-500 whitespace-nowrap">{{ t('customers.count_of', { shown: customers.total, total: totalCount }) }}</span>
                        <Button v-if="kpisDismissed" icon="chart" @click="kpisDismissed = false">
                            <span class="hidden sm:inline">{{ t('customers.show_kpis') }}</span>
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
                    :rows="customers.data"
                    :row-to="(row) => row.edit_url as string"
                    :row-actions="props.tableActions"
                    :selectable="hasBulkActions"
                    :selected="selected"
                    @update:selected="selected = $event"
                >
                    <template #empty>
                        <PageEmpty :title="t('customers.empty_title')">
                            {{ t('customers.empty_body') }}
                            <div v-if="hasActiveFilters" class="mt-3">
                                <Button @click="clearFilters">{{ t('customers.clear_filters') }}</Button>
                            </div>
                        </PageEmpty>
                    </template>

                    <template #cell-full_name="{ row }">
                        <div class="min-w-0 flex items-center gap-2.5">
                            <div
                                :class="[
                                    'w-7 h-7 rounded-full border grid place-items-center text-ink-700 text-[10.5px] font-semibold shrink-0',
                                    avatarTone(row.id as number),
                                ]"
                            >
                                {{ initials(row.first_name as string, row.last_name as string) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-[12.5px] text-ink-900 truncate">{{ row.full_name }}</div>
                                <div v-if="row.email" class="text-[11px] text-ink-500 truncate">{{ row.email }}</div>
                                <div v-else-if="row.account_ref" class="text-[11px] text-ink-500 truncate font-mono">{{ row.account_ref }}</div>
                            </div>
                        </div>
                    </template>

                    <template #cell-company_name="{ value }">
                        <span v-if="value" class="text-[12.5px] text-ink-900 truncate">{{ value }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>

                    <template #cell-customer_groups="{ value }">
                        <div class="min-w-0 flex flex-wrap gap-1">
                            <StatusBadge v-for="group in (value as CustomerGroupOption[])" :key="group.id" size="sm">{{ group.name }}</StatusBadge>
                            <span v-if="!(value as CustomerGroupOption[]).length" class="text-[12.5px] text-ink-400">—</span>
                        </div>
                    </template>

                    <template #cell-orders_count="{ value }">
                        <span class="text-[12.5px] text-ink-700 [font-variant-numeric:tabular-nums]">{{ value }}</span>
                    </template>

                    <template #cell-total_spend="{ value }">
                        <span v-if="value" class="text-[12.5px] text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ value }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>

                    <template #cell-last_order_at="{ value }">
                        <span v-if="value" class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ formatShortDate(value as string) }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>
                </DataTable>

                <div class="mt-4">
                    <Pagination :meta="customers" />
                </div>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
