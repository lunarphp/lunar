<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Icon from '../../Icon.vue';
import PageEmpty from '../../PageEmpty.vue';

interface ProductRow {
    name: string;
    sku: string | null;
    thumbnail: string | null;
    units: number;
    revenue: string;
    url: string | null;
}

defineProps<{ data: { products: ProductRow[] } }>();

const { t } = useI18n();
</script>

<template>
    <PageEmpty v-if="!data.products.length" :title="t('dashboard.no_products_title')">
        {{ t('dashboard.no_products_description') }}
    </PageEmpty>
    <div v-else class="flex flex-col gap-1">
        <component
            :is="product.url ? 'a' : 'div'"
            v-for="product in data.products"
            :key="product.sku ?? product.name"
            :href="product.url ?? undefined"
            :class="[
                'grid grid-cols-[auto_1fr_auto] items-center gap-3 py-1.5 -mx-1 px-1 rounded-sm',
                product.url ? 'hover:bg-surface-2' : '',
            ]"
        >
            <div class="w-8 h-8 rounded-md bg-surface-2 border border-line overflow-hidden grid place-items-center text-ink-400 shrink-0">
                <img v-if="product.thumbnail" :src="product.thumbnail" alt="" class="w-full h-full object-cover" />
                <Icon v-else name="image" cls="sm" />
            </div>
            <div class="min-w-0">
                <div class="text-[12.5px] font-medium text-ink-900 truncate">{{ product.name }}</div>
                <div class="text-[11px] text-ink-500 truncate">
                    <span v-if="product.sku" class="font-mono">{{ product.sku }}</span>
                    <span v-if="product.sku"> · </span>{{ t('dashboard.units_sold', { count: product.units }) }}
                </div>
            </div>
            <div class="text-[13px] font-semibold tracking-[-0.01em] [font-variant-numeric:tabular-nums] text-ink-900 shrink-0">
                {{ product.revenue }}
            </div>
        </component>
    </div>
</template>
