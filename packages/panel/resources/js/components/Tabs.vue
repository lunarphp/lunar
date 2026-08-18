<script setup lang="ts">
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';

defineProps<{
    modelValue: string;
    tabs: Array<{ value: string; label: string; count?: number | null }>;
}>();

defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<template>
    <TabsRoot
        :model-value="modelValue"
        @update:model-value="$emit('update:modelValue', $event)"
        class="flex flex-col"
    >
        <div class="flex items-end gap-2 border-b border-line">
            <TabsList class="flex flex-1 min-w-0 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <TabsTrigger
                    v-for="t in tabs"
                    :key="t.value"
                    :value="t.value"
                    class="relative inline-flex items-center gap-1.5 h-[38px] px-3 text-[12.5px] font-medium text-ink-500 hover:text-ink-900 data-[state=active]:text-ink-900 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35 transition-colors after:absolute after:left-2 after:right-2 after:bottom-[-1px] after:h-[2px] after:rounded-full after:bg-ink-900 after:opacity-0 data-[state=active]:after:opacity-100"
                >
                    <span>{{ t.label }}</span>
                    <span
                        v-if="t.count != null"
                        class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-surface-2 border border-line text-[10.5px] text-ink-700 [font-variant-numeric:tabular-nums]"
                    >{{ t.count }}</span>
                </TabsTrigger>
            </TabsList>
            <div v-if="$slots.actions" class="pb-2 pl-2 shrink-0 flex gap-1.5">
                <slot name="actions" />
            </div>
        </div>
        <TabsContent
            v-for="t in tabs"
            :key="t.value"
            :value="t.value"
            class="pt-5 focus-visible:outline-none"
        >
            <slot :name="t.value" />
        </TabsContent>
    </TabsRoot>
</template>
