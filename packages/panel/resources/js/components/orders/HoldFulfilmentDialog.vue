<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import FieldLabel from '../FieldLabel.vue';
import Select from '../Select.vue';
import Textarea from '../Textarea.vue';
import type { FulfilmentData } from './types';

const props = defineProps<{
    open: boolean;
    fulfilment: FulfilmentData | null;
    holdReasons: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm<{ reason: string; note: string }>({ reason: '', note: '' });

watch(
    () => props.open,
    (open) => {
        if (open) {
            form.reset();
            form.clearErrors();
        }
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (!props.fulfilment) {
        return;
    }

    form.transform((data) => ({
        reason: data.reason || null,
        note: data.note || null,
    })).post(props.fulfilment.urls.hold, { preserveScroll: true, onSuccess: close });
};
</script>

<template>
    <Dialog :open="open" :title="t('orders.hold_title')" size="sm" @update:open="(v: boolean) => !v && close()">
        <div class="space-y-3">
            <div>
                <FieldLabel>{{ t('orders.hold_reason') }}</FieldLabel>
                <Select v-model="form.reason">
                    <option value="">{{ t('orders.hold_no_reason') }}</option>
                    <option v-for="(label, key) in holdReasons" :key="key" :value="key">{{ label }}</option>
                </Select>
                <p v-if="form.errors.reason" class="m-0 text-danger text-[11px] mt-1">{{ form.errors.reason }}</p>
            </div>
            <div>
                <FieldLabel>{{ t('orders.hold_note') }}</FieldLabel>
                <Textarea v-model="form.note" :rows="3" />
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="form.processing" @click="submit">{{ t('orders.hold_confirm') }}</Button>
        </template>
    </Dialog>
</template>
