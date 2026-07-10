<script setup lang="ts">
withDefaults(defineProps<{ title?: string }>(), { title: '' });
</script>

<template>
    <div class="px-6 py-10 text-center">
        <div v-if="title" class="text-sm text-ink-900 font-medium mb-1">{{ title }}</div>
        <div class="text-xs text-ink-500"><slot /></div>
    </div>
</template>
