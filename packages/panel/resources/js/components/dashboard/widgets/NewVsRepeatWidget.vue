<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import DonutChart, { type DonutSegment } from '../../DonutChart.vue';
import PageEmpty from '../../PageEmpty.vue';

defineProps<{
    data: {
        segments: DonutSegment[];
        counts: { new: number; repeat: number };
        total: string;
        totalExact: string;
    };
}>();

const { t } = useI18n();
</script>

<template>
    <PageEmpty
        v-if="!data.segments.some((segment) => segment.value > 0)"
        :title="t('dashboard.no_orders_title')"
    >
        {{ t('dashboard.no_orders_description') }}
    </PageEmpty>
    <div v-else>
        <DonutChart
            :segments="data.segments"
            :centre-value="data.total"
            :centre-title="data.totalExact"
            :centre-label="t('dashboard.total')"
            :ariaLabel="t('dashboard.widget_new_vs_repeat_label')"
        />
        <div class="mt-3 pt-3 border-t border-line text-[11.5px] text-ink-500">
            {{ t('dashboard.new_vs_repeat_counts', { new: data.counts.new, repeat: data.counts.repeat }) }}
        </div>
    </div>
</template>
