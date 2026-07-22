<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import DonutChart, { type DonutSegment } from '../../DonutChart.vue';
import PageEmpty from '../../PageEmpty.vue';

defineProps<{ data: { segments: DonutSegment[]; total: string; totalExact: string } }>();

const { t } = useI18n();
</script>

<template>
    <PageEmpty v-if="!data.segments.length" :title="t('dashboard.no_orders_title')">
        {{ t('dashboard.no_orders_description') }}
    </PageEmpty>
    <DonutChart
        v-else
        :segments="data.segments"
        :centre-value="data.total"
        :centre-title="data.totalExact"
        :centre-label="t('dashboard.total')"
        :other-label="t('dashboard.segment_other')"
        :ariaLabel="t('dashboard.widget_customer_groups_label')"
    />
</template>
