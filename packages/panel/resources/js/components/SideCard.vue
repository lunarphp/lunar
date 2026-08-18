<script setup lang="ts">
withDefaults(defineProps<{ title?: string; bodyClass?: string }>(), { bodyClass: 'px-4 py-3.5' });
</script>

<template>
    <div class="bg-surface border border-line rounded-xl shadow-sm mb-4 overflow-hidden">
        <div class="flex items-center gap-2.5 px-4 py-3 border-b border-line">
            <h2 class="m-0 text-[13.5px] font-semibold tracking-[-0.01em]">{{ title }}</h2>
            <template v-if="$slots.actions">
                <div class="flex-1" />
                <slot name="actions" />
            </template>
        </div>
        <div :class="bodyClass">
            <slot />
        </div>
    </div>
</template>
