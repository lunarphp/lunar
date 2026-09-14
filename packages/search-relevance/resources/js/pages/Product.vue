<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Breadcrumbs, Button, DataTable, PageHeader, PageZone } from '@lunarphp/panel';
import RelativeBar from '../components/RelativeBar.vue';
import TableBlock from '../components/TableBlock.vue';

type QueryRow = {
    query: string;
    relative: number | null;
    clicks: number;
    baskets: number;
    purchases: number;
    url: string;
}

const props = defineProps<{
    product: { id: number; name: string; edit_url: string };
    queries: QueryRow[];
    urls: { index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed(() => [
    { label: t('search-relevance::panel.nav_group') },
    { label: t('search-relevance::panel.title'), href: props.urls.index },
    { label: props.product.name, current: true },
]);

const columns = [
    { key: 'query', label: t('search-relevance::panel.column_query'), width: 'minmax(0,1.4fr)' },
    { key: 'relative', label: t('search-relevance::panel.column_relative'), width: '160px' },
    { key: 'clicks', label: t('search-relevance::panel.column_clicks'), width: '80px', align: 'right' as const },
    { key: 'baskets', label: t('search-relevance::panel.column_baskets'), width: '80px', align: 'right' as const },
    { key: 'purchases', label: t('search-relevance::panel.column_purchases'), width: '90px', align: 'right' as const },
];

const rowTo = (row: Record<string, unknown>): string => row.url as string;
</script>

<template>
    <div data-screen-label="Search relevance product" class="contents">
        <Breadcrumbs :items="breadcrumbs" />

        <PageHeader :title="product.name" :description="t('search-relevance::panel.product_description')" icon="box">
            <template #actions>
                <Button icon="edit" @click="router.visit(product.edit_url)">{{ t('search-relevance::panel.product_edit') }}</Button>
            </template>
        </PageHeader>

        <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
            <PageZone region="main" position="before" />

            <TableBlock :title="t('search-relevance::panel.product_title')">
                <DataTable :columns="columns" :rows="queries" row-key="query" :row-to="rowTo" :empty-text="t('search-relevance::panel.product_empty')">
                    <template #cell-relative="{ value }">
                        <RelativeBar :value="value as number | null" />
                    </template>
                </DataTable>
            </TableBlock>

            <PageZone region="main" position="after" />
        </div>
    </div>
</template>
