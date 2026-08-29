<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import FieldLabel from '../FieldLabel.vue';
import Icon from '../Icon.vue';
import Select from '../Select.vue';
import TextInput from '../TextInput.vue';
import Toggle from '../Toggle.vue';
import type { CarrierData, FulfilmentData } from './types';

interface TrackingRow {
    carrier: string;
    shipping_method: string;
    tracking_number: string;
    tracking_url: string;
}

const props = defineProps<{
    open: boolean;
    fulfilment: FulfilmentData | null;
    carriers: CarrierData[];
    showNotify: boolean;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const emptyRow = (): TrackingRow => ({ carrier: '', shipping_method: '', tracking_number: '', tracking_url: '' });

const form = useForm<{ tracking: TrackingRow[]; notify: boolean }>({ tracking: [emptyRow()], notify: true });

watch(
    () => props.open,
    (open) => {
        if (open) {
            form.reset();
            form.clearErrors();
            form.tracking = [emptyRow()];
        }
    },
    { immediate: true },
);

const services = (row: TrackingRow): Record<string, string> =>
    props.carriers.find((carrier) => carrier.key === row.carrier)?.services ?? {};

const onCarrierChange = (row: TrackingRow): void => {
    row.shipping_method = '';
    row.tracking_url = '';
};

const addRow = (): void => {
    form.tracking.push(emptyRow());
};

const removeRow = (index: number): void => {
    form.tracking.splice(index, 1);
};

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (!props.fulfilment) {
        return;
    }

    form.transform((data) => ({
        notify: data.notify,
        // Drop empty fields and entirely-empty rows.
        tracking: data.tracking
            .map((row) => Object.fromEntries(Object.entries(row).filter(([, value]) => value !== '')))
            .filter((row) => Object.keys(row).length > 0),
    })).post(props.fulfilment.urls.ship, { preserveScroll: true, onSuccess: close });
};
</script>

<template>
    <Dialog :open="open" :title="t('orders.mark_shipped')" size="sm" @update:open="(v: boolean) => !v && close()">
        <div class="space-y-4">
            <div v-for="(row, index) in form.tracking" :key="index" class="space-y-3" :class="index > 0 ? 'pt-3 border-t border-line' : ''">
                <div class="flex items-end gap-1.5">
                    <div class="flex-1">
                        <FieldLabel>{{ t('orders.ship_carrier') }}</FieldLabel>
                        <Select v-model="row.carrier" @change="onCarrierChange(row)">
                            <option value="">{{ t('orders.ship_carrier_none') }}</option>
                            <option v-for="carrier in carriers" :key="carrier.key" :value="carrier.key">{{ carrier.name }}</option>
                        </Select>
                    </div>
                    <Button
                        v-if="form.tracking.length > 1"
                        variant="ghost"
                        icon="trash"
                        :aria-label="t('orders.tracking_remove_row')"
                        @click="removeRow(index)"
                    />
                </div>
                <div v-if="Object.keys(services(row)).length">
                    <FieldLabel>{{ t('orders.ship_shipping_method') }}</FieldLabel>
                    <Select v-model="row.shipping_method">
                        <option value="" />
                        <option v-for="(label, key) in services(row)" :key="key" :value="key">{{ label }}</option>
                    </Select>
                </div>
                <div>
                    <FieldLabel>{{ t('orders.ship_tracking_number') }}</FieldLabel>
                    <TextInput v-model="row.tracking_number" />
                </div>
                <div v-if="!row.carrier">
                    <FieldLabel :hint="t('orders.ship_tracking_url_help')">{{ t('orders.ship_tracking_url') }}</FieldLabel>
                    <TextInput v-model="row.tracking_url" type="url" />
                </div>
            </div>

            <button type="button" class="flex items-center gap-1.5 text-[12px] text-ink-500 hover:text-ink-900" @click="addRow">
                <Icon name="plus" cls="sm" />
                {{ t('orders.tracking_add_row') }}
            </button>

            <label v-if="showNotify" class="flex items-center gap-2 text-[12.5px] text-ink-700">
                <Toggle :on="form.notify" @toggle="form.notify = !form.notify" />
                {{ t('orders.ship_notify') }}
            </label>
        </div>
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="form.processing" @click="submit">{{ t('orders.mark_shipped') }}</Button>
        </template>
    </Dialog>
</template>
