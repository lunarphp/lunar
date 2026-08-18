<script setup lang="ts">
import { useI18n } from 'vue-i18n';

defineProps<{
    data: { total: number; recent: number };
    range: string;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="flex items-end gap-6">
        <div>
            <div class="text-[26px] leading-none font-semibold tracking-[-0.02em] [font-variant-numeric:tabular-nums] text-ink-900">
                {{ data.total }}
            </div>
            <div class="text-[11px] text-ink-500 mt-1">{{ t('example-addon::example.widget_total') }}</div>
        </div>
        <div>
            <div class="text-[26px] leading-none font-semibold tracking-[-0.02em] [font-variant-numeric:tabular-nums] text-ink-900">
                {{ data.recent }}
            </div>
            <div class="text-[11px] text-ink-500 mt-1">{{ t('example-addon::example.widget_recent') }}</div>
        </div>
    </div>
</template>
