<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import type { FulfilmentData } from './types';

const props = defineProps<{ open: boolean; fulfilment: FulfilmentData | null }>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm<{ target_id: number | null }>({ target_id: null });

watch(
    () => props.open,
    (open) => {
        if (open) {
            form.clearErrors();
            form.target_id = props.fulfilment?.merge_targets[0]?.id ?? null;
        }
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (props.fulfilment && form.target_id) {
        form.post(props.fulfilment.urls.merge, { preserveScroll: true, onSuccess: close });
    }
};
</script>

<template>
    <Dialog :open="open" :title="t('orders.merge_title')" :description="t('orders.merge_help')" size="sm" @update:open="(v: boolean) => !v && close()">
        <div v-if="fulfilment" class="space-y-1.5">
            <span class="block text-[11.5px] text-ink-500">{{ t('orders.merge_target') }}</span>
            <label
                v-for="target in fulfilment.merge_targets"
                :key="target.id"
                class="flex items-center gap-2.5 px-3 py-2 border border-line rounded-md cursor-pointer text-[12.5px]"
                :class="form.target_id === target.id ? 'border-sage-ink bg-sage-soft/40' : 'hover:bg-surface-2'"
            >
                <input v-model="form.target_id" type="radio" :value="target.id" class="accent-current" />
                <span class="font-mono text-ink-900">{{ target.reference }}</span>
                <span class="text-ink-500">{{ t('orders.item_count', { count: target.quantity }, target.quantity) }}</span>
            </label>
            <p v-if="form.errors.target_id" class="m-0 text-danger text-[11px]">{{ form.errors.target_id }}</p>
        </div>
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!form.target_id || form.processing" @click="submit">{{ t('orders.merge_confirm') }}</Button>
        </template>
    </Dialog>
</template>
