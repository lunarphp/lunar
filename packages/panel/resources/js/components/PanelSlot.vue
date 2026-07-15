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
    <!-- Index keys: the same component can appear twice in one zone (same banner,
         different props), so the component name is not a unique key. The list is
         fixed per page load, so index keys are stable. -->
    <component
        :is="resolved.component"
        v-for="(resolved, index) in resolvedEntries"
        :key="index"
        v-bind="{ ...resolved.entry.props, ...attrs }"
    />
</template>
