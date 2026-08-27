<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Checkbox from './Checkbox.vue';
import FieldLabel from './FieldLabel.vue';
import Select from './Select.vue';
import Textarea from './Textarea.vue';
import TextInput from './TextInput.vue';

export interface AddressFormValues {
    title: string;
    first_name: string;
    last_name: string;
    company_name: string;
    tax_identifier: string;
    line_one: string;
    line_two: string;
    line_three: string;
    city: string;
    state: string;
    postcode: string;
    country_id: number | string;
    delivery_instructions: string;
    contact_email: string;
    contact_phone: string;
    shipping_default?: boolean;
    billing_default?: boolean;
}

/** An Inertia useForm over the address fields — values and errors are read and written directly. */
type AddressForm = AddressFormValues & {
    errors: Partial<Record<keyof AddressFormValues, string>>;
};

const props = defineProps<{
    form: AddressForm;
    countries: { id: number; name: string }[];
    /** Prefixes input ids so two address forms on one page keep unique label associations. */
    idPrefix: string;
    /** Customer-address default checkboxes; hidden for order addresses, which have no defaults. */
    showDefaults?: boolean;
}>();

const { t } = useI18n();

const fieldId = (name: string): string => `${props.idPrefix}-${name}`;
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <FieldLabel :for="fieldId('first-name')" required>{{ t('customers.field_first_name') }}</FieldLabel>
                <TextInput :id="fieldId('first-name')" v-model="form.first_name" :invalid="!!form.errors.first_name" />
                <div v-if="form.errors.first_name" class="mt-1 text-[11px] text-danger">{{ form.errors.first_name }}</div>
            </div>
            <div>
                <FieldLabel :for="fieldId('last-name')" required>{{ t('customers.field_last_name') }}</FieldLabel>
                <TextInput :id="fieldId('last-name')" v-model="form.last_name" :invalid="!!form.errors.last_name" />
                <div v-if="form.errors.last_name" class="mt-1 text-[11px] text-danger">{{ form.errors.last_name }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <FieldLabel :for="fieldId('title')">{{ t('customers.field_title') }}</FieldLabel>
                <TextInput :id="fieldId('title')" v-model="form.title" :placeholder="t('common.optional')" :invalid="!!form.errors.title" />
                <div v-if="form.errors.title" class="mt-1 text-[11px] text-danger">{{ form.errors.title }}</div>
            </div>
            <div>
                <FieldLabel :for="fieldId('company-name')">{{ t('customers.field_company_name') }}</FieldLabel>
                <TextInput :id="fieldId('company-name')" v-model="form.company_name" :invalid="!!form.errors.company_name" />
                <div v-if="form.errors.company_name" class="mt-1 text-[11px] text-danger">{{ form.errors.company_name }}</div>
            </div>
        </div>
        <div>
            <FieldLabel :for="fieldId('tax-identifier')">{{ t('customers.field_tax_identifier') }}</FieldLabel>
            <TextInput :id="fieldId('tax-identifier')" v-model="form.tax_identifier" mono :invalid="!!form.errors.tax_identifier" />
            <div v-if="form.errors.tax_identifier" class="mt-1 text-[11px] text-danger">{{ form.errors.tax_identifier }}</div>
        </div>
        <div>
            <FieldLabel :for="fieldId('line-one')" required>{{ t('customers.field_line_one') }}</FieldLabel>
            <TextInput :id="fieldId('line-one')" v-model="form.line_one" :invalid="!!form.errors.line_one" />
            <div v-if="form.errors.line_one" class="mt-1 text-[11px] text-danger">{{ form.errors.line_one }}</div>
        </div>
        <div>
            <FieldLabel :for="fieldId('line-two')">{{ t('customers.field_line_two') }}</FieldLabel>
            <TextInput :id="fieldId('line-two')" v-model="form.line_two" :invalid="!!form.errors.line_two" />
            <div v-if="form.errors.line_two" class="mt-1 text-[11px] text-danger">{{ form.errors.line_two }}</div>
        </div>
        <div>
            <FieldLabel :for="fieldId('line-three')">{{ t('customers.field_line_three') }}</FieldLabel>
            <TextInput :id="fieldId('line-three')" v-model="form.line_three" :invalid="!!form.errors.line_three" />
            <div v-if="form.errors.line_three" class="mt-1 text-[11px] text-danger">{{ form.errors.line_three }}</div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <FieldLabel :for="fieldId('city')" required>{{ t('customers.field_city') }}</FieldLabel>
                <TextInput :id="fieldId('city')" v-model="form.city" :invalid="!!form.errors.city" />
                <div v-if="form.errors.city" class="mt-1 text-[11px] text-danger">{{ form.errors.city }}</div>
            </div>
            <div>
                <FieldLabel :for="fieldId('state')">{{ t('customers.field_state') }}</FieldLabel>
                <TextInput :id="fieldId('state')" v-model="form.state" :invalid="!!form.errors.state" />
                <div v-if="form.errors.state" class="mt-1 text-[11px] text-danger">{{ form.errors.state }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <FieldLabel :for="fieldId('postcode')">{{ t('customers.field_postcode') }}</FieldLabel>
                <TextInput :id="fieldId('postcode')" v-model="form.postcode" :invalid="!!form.errors.postcode" />
                <div v-if="form.errors.postcode" class="mt-1 text-[11px] text-danger">{{ form.errors.postcode }}</div>
            </div>
            <div>
                <FieldLabel :for="fieldId('country')" required>{{ t('customers.field_country') }}</FieldLabel>
                <Select
                    :id="fieldId('country')"
                    :model-value="form.country_id"
                    :invalid="!!form.errors.country_id"
                    @update:model-value="(value) => (form.country_id = value ? Number(value) : '')"
                >
                    <option value="">{{ t('customers.select_country') }}</option>
                    <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                </Select>
                <div v-if="form.errors.country_id" class="mt-1 text-[11px] text-danger">{{ form.errors.country_id }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <FieldLabel :for="fieldId('contact-email')">{{ t('customers.field_contact_email') }}</FieldLabel>
                <TextInput :id="fieldId('contact-email')" v-model="form.contact_email" type="email" :invalid="!!form.errors.contact_email" />
                <div v-if="form.errors.contact_email" class="mt-1 text-[11px] text-danger">{{ form.errors.contact_email }}</div>
            </div>
            <div>
                <FieldLabel :for="fieldId('contact-phone')">{{ t('customers.field_contact_phone') }}</FieldLabel>
                <TextInput :id="fieldId('contact-phone')" v-model="form.contact_phone" type="tel" :invalid="!!form.errors.contact_phone" />
                <div v-if="form.errors.contact_phone" class="mt-1 text-[11px] text-danger">{{ form.errors.contact_phone }}</div>
            </div>
        </div>
        <div>
            <FieldLabel :for="fieldId('delivery-instructions')">{{ t('customers.field_delivery_instructions') }}</FieldLabel>
            <Textarea :id="fieldId('delivery-instructions')" v-model="form.delivery_instructions" :rows="2" :invalid="!!form.errors.delivery_instructions" />
            <div v-if="form.errors.delivery_instructions" class="mt-1 text-[11px] text-danger">{{ form.errors.delivery_instructions }}</div>
        </div>
        <div v-if="showDefaults !== false" class="flex gap-4">
            <label class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                <Checkbox v-model="form.shipping_default" />
                {{ t('customers.default_shipping') }}
            </label>
            <label class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                <Checkbox v-model="form.billing_default" />
                {{ t('customers.default_billing') }}
            </label>
        </div>
    </div>
</template>
