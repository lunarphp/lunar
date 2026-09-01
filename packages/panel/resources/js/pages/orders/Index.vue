<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../../components/Button.vue';
import DataTable from '../../components/DataTable.vue';
import { type RowAction } from '../../components/RowActions.vue';
import { type BulkAction } from '../../components/BulkActionsToolbar.vue';
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

type Tone = 'sage' | 'warn' | 'danger' | 'archived' | 'neutral';

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    { label: t('nav.sales') },
    { label: t('nav.orders'), current: true },
];

interface ChannelOption {
    id: number;
    name: string;
}

interface OrderRow {
    id: number;
    reference: string;
    placed_at: string | null;
    customer_name: string | null;
    customer_email: string | null;
    items: number;
    payment_status: string;
    payment_status_label: string;
    fulfilment_status: string;
    fulfilment_status_label: string;
    cancelled: boolean;
    tags: string[];
    total: string;
    show_url: string;
    // Extension-contributed columns land here under their own key.
    [key: string]: unknown;
}

interface OrderColumn {
    key: string;
    label: string;
    width?: string;
    align?: 'left' | 'right' | 'center';
}

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
    orders: Paginated<OrderRow>;
    columns: OrderColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    channels: ChannelOption[];
    orderTags: Record<string, string>;
    paymentOptions: Record<string, string>;
    fulfilmentOptions: Record<string, string>;
    totalCount: number;
    kpis: {
        orders30d: number;
        revenue30d: string | null;
        awaitingPayment: number;
        awaitingFulfilment: number;
    };
    filters: {
        q?: string;
        payment_status?: string;
        fulfilment_status?: string;
        channel_id?: string | number;
        tag?: string;
        lifecycle?: string;
        date?: string;
        sort?: string;
        direction?: string;
    };
    urls: { index: string };
}>();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

const sortOptions: { value: string; label: string; sort: string; direction: string }[] = [
    { value: 'recent', label: t('orders.sort_recent'), sort: 'created_at', direction: 'desc' },
    { value: 'oldest', label: t('orders.sort_oldest'), sort: 'created_at', direction: 'asc' },
    { value: 'total_high', label: t('orders.sort_total_high'), sort: 'total', direction: 'desc' },
    { value: 'total_low', label: t('orders.sort_total_low'), sort: 'total', direction: 'asc' },
];

const q = ref(props.filters.q ?? '');
const paymentFilter = ref<string>(props.filters.payment_status ?? 'all');
const fulfilmentFilter = ref<string>(props.filters.fulfilment_status ?? 'all');
const channelFilter = ref<string | number>(props.filters.channel_id ?? 'all');
const tagFilter = ref<string>(props.filters.tag ?? 'all');
// Orders are an inbox: open is the default view, not "all".
const lifecycleFilter = ref<string>(props.filters.lifecycle ?? 'open');
const dateFilter = ref<string>(props.filters.date ?? 'all');
const sortKey = ref<string>(
    sortOptions.find((o) => o.sort === props.filters.sort && o.direction === (props.filters.direction ?? 'desc'))?.value ?? 'recent',
);

const extensionFilterValues = reactive<Record<string, string>>({ ...props.tableFilterValues });
const renderableExtensionFilters = computed(() =>
    props.tableFilters.filter((filter) => Object.keys(filter.options).length > 0));
const extensionFilterOptions = (filter: ExtensionFilter): FilterOption[] => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];
const activeExtensionFilters = (): Record<string, string> =>
    Object.fromEntries(Object.entries(extensionFilterValues).filter(([, value]) => value !== ''));

const paymentFilterOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('orders.all_payments') },
    ...Object.entries(props.paymentOptions).map(([value, label]) => ({ value, label })),
]);
const fulfilmentFilterOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('orders.all_fulfilment') },
    ...Object.entries(props.fulfilmentOptions).map(([value, label]) => ({ value, label })),
]);
const channelOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('orders.all_channels') },
    ...props.channels.map((channel) => ({ value: channel.id, label: channel.name })),
]);
const tagOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('orders.all_tags') },
    ...Object.entries(props.orderTags).map(([value, label]) => ({ value, label })),
]);
const lifecycleOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('orders.lifecycle_all') },
    { value: 'open', label: t('orders.lifecycle_open') },
    { value: 'closed', label: t('orders.lifecycle_closed') },
    { value: 'cancelled', label: t('orders.lifecycle_cancelled') },
]);
const dateOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('orders.date_all') },
    { value: 'today', label: t('orders.date_today') },
    { value: '7d', label: t('orders.date_7d') },
    { value: '30d', label: t('orders.date_30d') },
    { value: 'this_month', label: t('orders.date_this_month') },
    { value: 'last_month', label: t('orders.date_last_month') },
    { value: 'ytd', label: t('orders.date_ytd') },
]);

