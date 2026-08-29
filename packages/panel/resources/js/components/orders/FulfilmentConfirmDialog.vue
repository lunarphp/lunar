<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ConfirmDialog from '../ConfirmDialog.vue';
import Toggle from '../Toggle.vue';

/**
 * A confirm step for one pending fulfilment action — fulfil / return /
 * undo-return / release / cancel / a plain transition — dispatched when
 * confirmed. `showNotify` renders the notify toggle; its presence is the cue
 * that the action emails the customer.
 */
export interface PendingFulfilmentAction {
    title: string;
    description?: string;
    confirmLabel?: string;
    tone?: 'default' | 'danger';
    url: string;
    method?: 'post' | 'delete';
    data?: Record<string, unknown>;
    showNotify?: boolean;
}

const props = defineProps<{ action: PendingFulfilmentAction | null }>();

const emit = defineEmits<{ close: [] }>();

const { t } = useI18n();

const notify = ref(true);

watch(
    () => props.action,
    (action) => {
        if (action) {
            notify.value = true;
        }
    },
);

const confirm = (): void => {
    const action = props.action;

    if (!action) {
        return;
    }

    const data = { ...(action.data ?? {}), ...(action.showNotify ? { notify: notify.value } : {}) };

    if ((action.method ?? 'post') === 'delete') {
        router.delete(action.url, { preserveScroll: true });
    } else {
        router.post(action.url, data, { preserveScroll: true });
    }

    emit('close');
};
</script>

<template>
    <ConfirmDialog
        :open="!!action"
        :title="action?.title"
        :description="action?.description ?? ''"
        :confirm-label="action?.confirmLabel ?? action?.title"
        :tone="action?.tone ?? 'default'"
        @update:open="(v: boolean) => !v && emit('close')"
        @confirm="confirm"
    >
        <label v-if="action?.showNotify" class="flex items-center gap-2 text-[12.5px] text-ink-700">
            <Toggle :on="notify" @toggle="notify = !notify" />
            {{ t('orders.ship_notify') }}
        </label>
    </ConfirmDialog>
</template>
