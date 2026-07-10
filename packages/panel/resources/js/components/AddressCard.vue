<script setup lang="ts">
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';

interface AddressCardAddress {
    label?: string;
    title?: string;
    first_name?: string;
    last_name?: string;
    company_name?: string;
    tax_identifier?: string;
    line_one?: string;
    line_two?: string;
    line_three?: string;
    city?: string;
    state?: string;
    postcode?: string;
    country_id?: number | string;
    country_name?: string;
    delivery_instructions?: string;
    contact_email?: string;
    contact_phone?: string;
    shipping_default?: boolean;
    billing_default?: boolean;
}

const props = withDefaults(
    defineProps<{
        address: AddressCardAddress;
        showLabel?: boolean;
    }>(),
    { showLabel: true },
);

const fullName = computed(() => [props.address.title, props.address.first_name, props.address.last_name].filter(Boolean).join(' '));
</script>

<template>
    <div class="bg-surface border border-line rounded-md p-3.5 flex flex-col gap-2">
        <div v-if="showLabel" class="flex items-center gap-1.5 flex-wrap">
            <span class="text-[12.5px] text-ink-900 font-medium">{{ address.label || 'Address' }}</span>
            <StatusBadge v-if="address.billing_default" tone="sage" size="sm">Default billing</StatusBadge>
            <StatusBadge v-if="address.shipping_default" tone="sage" size="sm">Default shipping</StatusBadge>
        </div>
        <div class="text-[12.5px] text-ink-700 leading-[1.5]">
            <div v-if="address.company_name" class="text-ink-900">{{ address.company_name }}</div>
            <div class="text-ink-900">{{ fullName }}</div>
            <div>{{ address.line_one }}<span v-if="address.line_two">, {{ address.line_two }}</span><span v-if="address.line_three">, {{ address.line_three }}</span></div>
            <div>{{ address.city }}<span v-if="address.state">, {{ address.state }}</span></div>
            <div>{{ address.postcode }}</div>
            <div class="text-ink-500">{{ address.country_name }}</div>
            <div v-if="address.contact_phone" class="text-ink-500 mt-1">{{ address.contact_phone }}</div>
            <div v-if="address.contact_email" class="text-ink-500">{{ address.contact_email }}</div>
            <div v-if="address.delivery_instructions" class="text-ink-500 mt-1">{{ address.delivery_instructions }}</div>
        </div>
        <div v-if="$slots.actions" class="border-t border-line pt-2 flex flex-wrap gap-1.5">
            <slot name="actions" />
        </div>
    </div>
</template>
