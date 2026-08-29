<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import NavLink from './NavLink.vue';
import Tooltip from './Tooltip.vue';
import UserMenu from './UserMenu.vue';
import { useCommandPalette } from '../composables/useCommandPalette';
import type { NavItemShape, NavTreeShape } from '../types/navigation';

withDefaults(defineProps<{ collapsed?: boolean }>(), { collapsed: false });

const page = usePage();
const { t } = useI18n();

type PanelInfo = { name: string; storefront_url: string | null; support_url: string | null };

const panel = computed<PanelInfo>(() => page.props.panel as PanelInfo);

const navigation = computed<NavTreeShape>(() => (page.props.navigation as NavTreeShape | undefined) ?? { groups: [], items: [] });

const settingsTree = computed<NavTreeShape>(
    () => (page.props.settingsNavigation as NavTreeShape | undefined) ?? { groups: [], items: [] },
);

const settingsUrl = computed<string | null>(
    () => settingsTree.value.groups[0]?.items[0]?.url ?? settingsTree.value.items[0]?.url ?? null,
);

const settingsItem = computed<NavItemShape>(() => ({
    key: 'settings',
    label: t('nav.settings'),
    icon: 'settings',
    url: settingsUrl.value,
    priority: 0,
    badge: null,
    exact: false,
    children: [],
}));

const logoLetter = computed(() => panel.value.name?.charAt(0).toUpperCase() ?? 'L');

const { openPalette } = useCommandPalette();

// The Command keycap glyph plus K, mirroring the palette's own binding.
const SEARCH_SHORTCUT = '⌘K';

const footerItemBase = 'flex items-center gap-2.5 rounded-sm text-[13px] text-ink-700 cursor-pointer select-none hover:bg-surface-2';
</script>

<template>
    <div
        :class="[
            'flex items-center gap-2.5 pt-2 pb-2.5 rounded-md relative',
            collapsed ? 'justify-center px-0.5' : 'px-2',
        ]"
    >
        <div class="w-7 h-7 bg-ink-900 text-paper rounded-[7px] grid place-items-center font-bold text-[13px] tracking-[-0.02em]">
            {{ logoLetter }}
        </div>
        <div v-if="!collapsed" class="min-w-0">
            <div class="font-semibold text-[13px] tracking-[-0.01em] truncate">{{ panel.name }}</div>
            <a
                v-if="panel.storefront_url"
                :href="panel.storefront_url"
                target="_blank"
                rel="noopener"
                class="text-[11px] text-ink-500 flex items-center gap-[3px] hover:text-ink-900"
            >
                {{ t('nav.visit_store') }} <Icon name="externalLink" cls="sm" />
            </a>
        </div>
    </div>

    <Tooltip :text="collapsed ? `${t('nav.search')} (${SEARCH_SHORTCUT})` : ''">
        <button
            type="button"
            :aria-label="t('nav.search')"
            :class="[
                'flex items-center gap-2 bg-surface-2 border border-line rounded-md text-ink-500 text-xs cursor-pointer transition-colors hover:bg-paper-hover hover:text-ink-700 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35',
                collapsed ? 'w-auto justify-center p-2 my-1.5 mx-0' : 'w-[calc(100%-0.5rem)] px-2.5 py-1.5 mx-1 mt-1.5 mb-2.5',
            ]"
            @click="openPalette"
        >
            <Icon name="search" cls="sm" />
            <template v-if="!collapsed">
                <span>{{ t('nav.search') }}</span>
                <kbd class="ml-auto font-mono text-[10px] text-ink-400 bg-paper border border-line rounded px-1 py-px">{{ SEARCH_SHORTCUT }}</kbd>
            </template>
        </button>
    </Tooltip>

    <div v-if="navigation.items.length" class="mt-1">
        <NavLink v-for="item in navigation.items" :key="item.key" :item="item" :collapsed="collapsed" />
    </div>

    <template v-for="group in navigation.groups" :key="group.key">
        <div
            v-if="!collapsed"
            class="text-[10px] uppercase tracking-[0.08em] text-ink-400 px-2.5 pt-3.5 pb-1.5 font-medium"
        >{{ group.label }}</div>
        <div
            v-else
            class="relative h-3 px-3 my-1 before:content-[''] before:absolute before:left-3 before:right-3 before:top-1/2 before:h-px before:bg-line"
        />
        <NavLink v-for="item in group.items" :key="item.key" :item="item" :collapsed="collapsed" />
    </template>

    <div class="flex-1" />

    <NavLink :item="settingsItem" :collapsed="collapsed" />

    <Tooltip :text="collapsed ? t('nav.support') : ''">
        <a
            v-if="panel.support_url"
            :href="panel.support_url"
            target="_blank"
            rel="noopener"
            :class="[footerItemBase, collapsed ? 'justify-center py-2 px-0 gap-0' : 'px-2.5 py-1.5']"
        >
            <Icon name="help" /> <span v-if="!collapsed">{{ t('nav.support') }}</span>
        </a>
        <div v-else :class="[footerItemBase, collapsed ? 'justify-center py-2 px-0 gap-0' : 'px-2.5 py-1.5']">
            <Icon name="help" /> <span v-if="!collapsed">{{ t('nav.support') }}</span>
        </div>
    </Tooltip>

    <UserMenu :collapsed="collapsed" />
</template>
