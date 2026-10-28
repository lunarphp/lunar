<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Icon, StatusBadge } from '@lunarphp/panel';
import type { BundleSummary } from '../types';
import VariantThumb from './VariantThumb.vue';

// A multi-variant product: one row per variant linking to the variant page,
// where the editor lives.
defineProps<{ variants: BundleSummary[] }>();

const { t } = useI18n();
</script>

<template>
    <div>
        <p class="text-[12px] text-ink-500 mb-3">{{ t('bundles::bundles.panel.variant_summary_description') }}</p>
        <ul class="border border-line rounded-lg bg-surface overflow-hidden divide-y divide-line">
            <li v-for="row in variants" :key="row.variant.id" class="flex items-center gap-3 px-3 py-2">
                <VariantThumb :variant="row.variant" class="flex-1" />
                <StatusBadge v-if="row.bundle" tone="sage" size="sm">
                    {{ t('bundles::bundles.panel.variant_component_count', row.bundle.components.length) }}
                </StatusBadge>
                <span v-else class="text-[11.5px] text-ink-500">{{ t('bundles::bundles.panel.variant_not_bundle') }}</span>
                <Link
                    :href="row.variant.edit_url"
                    class="inline-flex items-center gap-1 text-[12px] text-ink-700 underline underline-offset-2 hover:text-ink-900"
                >
                    {{ t('bundles::bundles.panel.edit_variant') }}
                    <Icon name="chevRight" cls="sm" />
                </Link>
            </li>
        </ul>
    </div>
</template>
