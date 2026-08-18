<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PageEmpty from '../../PageEmpty.vue';
import TimeSeriesChart, { type ChartPoint } from '../../TimeSeriesChart.vue';

defineProps<{
    data: { points: ChartPoint[]; total: string; hasOrders: boolean };
    range: string;
}>();

const { t } = useI18n();
</script>

<template>
    <div>
        <div class="flex items-baseline gap-2 mb-2">
            <div class="text-[22px] leading-none font-semibold tracking-[-0.02em] [font-variant-numeric:tabular-nums] text-ink-900">
                {{ data.total }}
            </div>
            <div class="text-[11.5px] text-ink-500">{{ t(`dashboard.range_label_${range}`) }}</div>
        </div>

        <PageEmpty v-if="!data.hasOrders" :title="t('dashboard.no_revenue_title')">
            {{ t('dashboard.no_revenue_description') }}
        </PageEmpty>
        <TimeSeriesChart
            v-else
            :points="data.points"
            :height="180"
            :ariaLabel="t('dashboard.widget_revenue_chart_aria')"
        />
    </div>
</template>
