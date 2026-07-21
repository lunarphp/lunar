<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import NavLink from './NavLink.vue';
import Tooltip from './Tooltip.vue';
import UserMenu from './UserMenu.vue';
import { useNavState } from '../composables/useNavState';
import type { NavTreeShape } from '../types/navigation';

const props = withDefaults(defineProps<{ collapsed?: boolean }>(), { collapsed: false });

const { t } = useI18n();

const page = usePage();
const { closeDrawer } = useNavState();

const settingsTree = computed<NavTreeShape>(
    () => (page.props.settingsNavigation as NavTreeShape | undefined) ?? { groups: [], items: [] },
);

const mainNav = computed<NavTreeShape>(() => (page.props.navigation as NavTreeShape | undefined) ?? { groups: [], items: [] });

const backToMainUrl = computed<string>(
    () => mainNav.value.items.find((item) => item.key === 'dashboard')?.url ?? mainNav.value.items[0]?.url ?? '/',
);

const itemBase = 'flex items-center gap-2.5 rounded-sm text-[13px] text-ink-700 cursor-pointer select-none hover:bg-surface-2';
const backCls = computed(() => [
    itemBase,
    'mt-0.5',
    props.collapsed ? 'justify-center py-2 px-0 gap-0' : 'px-2.5 py-1.5',
]);
</script>

<template>
    <Tooltip :text="collapsed ? t('common.back_to_main') : ''">
        <Link :href="backToMainUrl" prefetch :class="backCls" @click="closeDrawer">
            <Icon name="arrowLeft" /> <span v-if="!collapsed">{{ t('common.back_to_main') }}</span>
        </Link>
    </Tooltip>

    <div class="h-px bg-line my-1.5" />

    <div v-if="settingsTree.items.length">
        <NavLink v-for="item in settingsTree.items" :key="item.key" :item="item" :collapsed="collapsed" />
    </div>

    <template v-for="group in settingsTree.groups" :key="group.key">
        <div
            v-if="!collapsed"
            class="text-[10px] uppercase tracking-[0.08em] text-ink-400 px-2.5 pt-3 pb-1.5 font-medium"
        >{{ group.label }}</div>
        <div
            v-else
            class="relative h-3 my-1 before:content-[''] before:absolute before:left-3 before:right-3 before:top-1/2 before:h-px before:bg-line"
        />
        <NavLink v-for="item in group.items" :key="item.key" :item="item" :collapsed="collapsed" />
    </template>

    <div class="flex-1" />
    <UserMenu :collapsed="collapsed" />
</template>
