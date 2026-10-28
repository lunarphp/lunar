<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { SideCard, http } from '@lunarphp/panel';
import RelativeBar from './RelativeBar.vue';

type QueryRow = {
    query: string;
    relative: number | null;
    clicks: number;
    purchases: number;
    url: string;
}

type Summary = { queries: QueryRow[]; total: number; url: string };

const props = defineProps<{
    product?: { id: number };
}>();

const { t } = useI18n();

// Renders nothing until data arrives, and nothing at all for products with
// no search activity, so untouched products pay no cost on the edit page.
const summary = ref<Summary | null>(null);

const panelPath = (usePage().props.panel as { path?: string } | undefined)?.path ?? 'panel';

onMounted(async () => {
    if (!props.product) {
        return;
    }

    try {
        const payload = await http.get<Summary>(`/${panelPath}/search-relevance/products/${props.product.id}/summary`);

        if (payload.queries.length) {
            summary.value = payload;
        }
    } catch {
        summary.value = null;
    }
});
</script>

<template>
    <SideCard v-if="summary" :title="t('search-relevance::panel.product_card_title')">
        <ul class="flex flex-col gap-2">
            <li v-for="row in summary.queries" :key="row.query">
                <Link :href="row.url" class="block rounded-sm -mx-1 px-1 py-0.5 hover:bg-surface-2">
                    <div class="flex items-center justify-between gap-3 text-[12.5px]">
                        <span class="text-ink-900 font-medium truncate">{{ row.query }}</span>
                        <span class="text-ink-500 shrink-0 [font-variant-numeric:tabular-nums]">{{ row.purchases }} / {{ row.clicks }}</span>
                    </div>
                    <RelativeBar v-if="row.relative !== null" :value="row.relative" class="mt-1" />
                </Link>
            </li>
        </ul>
        <div class="mt-3 flex items-center justify-between gap-3 text-[11px]">
            <span class="text-ink-500">{{ summary.total > summary.queries.length ? t('search-relevance::panel.product_card_more', { count: summary.total - summary.queries.length }) : '' }}</span>
            <Link :href="summary.url" class="text-ink-700 underline underline-offset-2 hover:text-ink-900">{{ t('search-relevance::panel.product_view_report') }}</Link>
        </div>
    </SideCard>
</template>
