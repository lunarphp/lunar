<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';

// A crumb with an `href` renders as an Inertia link; the `current` crumb is the
// active page (bold, and the only one shown on mobile). Ancestors collapse on
// narrow viewports, matching the design prototype.
export interface BreadcrumbItem {
    label: string;
    href?: string;
    current?: boolean;
}

defineProps<{ items: BreadcrumbItem[] }>();

const { t } = useI18n();
</script>

<template>
    <!-- Top bar: breadcrumb trail on the left, page-level actions on the right. The
         min-height matches the sidebar's brand header so the two top bands line up,
         whether or not a page fills the actions slot. Sticky on desktop only — on
         mobile the layouts' own panel-name bar holds the top-0 sticky slot. -->
    <div class="lg:sticky lg:top-0 z-30 flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-2.5 min-h-[50px] border-b border-line bg-paper/75 backdrop-saturate-[1.6] backdrop-blur-md">
        <nav :aria-label="t('common.breadcrumb')" class="flex items-center gap-1.5 text-xs text-ink-500 min-w-0">
            <template v-for="(crumb, i) in items" :key="i">
                <Icon v-if="i > 0" name="chevronRight" cls="sm" class="text-ink-300 hidden sm:inline" />
                <component
                    :is="crumb.href ? Link : 'span'"
                    :href="crumb.href"
                    :aria-current="crumb.current ? 'page' : undefined"
                    :class="[
                        crumb.current ? 'text-ink-900 font-medium truncate' : 'hidden sm:inline',
                        crumb.href ? 'hover:text-ink-900' : '',
                    ]"
                >{{ crumb.label }}</component>
            </template>
        </nav>

        <div class="flex-1" />

        <div class="flex items-center gap-1.5 shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>
