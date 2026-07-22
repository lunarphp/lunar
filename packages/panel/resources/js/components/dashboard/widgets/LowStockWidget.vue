<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import PageEmpty from '../../PageEmpty.vue';

interface VariantRow {
    id: number;
    name: string;
    sku: string | null;
    stock: number;
    url: string | null;
}

defineProps<{ data: { threshold: number; count: number; variants: VariantRow[] } }>();

const { t } = useI18n();

const stockTone = (stock: number): string => {
    if (stock <= 0) {
        return 'text-danger';
    }

    return 'text-warn-ink';
};
</script>

<template>
    <PageEmpty v-if="!data.variants.length" :title="t('dashboard.low_stock_healthy_title')">
        {{ t('dashboard.low_stock_healthy_description') }}
    </PageEmpty>
    <div v-else class="flex flex-col gap-1">
        <component
            :is="variant.url ? 'a' : 'div'"
            v-for="variant in data.variants"
            :key="variant.id"
            :href="variant.url ?? undefined"
            :class="[
                'grid grid-cols-[1fr_auto] items-center gap-3 py-1.5 -mx-1 px-1 rounded-sm',
                variant.url ? 'hover:bg-surface-2' : '',
            ]"
        >
            <div class="min-w-0">
                <div class="text-[12.5px] font-medium text-ink-900 truncate">{{ variant.name }}</div>
                <div v-if="variant.sku" class="text-[11px] text-ink-500 truncate font-mono">{{ variant.sku }}</div>
            </div>
            <div :class="['text-[13px] font-semibold tracking-[-0.01em] [font-variant-numeric:tabular-nums] shrink-0', stockTone(variant.stock)]">
                {{ variant.stock }}
            </div>
        </component>
        <div v-if="data.count > data.variants.length" class="mt-1 pt-2 border-t border-line text-[11.5px] text-ink-500">
            {{ t('dashboard.low_stock_more', { count: data.count - data.variants.length }) }}
        </div>
    </div>
</template>
