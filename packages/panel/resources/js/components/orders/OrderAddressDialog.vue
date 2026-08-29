<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AddressFormFields from '../AddressFormFields.vue';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';

export interface OrderAddressData {
    id: number;
    type: string;
    title: string | null;
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    tax_identifier: string | null;
    line_one: string | null;
    line_two: string | null;
    line_three: string | null;
    city: string | null;
    state: string | null;
    postcode: string | null;
    country_id: number | null;
    contact_email: string | null;
    contact_phone: string | null;
    delivery_instructions: string | null;
    update_url: string;
}

const props = defineProps<{
    open: boolean;
    address: OrderAddressData | null;
    title: string;
    countries: { id: number; name: string }[];
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm({
    title: '',
    first_name: '',
    last_name: '',
    company_name: '',
    tax_identifier: '',
    line_one: '',
    line_two: '',
    line_three: '',
    city: '',
    state: '',
    postcode: '',
    country_id: '' as number | string,
    delivery_instructions: '',
    contact_email: '',
    contact_phone: '',
});

watch(
    () => props.open,
    (open) => {
        if (open && props.address) {
            form.clearErrors();
            form.title = props.address.title ?? '';
            form.first_name = props.address.first_name ?? '';
            form.last_name = props.address.last_name ?? '';
            form.company_name = props.address.company_name ?? '';
            form.tax_identifier = props.address.tax_identifier ?? '';
            form.line_one = props.address.line_one ?? '';
            form.line_two = props.address.line_two ?? '';
            form.line_three = props.address.line_three ?? '';
            form.city = props.address.city ?? '';
            form.state = props.address.state ?? '';
            form.postcode = props.address.postcode ?? '';
            form.country_id = props.address.country_id ?? '';
            form.delivery_instructions = props.address.delivery_instructions ?? '';
            form.contact_email = props.address.contact_email ?? '';
            form.contact_phone = props.address.contact_phone ?? '';
        }
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (props.address) {
        form.put(props.address.update_url, { preserveScroll: true, onSuccess: close });
    }
};
</script>

<template>
    <Dialog :open="open" :title="title" size="md" @update:open="(v: boolean) => !v && close()">
        <AddressFormFields :form="form" :countries="countries" id-prefix="order-address" :show-defaults="false" />
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="form.processing" @click="submit">{{ t('orders.save_address') }}</Button>
        </template>
    </Dialog>
</template>
