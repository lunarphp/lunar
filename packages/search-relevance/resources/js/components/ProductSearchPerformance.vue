<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { DataTable, PageEmpty, http } from '@lunarphp/panel';
import RelativeBar from './RelativeBar.vue';
import TableBlock from './TableBlock.vue';

type QueryRow = {
    query: string;
    relative: number | null;
    clicks: number;
    baskets: number;
    purchases: number;
    url: string;
}

const props = defineProps<{
    product?: { id: number };
}>();

const { t } = useI18n();

const rows = ref<QueryRow[]>([]);
const loading = ref(true);
const failed = ref(false);

const columns = [
    { key: 'query', label: t('search-relevance::panel.column_query'), width: 'minmax(0,1.4fr)' },
    { key: 'relative', label: t('search-relevance::panel.column_relative'), width: '150px' },
    { key: 'clicks', label: t('search-relevance::panel.column_clicks'), width: '80px', align: 'right' as const },
    { key: 'baskets', label: t('search-relevance::panel.column_baskets'), width: '80px', align: 'right' as const },
    { key: 'purchases', label: t('search-relevance::panel.column_purchases'), width: '90px', align: 'right' as const },
];

const rowTo = (row: Record<string, unknown>): string => row.url as string;

const panelPath = (usePage().props.panel as { path?: string } | undefined)?.path ?? 'panel';
const endpoint = (id: number): string => `/${panelPath}/search-relevance/products/${id}`;

onMounted(async () => {
    if (!props.product) {
        loading.value = false;

        return;
    }

    try {
        const payload = await http.get<{ queries: QueryRow[] }>(endpoint(props.product.id));
        rows.value = payload.queries;
    } catch {
        failed.value = true;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <TableBlock :title="t('search-relevance::panel.product_title')" :description="t('search-relevance::panel.product_description')" bordered>
        <PageEmpty v-if="loading">{{ t('search-relevance::panel.product_loading') }}</PageEmpty>
        <PageEmpty v-else-if="failed">{{ t('search-relevance::panel.product_error') }}</PageEmpty>
        <DataTable v-else-if="rows.length" :columns="columns" :rows="rows" row-key="query" :row-to="rowTo">
            <template #cell-relative="{ value }">
                <RelativeBar :value="value as number | null" />
            </template>
        </DataTable>
        <PageEmpty v-else>{{ t('search-relevance::panel.product_empty') }}</PageEmpty>
    </TableBlock>
</template>
