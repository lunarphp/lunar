<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { KpiCard } from '@lunarphp/panel';

type Delta = { value: string; tone: 'neutral' | 'sage' | 'warn' | 'danger' } | null;

defineProps<{
    data: {
        searches: number;
        previous_searches: number;
        conversion_rate: number;
        previous_conversion_rate: number;
        searches_delta: Delta;
        conversion_delta: Delta;
        url: string;
    };
    range: string;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="grid grid-cols-2 gap-2.5">
            <KpiCard :label="t('search-relevance::panel.widget_searches')" :value="data.searches" icon="search" :delta="data.searches_delta" />
            <KpiCard
                :label="t('search-relevance::panel.widget_conversion_rate')"
                :value="`${data.conversion_rate}%`"
                icon="cart"
                tone="sage"
                :delta="data.conversion_delta"
            />
        </div>
        <Link :href="data.url" class="text-[12px] text-ink-700 underline underline-offset-2 hover:text-ink-900 self-start">
            {{ t('search-relevance::panel.widget_view') }}
        </Link>
    </div>
</template>
