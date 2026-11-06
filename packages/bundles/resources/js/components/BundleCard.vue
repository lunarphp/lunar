<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Section, http } from '@lunarphp/panel';
import { panelPath, type BundleSummary } from '../types';
import BundleEditor from './BundleEditor.vue';
import VariantBundleList from './VariantBundleList.vue';

// Injected into `products.edit:variants:after` (prop `product`) and
// `products.variants.edit:main:after` (prop `variant`). Nothing is fetched
// until mount, so ordinary product pages pay one request and no render.
const props = defineProps<{
    product?: { id: number };
    variant?: { id: number };
}>();

const { t } = useI18n();

const summaries = ref<BundleSummary[] | null>(null);
const failed = ref(false);

const path = panelPath(usePage().props as Record<string, unknown>);

onMounted(async () => {
    try {
        if (props.variant) {
            summaries.value = [await http.get<BundleSummary>(`/${path}/bundles/variants/${props.variant.id}`)];
        } else if (props.product) {
            const payload = await http.get<{ variants: BundleSummary[] }>(`/${path}/bundles/products/${props.product.id}`);
            summaries.value = payload.variants;
        }
    } catch {
        failed.value = true;
    }
});
</script>

<template>
    <Section v-if="summaries || failed" :title="t('bundles::bundles.panel.card_title')">
        <template #desc>{{ t('bundles::bundles.panel.card_description') }}</template>

        <p v-if="failed" class="text-[12px] text-danger">{{ t('bundles::bundles.panel.load_failed') }}</p>
        <BundleEditor v-else-if="summaries && summaries.length === 1" :summary="summaries[0]" />
        <VariantBundleList v-else-if="summaries" :variants="summaries" />
    </Section>
</template>
