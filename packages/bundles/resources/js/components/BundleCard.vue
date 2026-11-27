<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
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

// Only the newest request may write: an older one resolving late would
// bring back variants the page no longer has.
let latestRequest = 0;

const load = async (): Promise<void> => {
    const request = ++latestRequest;

    try {
        let loaded: BundleSummary[] | null = null;

        if (props.variant) {
            loaded = [await http.get<BundleSummary>(`/${path}/bundles/variants/${props.variant.id}`)];
        } else if (props.product) {
            const payload = await http.get<{ variants: BundleSummary[] }>(`/${path}/bundles/products/${props.product.id}`);
            loaded = payload.variants;
        }

        if (request === latestRequest) {
            summaries.value = loaded;
            failed.value = false;
        }
    } catch {
        if (request === latestRequest) {
            failed.value = true;
        }
    }
};

onMounted(load);

// Inertia keeps the page mounted across visits back to it, so a change made
// elsewhere on the page (collapsing to a simple product deletes the other
// variants, regenerating options replaces them) arrives as a fresh `product`
// or `variant` prop without remounting this card. Reload on each fresh prop;
// a partial reload that leaves the prop untouched does not refetch.
watch([() => props.product, () => props.variant], () => {
    void load();
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
