<script setup lang="ts">
defineProps<{ required?: boolean; hint?: string }>();
</script>

<template>
    <div class="flex items-center gap-1.5 text-xs font-medium text-ink-700 mb-1.5">
        <slot />
        <span v-if="required" class="text-danger font-semibold">*</span>
        <span v-if="hint" class="ml-auto text-[11px] font-normal text-ink-400">{{ hint }}</span>
    </div>
</template>
