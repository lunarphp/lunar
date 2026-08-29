<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { TooltipProvider, DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, VisuallyHidden } from 'reka-ui';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import NavBody from '../components/NavBody.vue';
import CommandPalette from '../components/CommandPalette.vue';
import Icon from '../components/Icon.vue';
import Toaster from '../components/Toaster.vue';
import { useNavState } from '../composables/useNavState';
import { useRecentRecords } from '../composables/useRecentRecords';
import type { SearchResult } from '../types/search';

const { state, toggleCollapsed, openDrawer } = useNavState();
const { t } = useI18n();

const page = usePage();

const panelName = computed(() => (page.props.panel as { name: string }).name);

const userId = computed(() => (page.props.auth as { user: { id: string | number } | null }).user?.id ?? 'guest');

const { remember } = useRecentRecords(userId.value);

// Record pages share the record they are showing; the palette offers the last
// few back as its empty state.
watch(
    () => page.props.visitedRecord as SearchResult | null | undefined,
    (record) => {
        if (record) {
            remember(record);
        }
    },
    { immediate: true },
);

const isDesktop = ref(typeof window !== 'undefined' && window.matchMedia ? window.matchMedia('(min-width: 1024px)').matches : true);

const effectivelyCollapsed = computed(() => isDesktop.value && state.collapsed);

let mql: MediaQueryList | null = null;

function onMqlChange(e: MediaQueryListEvent): void {
    isDesktop.value = e.matches;

    if (e.matches) {
        state.drawerOpen = false;
    }
}

function onKeydown(e: KeyboardEvent): void {
    if ((e.metaKey || e.ctrlKey) && e.key === '\\') {
        e.preventDefault();
        toggleCollapsed();
    }
}

onMounted(() => {
    window.addEventListener('keydown', onKeydown);

    if (typeof window === 'undefined' || !window.matchMedia) {
        return;
    }

    mql = window.matchMedia('(min-width: 1024px)');
    isDesktop.value = mql.matches;

    if (mql.addEventListener) {
        mql.addEventListener('change', onMqlChange);
    } else {
        mql.addListener(onMqlChange);
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);

    if (!mql) {
        return;
    }

    if (mql.removeEventListener) {
        mql.removeEventListener('change', onMqlChange);
    } else {
        mql.removeListener(onMqlChange);
    }
});
</script>

<template>
    <TooltipProvider :delay-duration="350">
        <div
            :class="[
                'min-h-screen lg:grid',
                state.collapsed ? 'lg:grid-cols-[56px_1fr]' : 'lg:grid-cols-[232px_1fr]',
            ]"
        >
            <!-- Desktop: always-visible sticky sidebar -->
            <aside
                v-if="isDesktop"
                :class="[
                    'bg-paper border-r border-line flex flex-col gap-0.5 overflow-y-auto overflow-x-hidden transition-[width] duration-200 sticky top-0 h-screen',
                    state.collapsed ? 'py-2.5 px-1.5' : 'p-2.5',
                ]"
            >
                <NavBody :collapsed="effectivelyCollapsed" />
            </aside>

            <!-- Mobile: dialog-backed drawer -->
            <DialogRoot v-else v-model:open="state.drawerOpen">
                <DialogPortal>
                    <DialogOverlay
                        class="fixed inset-0 bg-ink-900/40 z-40 lg:hidden data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
                    />
                    <DialogContent
                        class="bg-paper border-r border-line flex flex-col gap-0.5 overflow-y-auto overflow-x-hidden fixed inset-y-0 left-0 z-50 w-[260px] p-2.5 lg:hidden focus:outline-none transition-transform duration-200 data-[state=closed]:-translate-x-full data-[state=open]:translate-x-0"
                    >
                        <VisuallyHidden>
                            <DialogTitle>Navigation</DialogTitle>
                        </VisuallyHidden>
                        <NavBody :collapsed="false" />
                    </DialogContent>
                </DialogPortal>
            </DialogRoot>

            <button
                :class="[
                    'fixed top-3.5 w-[22px] h-[22px] rounded-full bg-paper border border-line-strong shadow-sm place-items-center text-ink-500 z-40 transition-[background-color,color,border-color,left] duration-200',
                    'hidden lg:grid',
                    'hover:bg-surface-2 hover:text-ink-900 hover:border-ink-300',
                    'focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35 focus-visible:border-sage',
                    state.collapsed ? 'left-[calc(56px-11px)]' : 'left-[calc(232px-11px)]',
                ]"
                :aria-label="state.collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                :title="state.collapsed ? 'Expand sidebar  ⌘\\' : 'Collapse sidebar  ⌘\\'"
                @click="toggleCollapsed"
            >
                <svg
                    class="w-3 h-3 transition-transform duration-200"
                    :class="{ 'rotate-180': state.collapsed }"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <polyline points="15 6 9 12 15 18" />
                </svg>
            </button>

            <main class="flex flex-col min-w-0">
                <div class="sticky top-0 z-30 flex items-center gap-2 px-4 py-2.5 bg-paper/75 backdrop-saturate-[1.6] backdrop-blur-md border-b border-line lg:hidden">
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-surface-2 shrink-0"
                        :aria-label="t('nav.toggle_sidebar')"
                        @click="openDrawer"
                    >
                        <Icon name="menu" cls="sm" />
                    </button>
                    <span class="text-[13px] font-medium text-ink-900 truncate">{{ panelName }}</span>
                </div>

                <slot />
            </main>
        </div>

        <CommandPalette />

        <Toaster />
    </TooltipProvider>
</template>
