<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import FieldLabel from '../FieldLabel.vue';
import Select from '../Select.vue';
import TextInput from '../TextInput.vue';
import type { CarrierData, FulfilmentData } from './types';

const props = defineProps<{
    open: boolean;
    fulfilment: FulfilmentData | null;
    carriers: CarrierData[];
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm<{ carrier: string; shipping_method: string; tracking_number: string; tracking_url: string }>({
    carrier: '',
    shipping_method: '',
    tracking_number: '',
    tracking_url: '',
});

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

const services = computed<Record<string, string>>(
    () => props.carriers.find((carrier) => carrier.key === form.carrier)?.services ?? {},
);

const onCarrierChange = (): void => {
    form.shipping_method = '';
    form.tracking_url = '';
};

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (!props.fulfilment) {
        return;
    }

    form.transform((data) => Object.fromEntries(Object.entries(data).filter(([, value]) => value !== '')))
        .post(props.fulfilment.urls.trackings, { preserveScroll: true, onSuccess: close });
};
</script>

<template>
    <Dialog :open="open" :title="t('orders.tracking_title')" size="sm" @update:open="(v: boolean) => !v && close()">
        <div class="space-y-3">
            <div>
                <FieldLabel>{{ t('orders.ship_carrier') }}</FieldLabel>
                <Select v-model="form.carrier" @change="onCarrierChange">
                    <option value="">{{ t('orders.ship_carrier_none') }}</option>
                    <option v-for="carrier in carriers" :key="carrier.key" :value="carrier.key">{{ carrier.name }}</option>
                </Select>
            </div>
            <div v-if="Object.keys(services).length">
                <FieldLabel>{{ t('orders.ship_shipping_method') }}</FieldLabel>
                <Select v-model="form.shipping_method">
                    <option value="" />
                    <option v-for="(label, key) in services" :key="key" :value="key">{{ label }}</option>
                </Select>
            </div>
            <div>
                <FieldLabel>{{ t('orders.ship_tracking_number') }}</FieldLabel>
                <TextInput v-model="form.tracking_number" :invalid="!!form.errors.tracking_number" />
            </div>
            <div v-if="!form.carrier">
                <FieldLabel :hint="t('orders.ship_tracking_url_help')">{{ t('orders.ship_tracking_url') }}</FieldLabel>
                <TextInput v-model="form.tracking_url" type="url" :invalid="!!form.errors.tracking_url" />
                <p v-if="form.errors.tracking_url" class="m-0 text-danger text-[11px] mt-1">{{ form.errors.tracking_url }}</p>
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="form.processing" @click="submit">{{ t('orders.tracking_title') }}</Button>
        </template>
    </Dialog>
</template>
