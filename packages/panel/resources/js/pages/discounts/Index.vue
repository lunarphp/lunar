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
import UsageMeter from '../../components/UsageMeter.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import type { BreadcrumbItem } from '../../components/Breadcrumbs.vue';

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    { label: t('nav.sales') },
    { label: t('nav.discounts'), current: true },
];

type DiscountStatus = 'active' | 'scheduled' | 'expired' | 'pending';

interface DiscountRow {
    id: number;
    name: string;
    handle: string;
    status: DiscountStatus;
    status_label: string;
    type: string;
    type_label: string;
    effect: string | null;
    coupon: string | null;
    starts_at: string | null;
    ends_at: string | null;
    uses: number;
    max_uses: number | null;
    priority: number;
    edit_url: string;
    // Extension-contributed columns land here under their own key.
    [key: string]: unknown;
}

interface DiscountColumn {
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
    discounts: Paginated<DiscountRow>;
    columns: DiscountColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    types: { value: string; label: string }[];
    channels: { id: number; name: string }[];
    customerGroups: { id: number; name: string }[];
    totalCount: number;
    kpis: { active: number; scheduled: number; endingSoon: number; redemptions: number };
    filters: {
        q?: string;
        status?: string;
        type?: string;
        channel_id?: string;
        customer_group_id?: string;
        redemption?: string;
        sort?: string;
        direction?: string;
    };
    urls: { index: string; create: string };
}>();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

const KPI_STORAGE_KEY = 'lunar.panel.discounts.kpisDismissed';
const kpisDismissed = ref(localStorage.getItem(KPI_STORAGE_KEY) === '1');
watch(kpisDismissed, (value) => localStorage.setItem(KPI_STORAGE_KEY, value ? '1' : '0'));

const kpis = computed(() => [
    { label: t('discounts.kpi_active_label'), value: props.kpis.active, hint: t('discounts.kpi_active_hint'), tone: 'sage' as const, icon: 'percent' },
    { label: t('discounts.kpi_scheduled_label'), value: props.kpis.scheduled, hint: t('discounts.kpi_scheduled_hint'), tone: 'neutral' as const, icon: 'calendar' },
    { label: t('discounts.kpi_ending_label'), value: props.kpis.endingSoon, hint: t('discounts.kpi_ending_hint'), tone: 'warn' as const, icon: 'clock' },
    { label: t('discounts.kpi_redemptions_label'), value: props.kpis.redemptions, hint: t('discounts.kpi_redemptions_hint'), tone: 'neutral' as const, icon: 'chart' },
]);

// Sort options fold the backend's sort + direction pair into one dropdown.
const sortOptions: { value: string; label: string; sort: string; direction: string }[] = [
    { value: 'priority', label: t('discounts.sort_priority'), sort: 'priority', direction: 'asc' },
    { value: 'name', label: t('discounts.sort_name'), sort: 'name', direction: 'asc' },
    { value: 'starts', label: t('discounts.sort_starts'), sort: 'starts_at', direction: 'asc' },
    { value: 'ends', label: t('discounts.sort_ends'), sort: 'ends_at', direction: 'asc' },
    { value: 'uses', label: t('discounts.sort_uses'), sort: 'uses', direction: 'desc' },
];

const q = ref(props.filters.q ?? '');
const statusFilter = ref<string>(props.filters.status ?? 'all');
const typeFilter = ref<string>(props.filters.type ?? 'all');
const channelFilter = ref<string>(props.filters.channel_id ?? 'all');
const customerGroupFilter = ref<string>(props.filters.customer_group_id ?? 'all');
const redemptionFilter = ref<string>(props.filters.redemption ?? 'all');

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
    sortOptions.find((o) => o.sort === props.filters.sort && o.direction === (props.filters.direction ?? 'asc'))?.value ?? 'priority',
);

