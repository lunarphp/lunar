<script setup lang="ts">
import { computed } from 'vue';

export interface OrderAddressDetailsData {
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    line_one: string | null;
    line_two: string | null;
    line_three: string | null;
    city: string | null;
    state: string | null;
    postcode: string | null;
    country: string | null;
    contact_phone: string | null;
    delivery_instructions: string | null;
}

/**
 * An order address as it reads in the order sidebar. Delivery instructions
 * are shown only where they matter, on the address the order ships to.
 */
const props = defineProps<{
    address: OrderAddressDetailsData;
    showInstructions?: boolean;
}>();

const lines = computed<string[]>(() =>
    [
        [props.address.first_name, props.address.last_name].filter(Boolean).join(' '),
        props.address.company_name,
        props.address.line_one,
        props.address.line_two,
        props.address.line_three,
        [props.address.city, props.address.postcode].filter(Boolean).join(' '),
        props.address.state,
        props.address.country,
    ].filter((line): line is string => !!line && line.trim().length > 0),
);
</script>

<template>
    <address class="not-italic text-[12.5px] text-ink-700 leading-relaxed">
        <div v-for="(line, i) in lines" :key="i">{{ line }}</div>
        <div v-if="address.contact_phone" class="mt-1.5 pt-1.5 border-t border-line">
            <a :href="`tel:${address.contact_phone}`" class="text-ink-500 hover:text-ink-900">{{ address.contact_phone }}</a>
        </div>
        <div v-if="showInstructions && address.delivery_instructions" class="mt-1.5 pt-1.5 border-t border-line text-ink-500 whitespace-pre-line" data-testid="delivery-instructions">
            {{ address.delivery_instructions }}
        </div>
    </address>
</template>
