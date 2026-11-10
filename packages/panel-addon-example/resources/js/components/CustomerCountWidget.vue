<script setup lang="ts">
import { useI18n } from 'vue-i18n';

defineProps<{
    data: { total: number; recent: number };
    range: string;
}>();

const { t } = useI18n();
</script>

<template>
    <div class="example:flex example:items-end example:gap-6">
        <div>
            <div class="example:text-[26px] example:leading-none example:font-semibold example:tracking-[-0.02em] example:[font-variant-numeric:tabular-nums] example:text-ink-900">
                {{ data.total }}
            </div>
            <div class="example:text-[11px] example:text-ink-500 example:mt-1">{{ t('example-addon::example.widget_total') }}</div>
        </div>
        <div>
            <div class="example:text-[26px] example:leading-none example:font-semibold example:tracking-[-0.02em] example:[font-variant-numeric:tabular-nums] example:text-ink-900">
                {{ data.recent }}
            </div>
            <div class="example:text-[11px] example:text-ink-500 example:mt-1">{{ t('example-addon::example.widget_recent') }}</div>
        </div>
    </div>
</template>
