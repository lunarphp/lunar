<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({ inheritAttrs: false });

const props = defineProps<{
    name: string;
}>();

const attrs = useAttrs();

type SlotEntry = {
    component: string;
    props: Record<string, unknown>;
    priority: number;
};

const resolvedEntries = computed(() => {
    const slots = (usePage().props.slots as Record<string, SlotEntry[]> | undefined) ?? {};
    const entries = [...(slots[props.name] ?? [])].sort((a, b) => a.priority - b.priority);

    return entries
        .map((entry) => ({ entry, component: window.LunarPanel.resolveExtensionComponent(entry.component) }))
        .filter((resolved) => resolved.component);
});
</script>

<template>
    <component
        :is="resolved.component"
        v-for="resolved in resolvedEntries"
        :key="resolved.entry.component"
        v-bind="{ ...resolved.entry.props, ...attrs }"
    />
</template>
