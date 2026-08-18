<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BulkActionsToolbar, { type BulkAction } from '../../../components/BulkActionsToolbar.vue';
import Button from '../../../components/Button.vue';
import DataTable from '../../../components/DataTable.vue';
import FilterDropdown, { type FilterOption } from '../../../components/FilterDropdown.vue';
import { type RowAction } from '../../../components/RowActions.vue';
import Dialog from '../../../components/Dialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Pagination from '../../../components/Pagination.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type AttributeGroup = {
    id: number;
    name: string;
    handle: string;
    position: number;
    system: boolean;
    attributes_count: number;
    urls: { edit: string };
};

type GroupColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

type ExtensionFilter = { key: string; label: string; component: string | null; options: Record<string, string> };

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{
    attributeGroups: Paginated<AttributeGroup>;
    columns: GroupColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    urls: { index: string; store: string };
}>();

const { t } = useI18n();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

// Add-on filter state, seeded from the server's current values ('' = off).
const extensionFilterValues = reactive<Record<string, string>>({ ...props.tableFilterValues });

const renderableExtensionFilters = computed(() =>
    props.tableFilters.filter((filter) => Object.keys(filter.options).length > 0));

const extensionFilterOptions = (filter: ExtensionFilter): FilterOption[] => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];

watch(extensionFilterValues, () => {
    const active = Object.fromEntries(Object.entries(extensionFilterValues).filter(([, value]) => value !== ''));

    router.get(
        props.urls.index,
        { filter: Object.keys(active).length ? active : undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

const rowTo = (row: Record<string, unknown>): string => (row as unknown as AttributeGroup).urls.edit;

const creating = ref(false);
const createForm = useForm({
    name: '',
    handle: '',
});

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    creating.value = true;
};

const submitCreate = (): void => {
    createForm.post(props.urls.store, {
        onSuccess: () => {
            createForm.reset();
            creating.value = false;
        },
    });
};
</script>

<template>
    <SettingsShell :title="t('attribute_groups.title')" :description="t('attribute_groups.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('attribute_groups.create_group') }}</Button>
        </template>

        <!-- Toolbar: add-on filters, replaced in place by the bulk-action bar while
             rows are selected so the table below never shifts. -->
        <div
            v-if="renderableExtensionFilters.length || (hasBulkActions && selected.length)"
            class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]"
        >
            <template v-if="!(hasBulkActions && selected.length)">
                <FilterDropdown
                    v-for="filter in renderableExtensionFilters"
                    :key="filter.key"
                    v-model="extensionFilterValues[filter.key]"
                    :label="filter.label"
                    :options="extensionFilterOptions(filter)"
                    default-value=""
                />
            </template>

            <BulkActionsToolbar
                v-else
                :actions="props.tableBulkActions"
                :selected="selected"
                @clear="selected = []"
                @done="selected = []"
            />
        </div>

        <DataTable
            :columns="props.columns"
            :rows="attributeGroups.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-name="{ row }">
                <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as AttributeGroup).name }}</span>
                <StatusBadge v-if="(row as unknown as AttributeGroup).system" tone="archived" size="sm" class="ml-2">{{ t('attribute_groups.system_badge') }}</StatusBadge>
            </template>
            <template #cell-handle="{ row }">
                <span class="font-mono text-xs text-ink-700">{{ (row as unknown as AttributeGroup).handle }}</span>
            </template>
            <template #cell-attributes_count="{ row }">
                <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ (row as unknown as AttributeGroup).attributes_count }}</span>
            </template>
            <template #empty>
                <PageEmpty :title="t('attribute_groups.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="attributeGroups" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('attribute_groups.create_group')"
        :description="t('attribute_groups.create_description')"
    >
        <div class="flex flex-col gap-3">
            <div>
                <FieldLabel required>{{ t('attribute_groups.field_name') }}</FieldLabel>
                <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('attribute_groups.name_placeholder')" />
                <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
            </div>
            <div>
                <FieldLabel :hint="t('attribute_groups.handle_hint')">{{ t('attribute_groups.field_handle') }}</FieldLabel>
                <TextInput v-model="createForm.handle" mono :invalid="!!createForm.errors.handle" :placeholder="t('attribute_groups.handle_placeholder')" />
                <div v-if="createForm.errors.handle" class="mt-1 text-[11px] text-danger">{{ createForm.errors.handle }}</div>
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
