<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { SideCard, http } from '@lunarphp/panel';
import { panelPath, type VariantRow } from '../types';

type IncludedIn = {
    id: number;
    variant: VariantRow;
    product_url: string;
    components: { quantity: number; variant: VariantRow }[];
};

// Sidebar card on the product edit page listing every bundle that includes
// one of this product's variants. Renders nothing when there are none.
const props = defineProps<{ product?: { id: number } }>();

const { t } = useI18n();

const bundles = ref<IncludedIn[]>([]);

const path = panelPath(usePage().props as Record<string, unknown>);

onMounted(async () => {
    if (!props.product) {
        return;
    }

    try {
        const payload = await http.get<{ data: IncludedIn[] }>(`/${path}/bundles/products/${props.product.id}/included-in`);
        bundles.value = payload.data;
    } catch {
        bundles.value = [];
    }
});
</script>

<template>
    <SideCard v-if="bundles.length" :title="t('bundles::bundles.panel.included_in_title')">
        <ul class="flex flex-col gap-2.5">
            <li v-for="bundle in bundles" :key="bundle.id">
                <Link :href="bundle.product_url" class="block text-[12.5px] font-medium text-ink-900 hover:underline underline-offset-2 truncate">
                    {{ bundle.variant.name }}<span v-if="bundle.variant.option" class="text-ink-500 font-normal"> ({{ bundle.variant.option }})</span>
                </Link>
                <ul class="mt-0.5 text-[11.5px] text-ink-500">
                    <li v-for="component in bundle.components" :key="component.variant.id" class="truncate">
                        {{ t('bundles::bundles.panel.included_quantity', { quantity: component.quantity }) }}
                        <span v-if="component.variant.sku" class="font-mono">{{ component.variant.sku }}</span>
                        <span v-if="component.variant.option"> {{ component.variant.option }}</span>
                    </li>
                </ul>
            </li>
        </ul>
        <p class="mt-3 text-[11px] text-ink-500">{{ t('bundles::bundles.panel.included_in_description') }}</p>
    </SideCard>
</template>