const reload = (): void => {
    const sortOption = sortOptions.find((o) => o.value === sortKey.value) ?? sortOptions[0];
    const extensionFilters = activeExtensionFilters();

    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            payment_status: paymentFilter.value === 'all' ? undefined : paymentFilter.value,
            fulfilment_status: fulfilmentFilter.value === 'all' ? undefined : fulfilmentFilter.value,
            channel_id: channelFilter.value === 'all' ? undefined : channelFilter.value,
            tag: tagFilter.value === 'all' ? undefined : tagFilter.value,
            // Omit when 'open' (the default the server applies); send 'all' and the
            // others explicitly.
            lifecycle: lifecycleFilter.value === 'open' ? undefined : lifecycleFilter.value,
            date: dateFilter.value === 'all' ? undefined : dateFilter.value,
            sort: sortOption.sort,
            direction: sortOption.direction,
            filter: Object.keys(extensionFilters).length ? extensionFilters : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch([paymentFilter, fulfilmentFilter, channelFilter, tagFilter, lifecycleFilter, dateFilter, sortKey], reload);
watch(extensionFilterValues, reload);

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const hasActiveFilters = computed(
    () => !!q.value.trim()
        || paymentFilter.value !== 'all'
        || fulfilmentFilter.value !== 'all'
        || channelFilter.value !== 'all'
        || tagFilter.value !== 'all'
        || lifecycleFilter.value !== 'open'
        || dateFilter.value !== 'all'
        || Object.keys(activeExtensionFilters()).length > 0,
);
const clearFilters = (): void => {
    q.value = '';
    paymentFilter.value = 'all';
    fulfilmentFilter.value = 'all';
    channelFilter.value = 'all';
    tagFilter.value = 'all';
    lifecycleFilter.value = 'open';
    dateFilter.value = 'all';
    Object.keys(extensionFilterValues).forEach((key) => {
        extensionFilterValues[key] = '';
    });
    reload();
};

const KPI_STORAGE_KEY = 'lunar.panel.orders.kpisDismissed';
const kpisDismissed = ref(localStorage.getItem(KPI_STORAGE_KEY) === '1');
watch(kpisDismissed, (value) => localStorage.setItem(KPI_STORAGE_KEY, value ? '1' : '0'));

const kpis = computed(() => [
    { label: t('orders.kpi_orders_label'), value: props.kpis.orders30d, hint: t('orders.kpi_orders_hint'), tone: 'neutral' as const, icon: 'cart' },
    { label: t('orders.kpi_revenue_label'), value: props.kpis.revenue30d ?? '—', hint: t('orders.kpi_revenue_hint'), tone: 'sage' as const, icon: 'chart' },
    { label: t('orders.kpi_awaiting_payment_label'), value: props.kpis.awaitingPayment, hint: t('orders.kpi_awaiting_payment_hint'), tone: 'warn' as const, icon: 'clock' },
    { label: t('orders.kpi_awaiting_fulfilment_label'), value: props.kpis.awaitingFulfilment, hint: t('orders.kpi_awaiting_fulfilment_hint'), tone: 'warn' as const, icon: 'box' },
]);

// Tones follow the prototype: settled = sage, in-flight = warn, reversed/failed = danger.
const PAYMENT_TONES: Record<string, Tone> = {
    paid: 'sage',
    authorized: 'warn',
    pending: 'warn',
    'partially-paid': 'warn',
    'partially-refunded': 'danger',
    refunded: 'danger',
    voided: 'danger',
};
const FULFILMENT_TONES: Record<string, Tone> = {
    fulfilled: 'sage',
    unfulfilled: 'warn',
    'partially-fulfilled': 'warn',
    'partially-returned': 'warn',
    returned: 'danger',
};
const paymentTone = (key: string): Tone => PAYMENT_TONES[key] ?? 'neutral';
const fulfilmentTone = (key: string): Tone => FULFILMENT_TONES[key] ?? 'neutral';

const formatShortDate = (value: string): string =>
    new Date(value).toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Orders" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader
                :title="t('orders.title')"
                :description="t('orders.description')"
                icon="cart"
            />

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

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

                <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
                    <div class="flex-1 max-w-[280px] min-w-[180px]">
                        <TextInput v-model="q" clearable :placeholder="t('orders.search_placeholder')">
                            <template #prefix><Icon name="search" cls="sm" /></template>
                        </TextInput>
                    </div>
                    <FilterDropdown v-model="paymentFilter" :label="t('orders.filter_payment')" :options="paymentFilterOptions" default-value="all" />
                    <FilterDropdown v-model="fulfilmentFilter" :label="t('orders.filter_fulfilment')" :options="fulfilmentFilterOptions" default-value="all" />
                    <FilterDropdown v-model="channelFilter" :label="t('orders.filter_channel')" :options="channelOptions" default-value="all" />
                    <FilterDropdown v-if="Object.keys(orderTags).length" v-model="tagFilter" :label="t('orders.filter_tag')" icon="tag" :options="tagOptions" default-value="all" />
                    <FilterDropdown v-model="dateFilter" :label="t('orders.filter_date')" icon="calendar" :options="dateOptions" default-value="all" />
                    <FilterDropdown v-model="lifecycleFilter" :label="t('orders.filter_lifecycle')" :options="lifecycleOptions" default-value="open" />
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
                    >{{ t('orders.clear_filters') }}</button>
                    <div class="flex-1" />
                    <span class="text-[11.5px] text-ink-500 whitespace-nowrap">{{ t('orders.count_of', { shown: orders.total, total: totalCount }) }}</span>
                    <Button v-if="kpisDismissed" icon="chart" @click="kpisDismissed = false">
                        <span class="hidden sm:inline">{{ t('orders.show_kpis') }}</span>
                    </Button>
                </div>

                <DataTable
                    :columns="props.columns"
                    :rows="orders.data"
                    :row-to="(row) => row.show_url as string"
                    :row-actions="props.tableActions"
                    :selectable="hasBulkActions"
                    :selected="selected"
                    @update:selected="selected = $event"
                >
                    <template #empty>
                        <PageEmpty :title="t('orders.empty_title')">
                            {{ t('orders.empty_body') }}
                            <div v-if="hasActiveFilters" class="mt-3">
                                <Button @click="clearFilters">{{ t('orders.clear_filters') }}</Button>
                            </div>
                        </PageEmpty>
                    </template>

                    <template #cell-reference="{ row }">
                        <div class="min-w-0 flex items-center gap-2">
                            <span class="text-[12.5px] text-ink-900 font-mono tracking-[-0.01em] truncate">{{ row.reference }}</span>
                            <StatusBadge v-if="row.cancelled" tone="danger" size="sm">{{ t('orders.lifecycle_cancelled') }}</StatusBadge>
                        </div>
                    </template>

                    <template #cell-placed_at="{ value }">
                        <span v-if="value" class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ formatShortDate(value as string) }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>

                    <template #cell-customer="{ row }">
                        <div class="min-w-0">
                            <div v-if="row.customer_name" class="text-[12.5px] text-ink-900 truncate">{{ row.customer_name }}</div>
                            <div v-else class="text-[12.5px] text-ink-400">{{ t('orders.guest') }}</div>
                            <div v-if="row.customer_email" class="text-[11px] text-ink-500 truncate">{{ row.customer_email }}</div>
                        </div>
                    </template>

                    <template #cell-items="{ value }">
                        <span class="text-[12.5px] text-ink-700 [font-variant-numeric:tabular-nums]">{{ value }}</span>
                    </template>

                    <template #cell-payment_status="{ row }">
                        <StatusBadge :tone="paymentTone(row.payment_status as string)" size="sm" dot>{{ row.payment_status_label }}</StatusBadge>
                    </template>

                    <template #cell-fulfilment_status="{ row }">
                        <StatusBadge :tone="fulfilmentTone(row.fulfilment_status as string)" size="sm" dot>{{ row.fulfilment_status_label }}</StatusBadge>
                    </template>

                    <template #cell-tags="{ value }">
                        <div class="min-w-0 flex flex-wrap gap-1">
                            <StatusBadge v-for="tag in (value as string[])" :key="tag" size="sm">{{ tag }}</StatusBadge>
                            <span v-if="!(value as string[]).length" class="text-[12.5px] text-ink-400">—</span>
                        </div>
                    </template>

                    <template #cell-total="{ value }">
                        <span class="text-[12.5px] text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ value }}</span>
                    </template>
                </DataTable>

                <div class="mt-4">
                    <Pagination :meta="orders" />
                </div>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
