<script setup lang="ts">
// Heading and description above a table, matching the panel's own Section
// component (not on the add-on surface). DataTable draws its own card, so it
// must not be nested inside a SideCard.
withDefaults(defineProps<{ title: string; description?: string; bordered?: boolean }>(), { description: '', bordered: false });
</script>

<template>
    <section :class="['min-w-0', bordered ? 'py-6 border-b border-line first:pt-1 last:border-b-0 last:pb-7' : '']">
        <div class="flex items-start gap-4 mb-4">
            <div class="flex-1 min-w-0">
                <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ title }}</h2>
                <div v-if="description" class="text-xs text-ink-500 leading-normal max-w-[640px]">{{ description }}</div>
            </div>
            <div v-if="$slots.actions" class="flex gap-1.5 shrink-0"><slot name="actions" /></div>
        </div>
        <div class="min-w-0">
            <slot />
        </div>
        <div v-if="$slots.footer" class="mt-2 text-[11px] text-ink-500"><slot name="footer" /></div>
    </section>
</template>
