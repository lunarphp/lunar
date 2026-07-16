<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';
import PageActions, { type PageAction } from './PageActions.vue';

// `icon` renders the standard header tile from the built-in icon set; the
// #icon slot overrides it for custom markup (avatars, images, initials).
defineProps<{ title: string; description?: string; icon?: string }>();

// Every page header carries the shared page-action ellipsis, so an add-on can
// always inject a header action without the page opting in. It renders nothing
// when no actions are registered.
const pageActions = computed(() => (usePage().props.pageActions as PageAction[] | undefined) ?? []);
</script>

<template>
    <div class="flex items-start gap-3 sm:gap-4 px-4 sm:px-5 lg:px-7 pt-[18px] pb-3.5 border-b border-line bg-paper">
        <!-- The scaffold owns the browser-tab title, so every page that renders a
             header gets one for free; app.ts suffixes the panel name. -->
        <Head :title="title" />

        <slot name="icon">
            <div v-if="icon" class="w-11 h-11 rounded-md overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700">
                <Icon :name="icon" />
            </div>
        </slot>

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
