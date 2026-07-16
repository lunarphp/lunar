<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
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
    company_name: string | null;
    account_ref: string | null;
    created_at: string;
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
    customerGroups: CustomerGroupOption[];
    totalCount: number;
    filters: { q?: string; customer_group_id?: string | number; type?: string; sort?: string; direction?: string };
    urls: { index: string; create: string };
}>();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

const flashSuccess = computed(() => (usePage().props.flash as { success?: string } | undefined)?.success);

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

    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            customer_group_id: groupFilter.value === 'all' ? undefined : groupFilter.value,
            type: typeFilter.value === 'all' ? undefined : typeFilter.value,
            sort: sortOption.sort,
            direction: sortOption.direction,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

// Dropdowns reload immediately; the search box debounces so a reload only fires
// once typing settles (no Search button).
watch([groupFilter, typeFilter, sortKey], reload);

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const hasActiveFilters = computed(
    () => !!q.value.trim() || groupFilter.value !== 'all' || typeFilter.value !== 'all',
);
const clearFilters = (): void => {
    q.value = '';
    groupFilter.value = 'all';
    typeFilter.value = 'all';
    reload();
};

// KPI strip: values are placeholders for now; the dismissed state persists locally.
const KPI_STORAGE_KEY = 'lunar.panel.customers.kpisDismissed';
const kpisDismissed = ref(localStorage.getItem(KPI_STORAGE_KEY) === '1');
watch(kpisDismissed, (value) => localStorage.setItem(KPI_STORAGE_KEY, value ? '1' : '0'));

const kpis = computed(() => [
    { label: 'Total customers', value: props.totalCount, hint: 'across all groups', tone: 'neutral' as const, icon: 'users', delta: { value: '+12', tone: 'sage' as const } },
    { label: 'New (30d)', value: 6, hint: 'joined in the last 30 days', tone: 'sage' as const, icon: 'userPlus', delta: { value: '+5', tone: 'sage' as const } },
    { label: 'B2B accounts', value: 10, hint: 'customers with a company', tone: 'neutral' as const, icon: 'building', delta: { value: '+1', tone: 'sage' as const } },
    { label: 'Avg lifetime value', value: '£3,575.00', hint: 'across customers with orders', tone: 'sage' as const, icon: 'chart', delta: { value: '+4%', tone: 'sage' as const } },
]);

const initials = (name: string): string =>
    name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

const formatDate = (value: string): string => new Date(value).toLocaleDateString();
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Customers" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                </template>
            </Breadcrumbs>

            <PageHeader
                :title="t('customers.title')"
                :description="t('customers.description')"
                icon="users"
            >
                <template #actions>
                    <Button icon="download">{{ t('common.export') }}</Button>
                    <Link :href="urls.create">
                        <Button variant="primary" icon="plus">{{ t('customers.add_customer') }}</Button>
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <div v-if="flashSuccess" class="mb-4 rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink">
                    {{ flashSuccess }}
                </div>

                <!-- KPI strip (values are placeholders); dismissable, restored via "Show KPIs". -->
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
                        aria-label="Hide KPIs"
                        title="Hide KPIs"
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
                            <TextInput v-model="q" :placeholder="t('customers.search_placeholder')">
                                <template #prefix><Icon name="search" cls="sm" /></template>
                            </TextInput>
                        </div>
                        <FilterDropdown v-model="groupFilter" :label="t('customers.filter_group')" icon="flag" :options="groupOptions" default-value="all" />
                        <FilterDropdown v-model="typeFilter" :label="t('customers.filter_type')" :options="typeOptions" default-value="all" />
                        <FilterDropdown v-model="sortKey" :label="t('common.sort')" :options="sortOptions" default-value="recent" />
                        <div class="flex-1" />
                        <span class="text-[11.5px] text-ink-500 whitespace-nowrap">{{ t('customers.count_of', { shown: customers.total, total: totalCount }) }}</span>
                        <Button v-if="kpisDismissed" icon="chart" @click="kpisDismissed = false">
                            <span class="hidden sm:inline">Show KPIs</span>
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
                            <div class="w-7 h-7 rounded-full border border-line bg-surface-2 grid place-items-center text-ink-700 text-[10.5px] font-semibold shrink-0">
                                {{ initials(row.full_name as string) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-[12.5px] text-ink-900 truncate">{{ row.full_name }}</div>
                                <div v-if="row.account_ref" class="text-[11px] text-ink-500 truncate font-mono">{{ row.account_ref }}</div>
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

                    <template #cell-created_at="{ value }">
                        <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ formatDate(value as string) }}</span>
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
