<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PageEmpty from '../../PageEmpty.vue';
import StatusBadge from '../../StatusBadge.vue';

type Tone = 'sage' | 'warn' | 'danger' | 'archived' | 'neutral';

interface OrderRow {
    id: number;
    reference: string;
    customer: string | null;
    status: string;
    status_label: string;
    placed_at: string | null;
    placed_at_human: string | null;
    total: string;
}

defineProps<{ data: { orders: OrderRow[] } }>();

const { t } = useI18n();

const STATUS_TONE: Record<string, Tone> = {
    open: 'sage',
    closed: 'archived',
    cancelled: 'danger',
};
</script>

<template>
    <PageEmpty v-if="!data.orders.length" :title="t('dashboard.no_orders_title')">
        {{ t('dashboard.no_orders_description') }}
    </PageEmpty>
    <div v-else class="divide-y divide-line">
        <div
            v-for="order in data.orders"
            :key="order.id"
            class="grid grid-cols-[1fr_auto] items-center gap-3 py-2.5 first:pt-0 last:pb-0"
        >
            <div class="min-w-0">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="font-mono text-[12px] text-ink-700">{{ order.reference }}</span>
                    <StatusBadge :tone="STATUS_TONE[order.status] || 'neutral'" size="sm" dot>
                        {{ order.status_label }}
                    </StatusBadge>
                </div>
                <div class="text-[12px] text-ink-700 truncate mt-0.5">{{ order.customer || '—' }}</div>
            </div>
            <div class="text-right shrink-0">
                <div class="text-[13px] font-semibold tracking-[-0.01em] [font-variant-numeric:tabular-nums] text-ink-900">
                    {{ order.total }}
                </div>
                <div class="text-[11px] text-ink-500">{{ order.placed_at_human }}</div>
            </div>
        </div>
    </div>
</template>
