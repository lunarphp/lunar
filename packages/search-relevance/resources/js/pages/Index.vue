<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Breadcrumbs, DataTable, KpiCard, PageHeader, PageZone, SideCard } from '@lunarphp/panel';
import TableBlock from '../components/TableBlock.vue';

type Kpis = {
    searches: number;
    click_through_rate: number;
    conversion_rate: number;
    zero_result_rate: number;
    mean_click_position: number | null;
}

type Uplift = {
    searches: number;
    mrr_shown: number;
    mrr_ranked: number;
    improved_share: number;
    worsened_share: number;
}

type TopQueryRow = {
    query: string;
    searches: number;
    clicks: number;
    conversions: number;
    conversion_rate: number;
    url: string;
}

type QueryRow = {
    query: string;
    searches: number;
    url: string;
}

const props = defineProps<{
    range: string;
    ranges: { value: string; label: string }[];
    kpis: Kpis;
    uplift: Uplift;
    top_queries: TopQueryRow[];
    zero_result_queries: QueryRow[];
    no_click_queries: QueryRow[];
    urls: { index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed(() => [
    { label: t('search-relevance::panel.nav_group') },
    { label: t('search-relevance::panel.title'), current: true },
]);

const setRange = (value: string): void => {
    router.get(props.urls.index, { range: value }, { preserveState: true, preserveScroll: true, replace: true });
};

const percent = (value: number): string => `${value}%`;

const topColumns = [
    { key: 'query', label: t('search-relevance::panel.column_query'), width: 'minmax(0,1.6fr)' },
    { key: 'searches', label: t('search-relevance::panel.column_searches'), width: '110px', align: 'right' as const },
    { key: 'clicks', label: t('search-relevance::panel.column_clicks'), width: '100px', align: 'right' as const },
    { key: 'conversions', label: t('search-relevance::panel.column_conversions'), width: '110px', align: 'right' as const },
    { key: 'conversion_rate', label: t('search-relevance::panel.column_conversion_rate'), width: '100px', align: 'right' as const },
];

const simpleColumns = [
    { key: 'query', label: t('search-relevance::panel.column_query'), width: 'minmax(0,1fr)' },
    { key: 'searches', label: t('search-relevance::panel.column_searches'), width: '110px', align: 'right' as const },
];

const rowTo = (row: Record<string, unknown>): string => row.url as string;

// Uplift tone: sage when the learned order places purchases higher on balance.
const upliftTone = computed<'sage' | 'danger' | 'neutral'>(() => {
    if (props.uplift.searches === 0 || props.uplift.mrr_ranked === props.uplift.mrr_shown) {
        return 'neutral';
    }

    return props.uplift.mrr_ranked > props.uplift.mrr_shown ? 'sage' : 'danger';
});
</script>

<template>
    <div data-screen-label="Search relevance" class="contents">
        <Breadcrumbs :items="breadcrumbs" />

        <PageHeader
            :title="t('search-relevance::panel.title')"
            :description="t('search-relevance::panel.description')"
            icon="chart"
        >
            <template #actions>
                <div
                    class="inline-flex border border-line-strong rounded-md p-0.5 bg-surface-2 gap-0.5 shadow-sm"
                    role="group"
                    :aria-label="t('search-relevance::panel.range')"
                >
                    <button
                        v-for="option in ranges"
                        :key="option.value"
                        type="button"
                        :class="[
                            'h-[26px] px-2.5 rounded-sm text-[12px] font-medium transition-[background-color,color,box-shadow] duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35',
                            range === option.value ? 'bg-surface text-ink-900 shadow-sm' : 'text-ink-500 hover:text-ink-900',
                        ]"
                        :aria-pressed="range === option.value"
                        @click="setRange(option.value)"
                    >
                        {{ option.label }}
                    </button>
                </div>
            </template>
        </PageHeader>

        <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
            <PageZone region="main" position="before" />

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-2.5">
                <KpiCard :label="t('search-relevance::panel.kpi_searches')" :value="kpis.searches" icon="search" tone="sage" />
                <KpiCard :label="t('search-relevance::panel.kpi_click_through_rate')" :value="percent(kpis.click_through_rate)" icon="eye" />
                <KpiCard :label="t('search-relevance::panel.kpi_conversion_rate')" :value="percent(kpis.conversion_rate)" icon="cart" />
                <KpiCard
                    :label="t('search-relevance::panel.kpi_zero_result_rate')"
                    :value="percent(kpis.zero_result_rate)"
                    icon="alert"
                    :tone="kpis.zero_result_rate > 0 ? 'warn' : 'neutral'"
                />
                <KpiCard
                    :label="t('search-relevance::panel.kpi_mean_click_position')"
                    :value="kpis.mean_click_position ?? '-'"
                    :hint="kpis.mean_click_position === null ? t('search-relevance::panel.kpi_no_clicks') : ''"
                    icon="sliders"
                />
            </div>

            <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] items-start">
                <div class="min-w-0">
                    <TableBlock :title="t('search-relevance::panel.top_queries_title')">
                        <DataTable
                            :columns="topColumns"
                            :rows="top_queries"
                            row-key="query"
                            :row-to="rowTo"
                            :empty-text="t('search-relevance::panel.empty_queries')"
                        >
                            <template #cell-conversion_rate="{ value }">{{ percent(value as number) }}</template>
                        </DataTable>
                    </TableBlock>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        <TableBlock :title="t('search-relevance::panel.zero_result_title')" :description="t('search-relevance::panel.zero_result_description')">
                            <DataTable
                                :columns="simpleColumns"
                                :rows="zero_result_queries"
                                row-key="query"
                                :row-to="rowTo"
                                :empty-text="t('search-relevance::panel.zero_result_empty')"
                            />
                        </TableBlock>

                        <TableBlock :title="t('search-relevance::panel.no_click_title')" :description="t('search-relevance::panel.no_click_description')">
                            <DataTable
                                :columns="simpleColumns"
                                :rows="no_click_queries"
                                row-key="query"
                                :row-to="rowTo"
                                :empty-text="t('search-relevance::panel.no_click_empty')"
                            />
                        </TableBlock>
                    </div>
                </div>

                <SideCard :title="t('search-relevance::panel.uplift_title')">
                    <p class="text-[12px] text-ink-500 mb-3">{{ t('search-relevance::panel.uplift_description') }}</p>

                    <template v-if="uplift.searches > 0">
                        <div class="grid grid-cols-2 gap-2.5">
                            <KpiCard :label="t('search-relevance::panel.uplift_mrr_shown')" :value="uplift.mrr_shown" />
                            <KpiCard :label="t('search-relevance::panel.uplift_mrr_ranked')" :value="uplift.mrr_ranked" :tone="upliftTone" />
                            <KpiCard :label="t('search-relevance::panel.uplift_improved')" :value="percent(uplift.improved_share)" tone="sage" />
                            <KpiCard :label="t('search-relevance::panel.uplift_worsened')" :value="percent(uplift.worsened_share)" :tone="uplift.worsened_share > 0 ? 'warn' : 'neutral'" />
                        </div>
                        <div class="mt-3 text-[12px] text-ink-500">
                            {{ t('search-relevance::panel.uplift_searches') }}: <span class="text-ink-900 font-medium">{{ uplift.searches }}</span>
                        </div>
                    </template>
                    <p v-else class="text-[12.5px] text-ink-700">{{ t('search-relevance::panel.uplift_empty') }}</p>
                </SideCard>
            </div>

            <PageZone region="main" position="after" />
        </div>
    </div>
</template>
