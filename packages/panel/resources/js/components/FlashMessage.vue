<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';

const props = withDefaults(
    defineProps<{
        message?: string | null;
        tone?: 'success' | 'error';
        /** Milliseconds before the message dismisses itself; 0 keeps it until dismissed. */
        timeout?: number;
    }>(),
    { message: null, tone: 'success', timeout: 6000 },
);

const { t } = useI18n();

const visible = ref(false);
let timer: ReturnType<typeof setTimeout> | undefined;

const dismiss = (): void => {
    visible.value = false;
    clearTimeout(timer);
};

const arm = (): void => {
    clearTimeout(timer);

    if (props.timeout > 0) {
        timer = setTimeout(dismiss, props.timeout);
    }
};

const show = (): void => {
    if (!props.message) {
        visible.value = false;

        return;
    }

    visible.value = true;
    arm();
};

// Re-show on every server response carrying flash data: the prop object is a
// fresh reference per visit, so a repeat save with identical text retriggers.
const page = usePage();
watch(() => [page.props.flash, props.message] as const, show, { immediate: true });

// Hovering pauses the auto-dismiss so the message can be read in peace.
const pause = (): void => clearTimeout(timer);
const resume = (): void => {
    if (visible.value) {
        arm();
    }
};

onBeforeUnmount(() => clearTimeout(timer));

const TONE = {
    success: {
        wrap: 'border-sage-border bg-sage-soft text-sage-ink',
        icon: 'check',
    },
    error: {
        wrap: 'border-danger-border bg-danger-soft text-danger',
        icon: 'alert',
    },
} as const;
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        leave-active-class="transition-all duration-200 ease-in"
        enter-from-class="opacity-0 max-h-0 !py-0 !mt-0 !mb-0 -translate-y-1"
        leave-to-class="opacity-0 max-h-0 !py-0 !mt-0 !mb-0 -translate-y-1"
    >
        <div
            v-if="visible && message"
            :class="['flex items-center gap-2 overflow-hidden max-h-24 rounded-md border px-3 py-2 text-[12px]', TONE[tone].wrap]"
            role="status"
            @mouseenter="pause"
            @mouseleave="resume"
        >
            <Icon :name="TONE[tone].icon" cls="sm" class="shrink-0" />
            <span class="flex-1 min-w-0 truncate">{{ message }}</span>
            <button
                type="button"
                class="shrink-0 grid place-items-center w-[18px] h-[18px] rounded-full text-current/70 hover:text-current hover:bg-surface/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                :aria-label="t('common.close')"
                @click="dismiss"
            >
                <Icon name="x" cls="sm" />
            </button>
        </div>
    </Transition>
</template>
