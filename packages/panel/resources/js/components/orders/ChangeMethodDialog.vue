<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AddressFormFields from '../AddressFormFields.vue';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import FieldLabel from '../FieldLabel.vue';
import Select from '../Select.vue';
import type { FulfilmentData, FulfilmentMethodTarget, LocationData } from './types';

const props = defineProps<{
    open: boolean;
    fulfilment: FulfilmentData | null;
    method: FulfilmentMethodTarget | null;
    locations: LocationData[];
    hasShippingAddress: boolean;
    countries: { id: number; name: string }[];
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm<{ location_id: number | null }>({ location_id: null });

const address = useForm({
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

// A delivery method sends goods to the shipping address, so one is entered here when the order has none.
const needsAddress = computed(() => !!props.method?.requires_delivery && !props.hasShippingAddress);

watch(
    () => props.open,
    (open) => {
        if (open) {
            form.clearErrors();
            address.reset();
            address.clearErrors();
            form.location_id = props.method?.location_id ?? null;
        }
    },
    { immediate: true },
);

const title = computed(() => (props.method ? t('orders.action_change_method', { method: props.method.label }) : ''));

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (!props.fulfilment || !props.method) {
        return;
    }

    const method = props.method;

    form.transform(({ location_id }) => ({
        method: method.key,
        location_id: method.requires_delivery ? null : location_id,
        address: needsAddress.value ? address.data() : null,
    })).put(props.fulfilment.urls.method, {
        preserveScroll: true,
        onSuccess: close,
        // The address fields read their errors unprefixed.
        onError: (errors) => address.setError(Object.fromEntries(
            Object.entries(errors)
                .filter(([field]) => field.startsWith('address.'))
                .map(([field, message]) => [field.slice('address.'.length), message]),
        )),
    });
};
</script>

<template>
    <Dialog :open="open" :title="title" :size="needsAddress ? 'md' : 'sm'" @update:open="(v: boolean) => !v && close()">
        <div v-if="method && !method.requires_delivery">
            <FieldLabel>{{ t('orders.location_field') }}</FieldLabel>
            <Select v-model="form.location_id">
                <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
            </Select>
            <div v-if="form.errors.location_id" class="mt-1 text-[11px] text-danger">{{ form.errors.location_id }}</div>
        </div>
        <template v-else-if="needsAddress">
            <p class="m-0 mb-3 text-[12.5px] text-ink-500">{{ t('orders.change_method_address_hint') }}</p>
            <AddressFormFields :form="address" :countries="countries" id-prefix="change-method-address" :show-defaults="false" />
        </template>
        <p v-else class="m-0 text-[12.5px] text-ink-500">{{ t('orders.change_method_ships_to_address') }}</p>
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="form.processing" @click="submit">{{ title }}</Button>
        </template>
    </Dialog>
</template>
