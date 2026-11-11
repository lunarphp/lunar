<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, DataTable, Dialog, FieldLabel, PageEmpty, Pagination, Select, SettingsShell, TextInput } from '@lunarphp/panel';
import ExtensionFilters from '../../../../components/ExtensionFilters.vue';
import { noLayout } from '../../../../lib/layout';
import type { ExtensionFilter, Paginated, RowAction, TableColumn } from '../../../../lib/types';

defineOptions({ layout: noLayout });

type Zone = {
    id: number;
    name: string;
    type: string;
    rates_count: number;
    exclusion_lists_count: number;
    urls: { edit: string };
};

const props = defineProps<{
    zones: Paginated<Zone>;
    columns: TableColumn[];
    tableActions: RowAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    urls: { index: string; store: string };
}>();

const { t } = useI18n();

const rowTo = (row: Record<string, unknown>): string => (row as unknown as Zone).urls.edit;

const creating = ref(false);
const createForm = useForm({ name: '', type: 'countries' });

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    creating.value = true;
};

const submitCreate = (): void => {
    createForm.post(props.urls.store, { onSuccess: () => { creating.value = false; } });
};
</script>

<template>
    <SettingsShell :title="t('shipping::zones.title')" :description="t('shipping::zones.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('shipping::zones.create_zone') }}</Button>
        </template>

        <div data-screen-label="Shipping zones">
            <ExtensionFilters :filters="tableFilters" :values="tableFilterValues" :index-url="urls.index" />

            <DataTable :columns="columns" :rows="zones.data" :row-to="rowTo" :row-actions="tableActions">
                <template #cell-name="{ row }">
                    <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as Zone).name }}</span>
                </template>
                <template #cell-type="{ row }">
                    <span class="text-xs text-ink-700">{{ t(`shipping::zones.type_${(row as unknown as Zone).type}`) }}</span>
                </template>
                <template #cell-rates_count="{ value }">
                    <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ value }}</span>
                </template>
                <template #cell-exclusion_lists_count="{ value }">
                    <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ value }}</span>
                </template>
                <template #empty>
                    <PageEmpty :title="t('shipping::zones.empty_title')" />
                </template>
            </DataTable>

            <div class="mt-4">
                <Pagination :meta="zones" />
            </div>
        </div>
    </SettingsShell>

    <Dialog v-model:open="creating" :title="t('shipping::zones.create_zone')" :description="t('shipping::zones.create_description')">
        <div class="flex flex-col gap-3">
            <div>
                <FieldLabel required>{{ t('shipping::zones.field_name') }}</FieldLabel>
                <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('shipping::zones.name_placeholder')" />
                <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
            </div>
            <div>
                <FieldLabel>{{ t('shipping::zones.field_type') }}</FieldLabel>
                <Select v-model="createForm.type">
                    <option value="unrestricted">{{ t('shipping::zones.type_unrestricted') }}</option>
                    <option value="countries">{{ t('shipping::zones.type_countries') }}</option>
                    <option value="states">{{ t('shipping::zones.type_states') }}</option>
                    <option value="postcodes">{{ t('shipping::zones.type_postcodes') }}</option>
                </Select>
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
