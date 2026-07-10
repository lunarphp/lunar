<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{ title?: string }>();

type NavItem = { key: string; label: string; url: string | null };
type NavGroup = { key: string; label: string; items: NavItem[] };

const settingsNavigation = computed(
    () => (usePage().props.settingsNavigation as { groups?: NavGroup[] } | undefined)?.groups ?? [],
);

const flashSuccess = computed(() => (usePage().props.flash as { success?: string })?.success);
const flashError = computed(() => (usePage().props.flash as { error?: string })?.error);

const isCurrent = (url: string | null) => !!url && window.location.pathname === new URL(url, window.location.origin).pathname;
</script>

<template>
    <div class="min-h-screen bg-canvas font-sans">
        <div class="mx-auto flex max-w-5xl gap-8 px-6 py-10">
            <aside class="w-48 shrink-0">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-ink-400 px-2 mb-2">Settings</div>
                <nav class="flex flex-col gap-4">
                    <div v-for="group in settingsNavigation" :key="group.key">
                        <div class="px-2 text-[11px] font-medium text-ink-400 mb-1">{{ group.label }}</div>
                        <div class="flex flex-col">
                            <Link
                                v-for="item in group.items"
                                :key="item.key"
                                :href="item.url ?? '#'"
                                class="rounded-md px-2 py-1.5 text-[13px] font-medium transition-colors"
                                :class="isCurrent(item.url) ? 'bg-surface-2 text-ink-900' : 'text-ink-600 hover:bg-surface-2 hover:text-ink-900'"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>
                </nav>
            </aside>

            <div class="flex-1 min-w-0">
                <h1 v-if="title" class="text-xl font-semibold tracking-[-0.02em] text-ink-900 mb-5">{{ title }}</h1>

                <div v-if="flashSuccess" class="mb-4 rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink">
                    {{ flashSuccess }}
                </div>
                <div v-if="flashError" class="mb-4 rounded-md border border-danger bg-danger/10 px-3 py-2 text-[12px] text-danger">
                    {{ flashError }}
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
