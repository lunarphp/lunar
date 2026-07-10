<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from '../components/Icon.vue';
import NavLink from '../components/NavLink.vue';
import { useLang } from '../composables/useLang';
import { useTheme } from '../composables/useTheme';
import type { NavItemShape, NavTreeShape } from '../types/navigation';

const page = usePage();
const t = useLang('nav');
const { theme, cycleTheme } = useTheme();

const panelName = computed(() => (page.props.panel as { name: string }).name);

const navigation = computed<NavTreeShape>(() => (page.props.navigation as NavTreeShape | undefined) ?? { groups: [], items: [] });

// Settings nav is flattened (no menus) since the middleware always requests skipMenus for it.
const settingsItems = computed<NavItemShape[]>(() => {
    const tree = (page.props.settingsNavigation as NavTreeShape | undefined) ?? { groups: [], items: [] };

    return [...tree.items, ...tree.groups.flatMap((group) => group.items)];
});

const collapsed = ref(false);
const mobileOpen = ref(false);

function toggleCollapsed(): void {
    collapsed.value = !collapsed.value;
}

const themeIcon = computed(() => {
    if (theme.value === 'dark') {
        return 'moon';
    }

    if (theme.value === 'light') {
        return 'sun';
    }

    return 'monitor';
});
</script>

<template>
    <div class="min-h-screen bg-canvas font-sans">
        <div v-if="mobileOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="mobileOpen = false" />

        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-line bg-surface transition-transform duration-150 lg:translate-x-0',
                mobileOpen ? 'translate-x-0' : '-translate-x-full',
                collapsed ? 'lg:w-16' : 'lg:w-60',
            ]"
        >
            <div class="flex h-12 shrink-0 items-center gap-2 border-b border-line px-3">
                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-ink-900 text-[11px] font-semibold text-paper">L</span>
                <span v-if="!collapsed" class="truncate text-[13px] font-semibold text-ink-900">{{ panelName }}</span>
            </div>

            <nav class="flex-1 space-y-4 overflow-y-auto px-2 py-3">
                <div v-if="navigation.items.length" class="space-y-0.5">
                    <NavLink v-for="item in navigation.items" :key="item.key" :item="item" :collapsed="collapsed" />
                </div>

                <div v-for="group in navigation.groups" :key="group.key">
                    <div v-if="!collapsed" class="mb-1 px-2 text-[10.5px] font-semibold uppercase tracking-wide text-ink-400">
                        {{ group.label }}
                    </div>
                    <div class="space-y-0.5">
                        <NavLink v-for="item in group.items" :key="item.key" :item="item" :collapsed="collapsed" />
                    </div>
                </div>
            </nav>

            <div v-if="settingsItems.length" class="space-y-0.5 border-t border-line p-2">
                <div v-if="!collapsed" class="mb-1 px-2 text-[10.5px] font-semibold uppercase tracking-wide text-ink-400">
                    {{ t('settings') }}
                </div>
                <NavLink v-for="item in settingsItems" :key="item.key" :item="item" :collapsed="collapsed" />
            </div>

            <div class="border-t border-line p-2">
                <button
                    type="button"
                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-[13px] text-ink-700 hover:bg-surface-2 hover:text-ink-900"
                    :aria-label="t('toggle_sidebar')"
                    @click="toggleCollapsed"
                >
                    <Icon :name="collapsed ? 'chevronRight' : 'chevronLeft'" cls="sm" />
                    <span v-if="!collapsed">{{ t('toggle_sidebar') }}</span>
                </button>
            </div>
        </aside>

        <div :class="['flex min-h-screen flex-col transition-[padding] duration-150', collapsed ? 'lg:pl-16' : 'lg:pl-60']">
            <header class="sticky top-0 z-30 flex h-12 items-center gap-2 border-b border-line bg-surface px-3">
                <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-surface-2 lg:hidden"
                    :aria-label="t('toggle_sidebar')"
                    @click="mobileOpen = !mobileOpen"
                >
                    <Icon name="menu" cls="sm" />
                </button>

                <span class="text-[13px] font-medium text-ink-900 lg:hidden">{{ panelName }}</span>

                <div class="ml-auto flex items-center gap-1">
                    <button
                        type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-ink-700 hover:bg-surface-2"
                        :aria-label="t('toggle_theme')"
                        @click="cycleTheme"
                    >
                        <Icon :name="themeIcon" cls="sm" />
                    </button>
                </div>
            </header>

            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>
