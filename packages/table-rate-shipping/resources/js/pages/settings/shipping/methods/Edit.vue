<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, ConfirmDialog, FieldLabel, RichTextEditor, Section, Select, SettingsShell, TextInput, Toggle } from '@lunarphp/panel';
import CustomerGroupAvailability, { type GroupRow } from '../../../../components/CustomerGroupAvailability.vue';
import DriverSettings, { type DriverData } from '../../../../components/DriverSettings.vue';
import ScheduleGrid, { type Schedule } from '../../../../components/ScheduleGrid.vue';
import { noLayout } from '../../../../lib/layout';
import type { CurrencyOption } from '../../../../lib/types';

defineOptions({ layout: noLayout });

const props = defineProps<{
    method: {
        id: number;
        name: string;
        code: string;
        driver: string;
        description: string | null;
        stock_available: boolean;
        weight_unit: string | null;
        min_weight: number | string | null;
        max_weight: number | string | null;
        data: DriverData & { schedule: Schedule | null };
    };
    drivers: { key: string; label: string }[];
    weightUnits: string[];
    currencies: CurrencyOption[];
    customerGroups: GroupRow[];
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed(() => [
    { label: t('nav.settings') },
    { label: t('shipping::methods.title'), href: props.urls.index },
    { label: props.method.name, current: true },
]);

const form = useForm({
    name: props.method.name,
    code: props.method.code,
    driver: props.method.driver,
    description: props.method.description ?? '',
    stock_available: props.method.stock_available,
    weight_unit: props.method.weight_unit ?? '',
    min_weight: props.method.min_weight ?? '',
    max_weight: props.method.max_weight ?? '',
    // `data` is reserved by useForm; renamed back to data on submit.
    driver_data: {
        charge_by: props.method.data.charge_by,
        use_discount_amount: props.method.data.use_discount_amount,
        minimum_spend: { ...props.method.data.minimum_spend },
        schedule: props.method.data.schedule ? JSON.parse(JSON.stringify(props.method.data.schedule)) as Schedule : null,
    },
    customer_groups: props.customerGroups.map((row) => ({ ...row })),
});

const driverData = computed<DriverData>({
    get: () => ({ charge_by: form.driver_data.charge_by, use_discount_amount: form.driver_data.use_discount_amount, minimum_spend: form.driver_data.minimum_spend }),
    set: (value) => {
        form.driver_data = { ...form.driver_data, ...value };
    },
});

const driverDescriptionKey = computed(() => {
    const key = `shipping::methods.driver_desc_${form.driver}`;

    return ['ship-by', 'free-shipping'].includes(form.driver) ? key : 'shipping::methods.driver_desc_none';
});

const submit = (): void => {
    form.transform(({ driver_data, ...rest }) => ({
        ...rest,
        data: driver_data,
        weight_unit: rest.weight_unit || null,
        min_weight: rest.weight_unit ? rest.min_weight : null,
        max_weight: rest.weight_unit ? rest.max_weight : null,
    })).put(props.urls.update, { preserveScroll: true });
};

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('shipping::methods.edit_title', { name: method.name })" :breadcrumbs="breadcrumbs" wide>
        <template #actions>
            <Button variant="ghost" icon="trash" @click="deleting = true">{{ t('common.delete') }}</Button>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <div data-screen-label="Edit shipping method">
            <Section :title="t('shipping::methods.section_details')">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <FieldLabel required>{{ t('shipping::methods.field_name') }}</FieldLabel>
                        <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                        <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <FieldLabel required>{{ t('shipping::methods.field_code') }}</FieldLabel>
                        <TextInput v-model="form.code" mono :invalid="!!form.errors.code" />
                        <div v-if="form.errors.code" class="mt-1 text-[11px] text-danger">{{ form.errors.code }}</div>
                        <div v-else class="mt-1 text-[11px] text-ink-500">{{ t('shipping::methods.code_hint') }}</div>
                    </div>
                    <div>
                        <FieldLabel>{{ t('shipping::methods.field_driver') }}</FieldLabel>
                        <Select v-model="form.driver" :invalid="!!form.errors.driver">
                            <option v-for="driver in drivers" :key="driver.key" :value="driver.key">{{ driver.label }}</option>
                        </Select>
                        <div v-if="form.errors.driver" class="mt-1 text-[11px] text-danger">{{ form.errors.driver }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <FieldLabel>{{ t('shipping::methods.field_description') }}</FieldLabel>
                        <RichTextEditor v-model="form.description" :invalid="!!form.errors.description" :aria-label="t('shipping::methods.field_description')" />
                        <div class="mt-1 text-[11px] text-ink-500">{{ t('shipping::methods.description_hint') }}</div>
                    </div>
                </div>
            </Section>

            <Section :title="t('shipping::methods.section_driver')">
                <template #desc>{{ t(driverDescriptionKey) }}</template>
                <DriverSettings v-model="driverData" :driver="form.driver" :currencies="currencies" :errors="form.errors" />
            </Section>

            <Section :title="t('shipping::methods.section_constraints')">
                <template #desc>{{ t('shipping::methods.constraints_desc') }}</template>
                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <FieldLabel>{{ t('shipping::methods.field_weight_unit') }}</FieldLabel>
                        <Select v-model="form.weight_unit">
                            <option value="">{{ t('shipping::methods.no_weight_restriction') }}</option>
                            <option v-for="unit in weightUnits" :key="unit" :value="unit">{{ unit }}</option>
                        </Select>
                    </div>
                    <div>
                        <FieldLabel :required="!!form.weight_unit">{{ t('shipping::methods.field_min_weight') }}</FieldLabel>
                        <TextInput v-model="form.min_weight" type="number" min="0" step="0.001" :disabled="!form.weight_unit" :invalid="!!form.errors.min_weight">
                            <template v-if="form.weight_unit" #suffix><span class="text-[11px] text-ink-500">{{ form.weight_unit }}</span></template>
                        </TextInput>
                        <div v-if="form.errors.min_weight" class="mt-1 text-[11px] text-danger">{{ form.errors.min_weight }}</div>
                    </div>
                    <div>
                        <FieldLabel :required="!!form.weight_unit">{{ t('shipping::methods.field_max_weight') }}</FieldLabel>
                        <TextInput v-model="form.max_weight" type="number" min="0" step="0.001" :disabled="!form.weight_unit" :invalid="!!form.errors.max_weight">
                            <template v-if="form.weight_unit" #suffix><span class="text-[11px] text-ink-500">{{ form.weight_unit }}</span></template>
                        </TextInput>
                        <div v-if="form.errors.max_weight" class="mt-1 text-[11px] text-danger">{{ form.errors.max_weight }}</div>
                    </div>
                </div>
                <label class="mt-4 flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.stock_available" @toggle="form.stock_available = !form.stock_available" />
                    <span class="text-[12.5px] text-ink-900 font-medium">{{ t('shipping::methods.field_stock_available') }}</span>
                </label>
            </Section>

            <Section :title="t('shipping::methods.section_schedule')">
                <template #desc>{{ t('shipping::methods.schedule_desc') }}</template>
                <ScheduleGrid v-model="form.driver_data.schedule" :errors="form.errors" />
            </Section>

            <Section :title="t('shipping::methods.section_customer_groups')">
                <template #desc>{{ t('shipping::methods.customer_groups_desc') }}</template>
                <CustomerGroupAvailability v-model="form.customer_groups" :errors="form.errors" />
            </Section>
        </div>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('shipping::methods.confirm_delete_title')"
        :description="t('shipping::methods.confirm_delete_body', { name: method.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
