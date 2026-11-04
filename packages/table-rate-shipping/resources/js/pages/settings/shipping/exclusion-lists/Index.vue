<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, DataTable, Dialog, FieldLabel, PageEmpty, Pagination, SettingsShell, TextInput } from '@lunarphp/panel';
import ExtensionFilters from '../../../../components/ExtensionFilters.vue';
import { noLayout } from '../../../../lib/layout';
import type { ExtensionFilter, Paginated, RowAction, TableColumn } from '../../../../lib/types';

defineOptions({ layout: noLayout });

type ExclusionList = {
    id: number;
    name: string;
    exclusions_count: number;
    zones_count: number;
    urls: { edit: string };
};

const props = defineProps<{
    lists: Paginated<ExclusionList>;
    columns: TableColumn[];
    tableActions: RowAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    urls: { index: string; store: string };
}>();

const { t } = useI18n();

const rowTo = (row: Record<string, unknown>): string => (row as unknown as ExclusionList).urls.edit;

const creating = ref(false);
const createForm = useForm({ name: '' });

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
    <SettingsShell :title="t('shipping::exclusion_lists.title')" :description="t('shipping::exclusion_lists.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('shipping::exclusion_lists.create_list') }}</Button>
        </template>

        <div data-screen-label="Shipping exclusion lists">
            <ExtensionFilters :filters="tableFilters" :values="tableFilterValues" :index-url="urls.index" />

            <DataTable :columns="columns" :rows="lists.data" :row-to="rowTo" :row-actions="tableActions">
                <template #cell-name="{ row }">
                    <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as ExclusionList).name }}</span>
                </template>
                <template #cell-exclusions_count="{ value }">
                    <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ value }}</span>
                </template>
                <template #cell-zones_count="{ value }">
                    <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ value }}</span>
                </template>
                <template #empty>
                    <PageEmpty :title="t('shipping::exclusion_lists.empty_title')" />
                </template>
            </DataTable>

            <div class="mt-4">
                <Pagination :meta="lists" />
            </div>
        </div>
    </SettingsShell>

    <Dialog v-model:open="creating" :title="t('shipping::exclusion_lists.create_list')" :description="t('shipping::exclusion_lists.create_description')">
        <div>
            <FieldLabel required>{{ t('shipping::exclusion_lists.field_name') }}</FieldLabel>
            <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('shipping::exclusion_lists.name_placeholder')" @keyup.enter="submitCreate" />
            <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
