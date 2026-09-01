<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import FieldLabel from '../FieldLabel.vue';
import Select from '../Select.vue';
import type { FulfilmentData, LocationData } from './types';

const props = defineProps<{
    open: boolean;
    fulfilment: FulfilmentData | null;
    locations: LocationData[];
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm<{ location_id: number | null }>({ location_id: null });

watch(
    () => props.open,
    (open) => {
        if (open) {
            form.clearErrors();
            form.location_id = props.fulfilment?.location_id ?? null;
        }
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (props.fulfilment && form.location_id) {
        form.put(props.fulfilment.urls.location, { preserveScroll: true, onSuccess: close });
    }
};
</script>

<template>
    <Dialog :open="open" :title="t('orders.location_title')" size="sm" @update:open="(v: boolean) => !v && close()">
        <div>
            <FieldLabel>{{ t('orders.location_field') }}</FieldLabel>
            <Select v-model="form.location_id">
                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
            </Select>
        </div>
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!form.location_id || form.processing" @click="submit">{{ t('orders.location_confirm') }}</Button>
        </template>
    </Dialog>
</template>
