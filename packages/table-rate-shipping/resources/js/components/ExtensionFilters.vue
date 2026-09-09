<script setup lang="ts">
// The add-on filter toolbar every index page renders: one dropdown per
// TableFilter another add-on registered against this table, round-tripping as
// nested filter[{key}] query params exactly as first-party settings pages do.
import { computed, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { FilterDropdown } from '@lunarphp/panel';
import type { ExtensionFilter } from '../lib/types';

const props = defineProps<{
    filters: ExtensionFilter[];
    values: Record<string, string>;
    indexUrl: string;
}>();

const { t } = useI18n();

const values = reactive<Record<string, string>>({ ...props.values });

const renderable = computed(() => props.filters.filter((filter) => Object.keys(filter.options).length > 0));

const optionsFor = (filter: ExtensionFilter) => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];

watch(values, () => {
    const active = Object.fromEntries(Object.entries(values).filter(([, value]) => value !== ''));

    router.get(props.indexUrl, { filter: Object.keys(active).length ? active : undefined }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});
</script>

<template>
    <div v-if="renderable.length" class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
        <FilterDropdown
            v-for="filter in renderable"
            :key="filter.key"
            v-model="values[filter.key]"
            :label="filter.label"
            :options="optionsFor(filter)"
            default-value=""
        />
    </div>
</template>
