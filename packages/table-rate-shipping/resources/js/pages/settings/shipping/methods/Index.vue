<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, DataTable, Dialog, FieldLabel, PageEmpty, Pagination, Select, SettingsShell, TextInput } from '@lunarphp/panel';
import ExtensionFilters from '../../../../components/ExtensionFilters.vue';
import { noLayout } from '../../../../lib/layout';
import type { ExtensionFilter, Paginated, RowAction, TableColumn } from '../../../../lib/types';

defineOptions({ layout: noLayout });

type Method = {
    id: number;
    name: string;
    code: string;
    driver: string;
    driver_label: string;
    enabled_groups_count: number;
    groups_count: number;
    urls: { edit: string };
};

const props = defineProps<{
    methods: Paginated<Method>;
    drivers: { key: string; label: string }[];
    columns: TableColumn[];
    tableActions: RowAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    urls: { index: string; store: string };
}>();

const { t } = useI18n();

const rowTo = (row: Record<string, unknown>): string => (row as unknown as Method).urls.edit;

const creating = ref(false);
const codeEdited = ref(false);
// `data` is reserved by useForm, so the charge basis travels flat and is
// nested back under data on submit.
const createForm = useForm({ name: '', code: '', driver: 'ship-by', charge_by: 'cart_total' });

// The code follows the name until staff edit it, like a channel handle.
watch(() => createForm.name, (name) => {
    if (!codeEdited.value) {
        createForm.code = name.trim().toUpperCase().replace(/[^A-Z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    codeEdited.value = false;
    creating.value = true;
};

const submitCreate = (): void => {
    createForm
        .transform((data) => ({ name: data.name, code: data.code, driver: data.driver, data: { charge_by: data.charge_by } }))
        .post(props.urls.store, { onSuccess: () => { creating.value = false; } });
};
</script>

<template>
    <SettingsShell :title="t('shipping::methods.title')" :description="t('shipping::methods.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('shipping::methods.create_method') }}</Button>
        </template>

        <div data-screen-label="Shipping methods">
            <ExtensionFilters :filters="tableFilters" :values="tableFilterValues" :index-url="urls.index" />

            <DataTable :columns="columns" :rows="methods.data" :row-to="rowTo" :row-actions="tableActions">
                <template #cell-name="{ row }">
                    <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as Method).name }}</span>
                </template>
                <template #cell-code="{ value }">
                    <span class="text-xs font-mono text-ink-700">{{ value }}</span>
                </template>
                <template #cell-driver="{ row }">
                    <span class="text-xs text-ink-700">{{ (row as unknown as Method).driver_label }}</span>
                </template>
                <template #cell-availability="{ row }">
                    <span class="text-xs" :class="(row as unknown as Method).enabled_groups_count ? 'text-ink-700' : 'text-warn'">
                        {{ t('shipping::methods.availability_summary', { enabled: (row as unknown as Method).enabled_groups_count, total: (row as unknown as Method).groups_count }) }}
                    </span>
                </template>
                <template #empty>
                    <PageEmpty :title="t('shipping::methods.empty_title')" />
                </template>
            </DataTable>

            <div class="mt-4">
                <Pagination :meta="methods" />
            </div>
        </div>
    </SettingsShell>

    <Dialog v-model:open="creating" :title="t('shipping::methods.create_method')" :description="t('shipping::methods.create_description')">
        <div class="flex flex-col gap-3">
            <div>
                <FieldLabel required>{{ t('shipping::methods.field_name') }}</FieldLabel>
                <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('shipping::methods.name_placeholder')" />
                <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
            </div>
            <div>
                <FieldLabel required>{{ t('shipping::methods.field_code') }}</FieldLabel>
                <TextInput v-model="createForm.code" mono :invalid="!!createForm.errors.code" @update:model-value="codeEdited = true" />
                <div v-if="createForm.errors.code" class="mt-1 text-[11px] text-danger">{{ createForm.errors.code }}</div>
            </div>
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel>{{ t('shipping::methods.field_driver') }}</FieldLabel>
                    <Select v-model="createForm.driver" :invalid="!!createForm.errors.driver">
                        <option v-for="driver in drivers" :key="driver.key" :value="driver.key">{{ driver.label }}</option>
                    </Select>
                </div>
                <div v-if="createForm.driver === 'ship-by'">
                    <FieldLabel>{{ t('shipping::methods.field_charge_by') }}</FieldLabel>
                    <Select v-model="createForm.charge_by">
                        <option value="cart_total">{{ t('shipping::methods.charge_by_cart_total') }}</option>
                        <option value="weight">{{ t('shipping::methods.charge_by_weight') }}</option>
                    </Select>
                </div>
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
