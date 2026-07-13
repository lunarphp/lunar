<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PanelSlot from './PanelSlot.vue';

// A slot zone scoped to the current page. `region`/`position` are combined with
// the page id shared by the server into the standard zone name
// `{pageId}:{region}[:position]`, so pages declare zones without repeating their id.
const props = defineProps<{ region: string; position?: string }>();

const pageId = computed(() => (usePage().props.pageId as string | undefined) ?? '');

const name = computed(() => {
    const base = `${pageId.value}:${props.region}`;

    return props.position ? `${base}:${props.position}` : base;
});
</script>

<template>
    <PanelSlot :name="name" v-bind="$attrs" />
</template>
