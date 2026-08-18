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
        size?: 'sm' | 'md' | 'lg' | 'xl';
        hideCloseButton?: boolean;
    }>(),
    { open: false, title: '', description: '', size: 'md', hideCloseButton: false },
);

defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const sizeMap: Record<string, string> = {
    sm: 'max-w-[420px]',
    md: 'max-w-[520px]',
    lg: 'max-w-[720px]',
    xl: 'max-w-[880px]',
};
</script>

<template>
    <DialogRoot :open="open" @update:open="$emit('update:open', $event)">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 bg-ink-900/40 z-50 data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out" />
            <DialogContent
                :class="[
                    'fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[calc(100vw-2rem)] max-h-[calc(100vh-4rem)] z-50',
                    'flex flex-col bg-paper border border-line rounded-xl shadow-lg focus:outline-none',
                    'data-[state=open]:animate-dialog-in data-[state=closed]:animate-dialog-out',
                    sizeMap[size] || sizeMap.md,
                ]"
            >
                <div class="flex items-start gap-3 px-5 pt-5 pb-4 shrink-0">
                    <div class="flex-1 min-w-0">
                        <DialogTitle class="text-sm font-semibold text-ink-900 tracking-[-0.01em]">{{ title }}</DialogTitle>
                        <DialogDescription
                            v-if="description"
                            class="mt-1 text-xs text-ink-500 leading-normal"
                        >{{ description }}</DialogDescription>
                        <VisuallyHidden v-else><DialogDescription>{{ title }}</DialogDescription></VisuallyHidden>
                    </div>
                    <DialogClose
                        v-if="!hideCloseButton"
                        class="shrink-0 -mr-1 -mt-0.5 p-1 rounded-sm text-ink-500 hover:text-ink-900 hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35"
                        :aria-label="t('common.close')"
                    >
                        <Icon name="x" cls="sm" />
                    </DialogClose>
                </div>

                <div class="min-w-0 px-5 pb-5 flex-1 overflow-y-auto">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="flex justify-end gap-2 px-5 py-4 border-t border-line shrink-0">
                    <slot name="footer" />
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
