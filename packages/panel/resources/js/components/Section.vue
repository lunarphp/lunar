<script setup lang="ts">
withDefaults(defineProps<{ title?: string; className?: string }>(), { className: '' });
</script>

<template>
    <section :class="['py-6 border-b border-line first:pt-1 last:border-b-0 last:pb-7', className]">
        <div class="flex items-start gap-4 mb-4">
            <div class="flex-1 min-w-0">
                <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ title }}</h2>
                <div v-if="$slots.desc" class="text-xs text-ink-500 leading-normal max-w-[640px]"><slot name="desc" /></div>
            </div>
            <div v-if="$slots.actions" class="flex gap-1.5 shrink-0"><slot name="actions" /></div>
        </div>
        <div class="min-w-0">
            <slot />
        </div>
    </section>
</template>
