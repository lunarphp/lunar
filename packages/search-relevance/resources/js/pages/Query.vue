<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Breadcrumbs, Button, ConfirmDialog, DataTable, PageEmpty, PageHeader, PageZone, SideCard, Tooltip } from '@lunarphp/panel';
import RelativeBar from '../components/RelativeBar.vue';
import TableBlock from '../components/TableBlock.vue';

type LearnedRow = {
    product_id: number;
    name: string;
    relative: number;
    score: number;
    clicks: number;
    baskets: number;
    purchases: number;
    sessions: number;
    last_event_at: string | null;
    typical_position: number | null;
    url: string | null;
}

type VariantRow = {
    raw_query: string;
    searches: number;
}

type ExcludedRow = {
    product_id: number;
    name: string;
    url: string;
}

const props = defineProps<{
    query: string;
    model_type: string;
    learned: LearnedRow[];
    variants: VariantRow[];
    excluded: ExcludedRow[];
    reset_at: string | null;
    urls: { index: string; reset: string };
}>();

const { t } = useI18n();

// Staff levers against a manipulated or embarrassing learned order.
const learnedActions = computed(() => [
    { key: 'exclude', label: t('search-relevance::panel.override_exclude'), icon: 'x', method: 'post', primary: false, confirmation: t('search-relevance::panel.override_exclude_confirm') },
]);

const resetOpen = ref(false);

const reset = (): void => {
    router.post(props.urls.reset, {}, { preserveScroll: true });
};

const include = (row: ExcludedRow): void => {
    router.delete(row.url, { preserveScroll: true });
};

const breadcrumbs = computed(() => [
    { label: t('search-relevance::panel.nav_group') },
    { label: t('search-relevance::panel.title'), href: props.urls.index },
    { label: t('search-relevance::panel.query_title'), current: true },
]);

const learnedColumns = [
    { key: 'name', label: t('search-relevance::panel.column_product'), width: 'minmax(0,1.6fr)' },
    { key: 'relative', label: t('search-relevance::panel.column_relative'), width: '160px' },
    { key: 'clicks', label: t('search-relevance::panel.column_clicks'), width: '80px', align: 'right' as const },
    { key: 'baskets', label: t('search-relevance::panel.column_baskets'), width: '80px', align: 'right' as const },
    { key: 'purchases', label: t('search-relevance::panel.column_purchases'), width: '90px', align: 'right' as const },
    { key: 'sessions', label: t('search-relevance::panel.column_sessions'), width: '90px', align: 'right' as const },
    { key: 'typical_position', label: t('search-relevance::panel.column_typical_position'), width: '120px', align: 'right' as const },
    { key: 'last_event_at', label: t('search-relevance::panel.column_last_event'), width: '150px' },
];

const variantColumns = [
    { key: 'raw_query', label: t('search-relevance::panel.column_raw_query'), width: 'minmax(0,1fr)' },
    { key: 'searches', label: t('search-relevance::panel.column_searches'), width: '90px', align: 'right' as const },
];

const rowTo = (row: Record<string, unknown>): string | null => (row.url as string | null) ?? null;

const formatDate = (value: string | null): string => {
    if (!value) {
        return '-';
    }

    const date = new Date(value.replace(' ', 'T'));

    return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <div data-screen-label="Search relevance query" class="contents">
        <Breadcrumbs :items="breadcrumbs" />

        <PageHeader :title="query" :description="t('search-relevance::panel.query_description')" icon="search">
            <template #actions>
                <Button icon="refresh" @click="resetOpen = true">{{ t('search-relevance::panel.override_reset') }}</Button>
            </template>
        </PageHeader>

        <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
            <PageZone region="main" position="before" />

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] items-start">
                <div class="min-w-0">
                    <TableBlock :title="t('search-relevance::panel.query_learned_title')" >
                        <DataTable
                            v-if="learned.length"
                            :columns="learnedColumns"
                            :rows="learned"
                            row-key="product_id"
                            :row-to="rowTo"
                            :row-actions="learnedActions"
                        >
                            <template #cell-relative="{ row }">
                                <Tooltip :text="`${t('search-relevance::panel.relative_tooltip')} ${t('search-relevance::panel.column_score')}: ${(row as unknown as LearnedRow).score}`">
                                    <RelativeBar :value="(row as unknown as LearnedRow).relative" />
                                </Tooltip>
                            </template>
                            <template #cell-typical_position="{ value }">
                                <Tooltip :text="t('search-relevance::panel.typical_position_tooltip')">
                                    <span>{{ value === null ? '-' : value }}</span>
                                </Tooltip>
                            </template>
                            <template #cell-last_event_at="{ value }">{{ formatDate(value as string | null) }}</template>
                        </DataTable>
                        <PageEmpty v-else>{{ t('search-relevance::panel.query_learned_empty') }}</PageEmpty>
                        <template v-if="reset_at" #footer>{{ t('search-relevance::panel.reset_at', { date: formatDate(reset_at) }) }}</template>
                    </TableBlock>

                    <SideCard :title="t('search-relevance::panel.excluded_title')" class="mt-6">
                        <p class="text-[12px] text-ink-500 mb-2">{{ t('search-relevance::panel.excluded_description') }}</p>
                        <ul v-if="excluded.length" class="flex flex-col gap-2">
                            <li v-for="row in excluded" :key="row.product_id" class="flex items-center justify-between gap-3 text-[12.5px] text-ink-900">
                                <span>{{ row.name }}</span>
                                <Button size="sm" @click="include(row)">{{ t('search-relevance::panel.override_include') }}</Button>
                            </li>
                        </ul>
                        <p v-else class="text-[12.5px] text-ink-700">{{ t('search-relevance::panel.excluded_empty') }}</p>
                    </SideCard>
                </div>

                <TableBlock :title="t('search-relevance::panel.query_variants_title')">
                    <DataTable v-if="variants.length" :columns="variantColumns" :rows="variants" row-key="raw_query" />
                    <PageEmpty v-else>{{ t('search-relevance::panel.query_variants_empty') }}</PageEmpty>
                    <template #footer>{{ t('search-relevance::panel.query_model') }}: <span class="text-ink-700">{{ model_type }}</span></template>
                </TableBlock>
            </div>

            <PageZone region="main" position="after" />
        </div>

        <ConfirmDialog
            v-model:open="resetOpen"
            :title="t('search-relevance::panel.override_reset_title')"
            :description="t('search-relevance::panel.override_reset_description')"
            tone="danger"
            @confirm="reset"
        />
    </div>
</template>
