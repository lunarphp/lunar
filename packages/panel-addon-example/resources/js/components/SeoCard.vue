<script setup lang="ts">
import { computed, ref } from 'vue';

// The canonical slot example (spec 0049/0057): an SEO card injected into the
// product edit page's `products.edit:content:after` zone. The zone passes the
// page's product as a prop; edits here are demonstration-local — a real SEO
// add-on would persist through its own registered routes.
const props = defineProps<{
    product?: { id: number; display_name?: string };
}>();

const title = ref(props.product?.display_name ?? '');
const description = ref('');

const previewTitle = computed(() => title.value || props.product?.display_name || 'Page title');
const previewDescription = computed(() => description.value || 'A meta description preview appears here as you type.');
</script>

<template>
    <div style="margin: 1.5rem 0; padding: 1rem; border: 1px solid; border-radius: 8px">
        <h2 style="margin: 0 0 0.25rem; font-size: 0.875rem; font-weight: 600">Search engine listing</h2>
        <p style="margin: 0 0 0.75rem; font-size: 0.75rem; opacity: 0.7">
            Injected by the example add-on via the <code>products.edit:content:after</code> slot zone.
        </p>

        <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem" for="example-seo-title">SEO title</label>
        <input
            id="example-seo-title"
            v-model="title"
            style="display: block; width: 100%; margin-bottom: 0.5rem; padding: 0.375rem 0.5rem; border: 1px solid; border-radius: 6px; font: inherit; font-size: 0.8125rem"
        >

        <label style="display: block; font-size: 0.75rem; margin-bottom: 0.25rem" for="example-seo-description">Meta description</label>
        <textarea
            id="example-seo-description"
            v-model="description"
            rows="2"
            style="display: block; width: 100%; margin-bottom: 0.75rem; padding: 0.375rem 0.5rem; border: 1px solid; border-radius: 6px; font: inherit; font-size: 0.8125rem; resize: vertical"
        />

        <div style="padding: 0.625rem 0.75rem; border: 1px dashed; border-radius: 6px">
            <div style="font-size: 0.875rem; color: #1a0dab">{{ previewTitle }}</div>
            <div style="font-size: 0.6875rem; opacity: 0.6">shop.example / products / {{ product?.id ?? '…' }}</div>
            <div style="font-size: 0.75rem; opacity: 0.85">{{ previewDescription }}</div>
        </div>
    </div>
</template>
