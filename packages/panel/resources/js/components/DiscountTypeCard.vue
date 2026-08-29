<script setup lang="ts">
defineProps<{
    label: string;
    description?: string;
    selected: boolean;
}>();

defineEmits<{ select: [] }>();
</script>

<template>
    <button
        type="button"
        class="text-left rounded-lg border p-3 transition focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35"
        :class="selected
            ? 'border-sage bg-sage/5 ring-1 ring-sage/40'
            : 'border-line bg-surface hover:border-ink-300'"
        :aria-pressed="selected"
        @click="$emit('select')"
    >
        <div class="text-[12.5px] font-medium text-ink-900">{{ label }}</div>
        <div v-if="description" class="mt-0.5 text-[11.5px] text-ink-500 leading-normal">{{ description }}</div>
    </button>
</template>
