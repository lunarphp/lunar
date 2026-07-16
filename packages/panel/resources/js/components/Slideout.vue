<script setup lang="ts">
import {
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
    VisuallyHidden,
} from 'reka-ui';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';

withDefaults(
    defineProps<{
        open?: boolean;
        title?: string;
        description?: string;
        size?: 'md' | 'lg' | 'xl';
        hideCloseButton?: boolean;
    }>(),
    { open: false, title: '', description: '', size: 'lg', hideCloseButton: false },
);

defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const sizeMap: Record<string, string> = {
    md: 'sm:max-w-[520px]',
    lg: 'sm:max-w-[640px]',
    xl: 'sm:max-w-[760px]',
};
</script>

<template>
    <DialogRoot :open="open" @update:open="$emit('update:open', $event)">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 bg-ink-900/40 z-50 data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out" />
            <DialogContent
                :class="[
                    'fixed right-0 top-0 h-full w-[calc(100vw-2rem)] z-50',
                    'flex flex-col bg-paper border-l border-line shadow-lg focus:outline-none',
                    'will-change-transform',
                    'data-[state=open]:animate-slide-in-right data-[state=closed]:animate-slide-out-right',
                    sizeMap[size] || sizeMap.lg,
                ]"
            >
                <div class="flex items-start gap-3 px-5 pt-5 pb-4 shrink-0 border-b border-line">
                    <div class="flex-1 min-w-0">
                        <DialogTitle v-if="title" class="text-sm font-semibold text-ink-900 tracking-[-0.01em]">{{ title }}</DialogTitle>
                        <VisuallyHidden v-else><DialogTitle>{{ description || 'Panel' }}</DialogTitle></VisuallyHidden>
                        <DialogDescription
                            v-if="description"
                            class="mt-1 text-xs text-ink-500 leading-normal"
                        >{{ description }}</DialogDescription>
                        <VisuallyHidden v-else><DialogDescription>{{ title }}</DialogDescription></VisuallyHidden>
                        <slot name="header" />
                    </div>
                    <DialogClose
                        v-if="!hideCloseButton"
                        class="shrink-0 -mr-1 -mt-0.5 p-1 rounded-sm text-ink-500 hover:text-ink-900 hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35"
                        :aria-label="t('common.close')"
                    >
                        <Icon name="x" cls="sm" />
                    </DialogClose>
                </div>

                <div class="min-w-0 flex-1 overflow-y-auto">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="flex justify-end gap-2 px-5 py-3.5 border-t border-line shrink-0 bg-paper">
                    <slot name="footer" />
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
