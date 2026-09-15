<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { StatusBadge } from '@lunarphp/panel';

// Products table cell for the add-on's `is_bundle` column: a badge on
// products with a bundle variant, nothing on the rest.
defineProps<{ row: Record<string, unknown>; value: unknown }>();

const { t } = useI18n();
</script>

<template>
    <StatusBadge v-if="value" tone="sage" size="sm">{{ t('bundles::bundles.panel.badge_bundle') }}</StatusBadge>
</template>
