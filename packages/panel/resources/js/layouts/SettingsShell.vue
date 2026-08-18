<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { TooltipProvider, DialogContent, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, VisuallyHidden } from 'reka-ui';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import SettingsNavBody from '../components/SettingsNavBody.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../components/Breadcrumbs.vue';
import Icon from '../components/Icon.vue';
import PageActions, { type PageAction } from '../components/PageActions.vue';
import PageHeader from '../components/PageHeader.vue';
import PageZone from '../components/PageZone.vue';
import Toaster from '../components/Toaster.vue';
import { useNavState } from '../composables/useNavState';

// Settings pages get the same scaffold as top-level pages: a breadcrumb bar
// ("Settings > {page}"; `breadcrumbs` overrides the trail for nested pages
// like "Settings > Channels > Web store") and a PageHeader carrying the
// title, an optional description, the page's own #actions, and the shared
// page-action ellipsis. Content is a centered column: max-w-5xl for forms,
// or the same max-w-[1400px] as top-level listing pages with `wide`.
const props = defineProps<{
    title?: string;
    description?: string;
    icon?: string;
    breadcrumbs?: BreadcrumbItem[];
    wide?: boolean;
}>();

const { state, toggleCollapsed, openDrawer } = useNavState();
const { t } = useI18n();

const crumbs = computed<BreadcrumbItem[]>(() => {
    if (props.breadcrumbs) {
        return props.breadcrumbs;
    }

    return [
        { label: t('nav.settings'), current: !props.title },
        ...(props.title ? [{ label: props.title, current: true }] : []),
    ];
});

const panelName = computed(() => (usePage().props.panel as { name: string }).name);
const pageActions = computed(() => (usePage().props.pageActions as PageAction[] | undefined) ?? []);

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
            <aside
                v-if="isDesktop"
                :class="[
                    'bg-paper border-r border-line flex flex-col gap-0.5 overflow-y-auto overflow-x-hidden transition-[width] duration-200 sticky top-0 h-screen',
                    state.collapsed ? 'py-2.5 px-1.5' : 'p-2.5',
                ]"
            >
                <SettingsNavBody :collapsed="effectivelyCollapsed" />
            </aside>

            <DialogRoot v-else v-model:open="state.drawerOpen">
                <DialogPortal>
                    <DialogOverlay
                        class="fixed inset-0 bg-ink-900/40 z-40 lg:hidden data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
                    />
                    <DialogContent
                        class="bg-paper border-r border-line flex flex-col gap-0.5 overflow-y-auto overflow-x-hidden fixed inset-y-0 left-0 z-50 w-[260px] p-2.5 lg:hidden focus:outline-none transition-transform duration-200 data-[state=closed]:-translate-x-full data-[state=open]:translate-x-0"
                    >
                        <VisuallyHidden>
                            <DialogTitle>Settings navigation</DialogTitle>
                        </VisuallyHidden>
                        <SettingsNavBody :collapsed="false" />
                    </DialogContent>
                </DialogPortal>
            </DialogRoot>

            <button
                :class="[
                    'fixed top-3.5 w-[22px] h-[22px] rounded-full bg-paper border border-line-strong shadow-sm place-items-center text-ink-500 z-40 transition-[background-color,color,border-color,left] duration-200 hidden lg:grid',
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

                <Breadcrumbs :items="crumbs">
                    <!-- PageHeader is the ellipsis's home; without a title (no
                         header) it falls back to the breadcrumb bar so add-on
                         page actions always have somewhere to render. -->
                    <template v-if="!title" #actions>
                        <slot name="actions" />
                        <PageActions :actions="pageActions" />
                    </template>
                </Breadcrumbs>

                <!-- PageHeader owns the browser-tab <Head> title; pages without
                     one fall back to the panel name via app.ts. -->
                <PageHeader v-if="title" :title="title" :description="description" :icon="icon">
                    <template #actions>
                        <slot name="actions" />
                    </template>
                </PageHeader>

                <div :class="wide ? 'px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7' : 'mx-auto w-full max-w-5xl px-6 pt-5 pb-7'">
                    <PageZone region="main" position="before" />

                    <slot />

                    <PageZone region="main" position="after" />
                </div>
            </main>
        </div>

        <Toaster />
    </TooltipProvider>
</template>
