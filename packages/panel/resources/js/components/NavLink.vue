<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';
import Tooltip from './Tooltip.vue';
import { useNavState } from '../composables/useNavState';
import type { NavItemShape } from '../types/navigation';

const props = withDefaults(defineProps<{ item: NavItemShape; collapsed?: boolean }>(), {
    collapsed: false,
});

const page = usePage();
const { closeDrawer } = useNavState();

function pathOf(url: string | null): string | null {
    if (!url) {
        return null;
    }

    try {
        return new URL(url, window.location.origin).pathname;
    } catch {
        return null;
    }
}

function matches(url: string | null, exact: boolean): boolean {
    const path = pathOf(url);

    if (!path) {
        return false;
    }

    const current = page.url.split('?')[0];

    return exact ? current === path : current.startsWith(path);
}

const isActive = computed(
    () => matches(props.item.url, props.item.exact) || props.item.children.some((child) => matches(child.url, child.exact)),
);

const itemBase = 'flex items-center gap-2.5 rounded-sm text-[13px] text-ink-700 cursor-pointer select-none hover:bg-surface-2 transition-colors';

const itemCls = computed(() => [
    itemBase,
    props.collapsed ? 'justify-center py-2 px-0 gap-0' : 'px-2.5 py-1.5',
    isActive.value ? 'bg-active text-ink-900 font-medium' : '',
]);

function childCls(child: NavItemShape) {
    return [
        'flex items-center gap-2.5 pl-[34px] pr-2.5 py-1.5 rounded-sm text-xs cursor-pointer select-none hover:bg-surface-2',
        matches(child.url, child.exact) ? 'text-ink-900 font-medium' : 'text-ink-500',
    ];
}
</script>

<template>
    <Tooltip :text="collapsed ? item.label : ''">
        <Link :href="item.url ?? '#'" :prefetch="!!item.url" :class="itemCls" @click="closeDrawer">
            <Icon v-if="item.icon" :name="item.icon" />
            <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
            <span
                v-if="!collapsed && item.badge"
                class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[16px] px-1 rounded-full bg-sage-soft border border-sage-border text-[9.5px] font-semibold text-sage-ink tabular-nums"
            >{{ item.badge }}</span>
        </Link>
    </Tooltip>

    <template v-if="!collapsed && isActive && item.children.length">
        <Link
            v-for="child in item.children"
            :key="child.key"
            :href="child.url ?? '#'"
            :prefetch="!!child.url"
            :class="childCls(child)"
            @click="closeDrawer"
        >{{ child.label }}</Link>
    </template>
</template>
