<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';
import type { NavItemShape } from '../types/navigation';

const props = withDefaults(defineProps<{ item: NavItemShape; collapsed?: boolean; depth?: number }>(), {
    collapsed: false,
    depth: 0,
});

const page = usePage();

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

const isActive = computed(() => {
    const path = pathOf(props.item.url);

    if (!path) {
        return false;
    }

    const current = page.url.split('?')[0];

    return props.item.exact ? current === path : current.startsWith(path);
});
</script>

<template>
    <div>
        <Link
            :href="item.url ?? '#'"
            :class="[
                'flex items-center gap-2 rounded-md px-2 py-1.5 text-[13px] transition-colors duration-100',
                depth ? 'ml-4' : '',
                isActive ? 'bg-active text-ink-900 font-medium' : 'text-ink-700 hover:bg-surface-2 hover:text-ink-900',
            ]"
        >
            <Icon v-if="item.icon" :name="item.icon" cls="sm" />
            <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
            <span
                v-if="!collapsed && item.badge"
                class="ml-auto rounded-full bg-sage-soft px-1.5 py-0.5 text-[10px] font-semibold text-sage-ink"
            >{{ item.badge }}</span>
        </Link>

        <div v-if="!collapsed && item.children.length" class="mt-0.5 space-y-0.5">
            <NavLink v-for="child in item.children" :key="child.key" :item="child" :depth="depth + 1" />
        </div>
    </div>
</template>
