<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import { pushServerFlash, useToasts, type ServerFlash, type Toast } from '../composables/useToasts';

// The panel's notification surface: a fixed bottom-centre stack fed by the
// useToasts() store, so messages stay visible wherever the page is scrolled.
// Mounted once per shell (PanelLayout, SettingsShell); it also bridges the
// server's flash props (success / error / info) into the stack, so pages no
// longer render flash messages themselves.
const { t } = useI18n();

const { toasts, dismiss } = useToasts();

const page = usePage();

watch(
    () => page.props.flash as ServerFlash | undefined,
    (flash) => pushServerFlash(flash),
    { immediate: true },
);

// Auto-dismiss timers live here (not in the store) so hovering a toast can
// pause its clock; re-arming on leave restarts the full duration.
const timers = new Map<number, ReturnType<typeof setTimeout>>();

const stopTimer = (id: number): void => {
    const timer = timers.get(id);

    if (timer !== undefined) {
        clearTimeout(timer);
        timers.delete(id);
    }
};

const remove = (id: number): void => {
    stopTimer(id);
    dismiss(id);
};

const arm = (toast: Toast): void => {
    if (toast.duration > 0 && !timers.has(toast.id)) {
        timers.set(
            toast.id,
            setTimeout(() => remove(toast.id), toast.duration),
        );
    }
};

watch(
    toasts,
    (list) => {
        list.forEach(arm);

        for (const id of [...timers.keys()]) {
            if (!list.some((toast) => toast.id === id)) {
                stopTimer(id);
            }
        }
    },
    { immediate: true, deep: true },
);

onBeforeUnmount(() => {
    for (const id of [...timers.keys()]) {
        stopTimer(id);
    }
});

const pause = (toast: Toast): void => stopTimer(toast.id);
const resume = (toast: Toast): void => arm(toast);

const TONE = {
    success: {
        wrap: 'border-sage-border bg-sage-soft text-sage-ink',
        icon: 'check',
    },
    error: {
        wrap: 'border-danger-border bg-danger-soft text-danger',
        icon: 'alert',
    },
    info: {
        wrap: 'border-line bg-surface text-ink-700',
        icon: 'info',
    },
} as const;
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed bottom-4 inset-x-0 z-[70] flex flex-col items-center gap-2 px-4 pointer-events-none"
            aria-live="polite"
        >
            <TransitionGroup
                enter-active-class="transition-all duration-300 ease-out"
                leave-active-class="transition-all duration-200 ease-in"
                enter-from-class="opacity-0 translate-y-2"
                leave-to-class="opacity-0 translate-y-2"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="['pointer-events-auto flex items-center gap-2 w-full max-w-[420px] rounded-lg border px-3.5 py-2.5 text-[12.5px] shadow-lg', TONE[toast.tone].wrap]"
                    :role="toast.tone === 'error' ? 'alert' : 'status'"
                    @mouseenter="pause(toast)"
                    @mouseleave="resume(toast)"
                >
                    <Icon :name="TONE[toast.tone].icon" cls="sm" class="shrink-0" />
                    <span class="flex-1 min-w-0 break-words">{{ toast.message }}</span>
                    <button
                        type="button"
                        class="shrink-0 grid place-items-center w-[18px] h-[18px] rounded-full text-current/70 hover:text-current hover:bg-surface/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                        :aria-label="t('common.close')"
                        @click="remove(toast.id)"
                    >
                        <Icon name="x" cls="sm" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