const statusOptions: FilterOption[] = [
    { value: 'all', label: t('discounts.filter_all_statuses') },
    { value: 'active', label: t('discounts.status_active') },
    { value: 'scheduled', label: t('discounts.status_scheduled') },
    { value: 'expired', label: t('discounts.status_expired') },
];

const typeOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('discounts.filter_all_types') },
    ...props.types.map((type) => ({ value: type.value, label: type.label })),
]);

const channelOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('discounts.filter_all_channels') },
    ...props.channels.map((channel) => ({ value: String(channel.id), label: channel.name })),
]);

const customerGroupOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('discounts.filter_all_customer_groups') },
    ...props.customerGroups.map((group) => ({ value: String(group.id), label: group.name })),
]);

const redemptionOptions: FilterOption[] = [
    { value: 'all', label: t('discounts.filter_all_redemptions') },
    { value: 'coupon', label: t('discounts.redemption_coupon') },
    { value: 'automatic', label: t('discounts.redemption_automatic') },
];

const unset = (value: string): string | undefined => (value === 'all' ? undefined : value);

const reload = (): void => {
    const sortOption = sortOptions.find((o) => o.value === sortKey.value) ?? sortOptions[0];
    const extensionFilters = activeExtensionFilters();

    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            status: unset(statusFilter.value),
            type: unset(typeFilter.value),
            channel_id: unset(channelFilter.value),
            customer_group_id: unset(customerGroupFilter.value),
            redemption: unset(redemptionFilter.value),
            sort: sortOption.sort,
            direction: sortOption.direction,
            filter: Object.keys(extensionFilters).length ? extensionFilters : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch([statusFilter, typeFilter, channelFilter, customerGroupFilter, redemptionFilter, sortKey], reload);
watch(extensionFilterValues, reload);

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const hasActiveFilters = computed(
    () => !!q.value.trim()
        || [statusFilter, typeFilter, channelFilter, customerGroupFilter, redemptionFilter].some((f) => f.value !== 'all')
        || Object.keys(activeExtensionFilters()).length > 0,
);

const clearFilters = (): void => {
    q.value = '';
    [statusFilter, typeFilter, channelFilter, customerGroupFilter, redemptionFilter].forEach((f) => {
        f.value = 'all';
    });
    Object.keys(extensionFilterValues).forEach((key) => {
        extensionFilterValues[key] = '';
    });
    reload();
};

const statusTone = (status: DiscountStatus): 'sage' | 'warn' | 'neutral' | 'danger' => {
    if (status === 'active') {
        return 'sage';
    }

    if (status === 'scheduled') {
        return 'neutral';
    }

    if (status === 'expired') {
        return 'danger';
    }

    return 'warn';
};

const dateFormatter = new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' });

const formatDate = (value: string | null): string | null =>
    (value ? dateFormatter.format(new Date(value)) : null);
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Discounts" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader
                :title="t('discounts.title')"
                :description="t('discounts.description')"
                icon="percent"
            >
                <template #actions>
                    <Link :href="urls.create">
                        <Button variant="primary" icon="plus">{{ t('discounts.new_discount') }}</Button>
                    </Link>
                </template>
            </PageHeader>

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
                    <template v-if="!(hasBulkActions && selected.length)">
                        <div class="flex-1 max-w-[280px] min-w-[180px]">
                            <TextInput v-model="q" clearable :placeholder="t('discounts.search_placeholder')">
                                <template #prefix><Icon name="search" cls="sm" /></template>
                            </TextInput>
                        </div>
                        <FilterDropdown v-model="statusFilter" :label="t('discounts.filter_status')" :options="statusOptions" default-value="all" />
                        <FilterDropdown v-model="typeFilter" :label="t('discounts.filter_type')" :options="typeOptions" default-value="all" />
                        <FilterDropdown v-model="redemptionFilter" :label="t('discounts.filter_redemption')" :options="redemptionOptions" default-value="all" />
                        <FilterDropdown v-model="channelFilter" :label="t('discounts.filter_channel')" :options="channelOptions" default-value="all" />
                        <FilterDropdown v-model="customerGroupFilter" :label="t('discounts.filter_customer_group')" :options="customerGroupOptions" default-value="all" />
                        <FilterDropdown
                            v-for="filter in renderableExtensionFilters"
                            :key="filter.key"
                            v-model="extensionFilterValues[filter.key]"
                            :label="filter.label"
                            :options="extensionFilterOptions(filter)"
                            default-value=""
                        />
                        <FilterDropdown v-model="sortKey" :label="t('common.sort')" :options="sortOptions" default-value="priority" />
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="text-[12px] text-ink-500 underline underline-offset-2 whitespace-nowrap rounded-sm hover:text-ink-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                            @click="clearFilters"
                        >{{ t('discounts.clear_filters') }}</button>
                        <div class="flex-1" />
                        <span class="text-[11.5px] text-ink-500 whitespace-nowrap">{{ t('discounts.count_of', { shown: discounts.total, total: totalCount }) }}</span>
                        <Button v-if="kpisDismissed" icon="chart" @click="kpisDismissed = false">
                            <span class="hidden sm:inline">{{ t('discounts.show_kpis') }}</span>
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
                    :rows="discounts.data"
                    :row-to="(row) => row.edit_url as string"
                    :row-actions="props.tableActions"
                    :selectable="hasBulkActions"
                    :selected="selected"
                    @update:selected="selected = $event"
                >
                    <template #empty>
                        <PageEmpty :title="hasActiveFilters ? t('discounts.empty_title') : t('discounts.empty_none_title')">
                            {{ hasActiveFilters ? t('discounts.empty_description') : t('discounts.empty_none_description') }}
                            <div v-if="hasActiveFilters" class="mt-3">
                                <Button @click="clearFilters">{{ t('discounts.clear_filters') }}</Button>
                            </div>
                        </PageEmpty>
                    </template>

                    <template #cell-status="{ row }">
                        <StatusBadge :tone="statusTone(row.status as DiscountStatus)" dot>
                            {{ row.status_label }}
                        </StatusBadge>
                    </template>

                    <template #cell-name="{ row }">
                        <div class="min-w-0">
                            <div class="text-[12.5px] font-medium text-ink-900 truncate">{{ row.name }}</div>
                            <div class="text-[11px] font-mono text-ink-500 truncate">{{ row.handle }}</div>
                        </div>
                    </template>

                    <template #cell-type_label="{ row }">
                        <div class="min-w-0">
                            <div class="text-[12.5px] text-ink-900 truncate">{{ row.type_label }}</div>
                            <div v-if="row.effect" class="text-[11px] text-ink-500 truncate">{{ row.effect }}</div>
                        </div>
                    </template>

                    <template #cell-coupon="{ row }">
                        <span v-if="row.coupon" class="text-[11.5px] font-mono text-ink-900">{{ row.coupon }}</span>
                        <StatusBadge v-else tone="neutral">{{ t('discounts.automatic') }}</StatusBadge>
                    </template>

                    <template #cell-window="{ row }">
                        <div class="text-[11.5px] text-ink-700 whitespace-nowrap">
                            {{ formatDate(row.starts_at as string | null) }}
                            <span class="text-ink-400">&rarr;</span>
                            <span v-if="row.ends_at">{{ formatDate(row.ends_at as string | null) }}</span>
                            <span v-else class="text-ink-400">{{ t('discounts.no_end_date') }}</span>
                        </div>
                    </template>

                    <template #cell-usage="{ row }">
                        <UsageMeter :used="row.uses as number" :max="row.max_uses as number | null" />
                    </template>

                    <template #cell-priority="{ value }">
                        <span class="text-[12.5px] text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ value }}</span>
                    </template>
                </DataTable>

                <div class="mt-4">
                    <Pagination :meta="discounts" />
                </div>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
