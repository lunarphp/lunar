<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PageActions, { type PageAction } from './PageActions.vue';

defineProps<{ title: string; description?: string }>();

// Every page header carries the shared page-action ellipsis, so an add-on can
// always inject a header action without the page opting in. It renders nothing
// when no actions are registered.
const pageActions = computed(() => (usePage().props.pageActions as PageAction[] | undefined) ?? []);
</script>

<template>
    <div class="flex items-start gap-3 sm:gap-4 px-4 sm:px-5 lg:px-7 pt-[18px] pb-3.5 border-b border-line bg-paper">
        <slot name="icon" />

        <div class="flex-1 min-w-0">
            <h1 class="m-0 text-lg sm:text-xl font-semibold tracking-[-0.015em] truncate">{{ title }}</h1>
            <div v-if="$slots.description || description" class="text-xs text-ink-500 mt-[3px] max-w-[640px]">
                <slot name="description">{{ description }}</slot>
            </div>
        </div>

        <div class="hidden sm:flex gap-1.5 shrink-0 items-center">
            <slot name="actions" />
            <PageActions :actions="pageActions" />
        </div>
    </div>
</template>
