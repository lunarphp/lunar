<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BulkActionsToolbar, { type BulkAction } from '../../../components/BulkActionsToolbar.vue';
import Button from '../../../components/Button.vue';
import Checkbox from '../../../components/Checkbox.vue';
import DataTable from '../../../components/DataTable.vue';
import FilterDropdown, { type FilterOption } from '../../../components/FilterDropdown.vue';
import { type RowAction } from '../../../components/RowActions.vue';
import Dialog from '../../../components/Dialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Pagination from '../../../components/Pagination.vue';
import Select from '../../../components/Select.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type AttributeRow = {
    id: number;
    name: string;
    handle: string;
    type: string;
    group: string | null;
    required: boolean;
    searchable: boolean;
    filterable: boolean;
    system: boolean;
    urls: { edit: string };
};

type AttributeColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

type ExtensionFilter = { key: string; label: string; component: string | null; options: Record<string, string> };

type Option = { value: string; label: string };
type NamedOption = { id: number; name: string };

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
    attributes: Paginated<AttributeRow>;
    columns: AttributeColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    attributeGroups: NamedOption[];
    fieldTypes: Option[];
    modelTypes: Option[];
    filters: { attribute_group_id?: string | number };
    urls: { index: string; store: string };
}>();

const { t } = useI18n();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

const groupFilter = ref<string | number>(props.filters.attribute_group_id ?? 'all');

const groupOptions = computed<FilterOption[]>(() => [
    { value: 'all', label: t('attributes_settings.all_groups') },
    ...props.attributeGroups.map((group) => ({ value: group.id, label: group.name })),
]);

// Add-on filter state, seeded from the server's current values ('' = off).
const extensionFilterValues = reactive<Record<string, string>>({ ...props.tableFilterValues });

const renderableExtensionFilters = computed(() =>
    props.tableFilters.filter((filter) => Object.keys(filter.options).length > 0));

const extensionFilterOptions = (filter: ExtensionFilter): FilterOption[] => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];

const reload = (): void => {
    const active = Object.fromEntries(Object.entries(extensionFilterValues).filter(([, value]) => value !== ''));

    router.get(
        props.urls.index,
        {
            attribute_group_id: groupFilter.value === 'all' ? undefined : groupFilter.value,
            filter: Object.keys(active).length ? active : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch([groupFilter], reload);
watch(extensionFilterValues, reload);

const rowTo = (row: Record<string, unknown>): string => (row as unknown as AttributeRow).urls.edit;

const creating = ref(false);
const createForm = useForm({
    name: '',
    handle: '',
    attribute_group_id: null as number | null,
    type: 'text',
    model_types: [] as string[],
});

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    creating.value = true;
};

const toggleModelType = (value: string): void => {
    const index = createForm.model_types.indexOf(value);
    if (index >= 0) {
        createForm.model_types.splice(index, 1);
    } else {
        createForm.model_types.push(value);
    }
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
    <SettingsShell :title="t('attributes_settings.title')" :description="t('attributes_settings.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('attributes_settings.create_attribute') }}</Button>
        </template>

        <!-- Toolbar: group filter + add-on filters, replaced in place by the
             bulk-action bar while rows are selected so the table never shifts. -->
        <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
            <template v-if="!(hasBulkActions && selected.length)">
                <FilterDropdown v-model="groupFilter" :label="t('attributes_settings.column_group')" :options="groupOptions" default-value="all" />
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
            :rows="attributes.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-name="{ row }">
                <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as AttributeRow).name }}</span>
                <StatusBadge v-if="(row as unknown as AttributeRow).system" tone="archived" size="sm" class="ml-2">{{ t('attributes_settings.system_badge') }}</StatusBadge>
            </template>
            <template #cell-handle="{ row }">
                <span class="font-mono text-xs text-ink-700">{{ (row as unknown as AttributeRow).handle }}</span>
            </template>
            <template #cell-group="{ row }">
                <span class="text-xs text-ink-700">{{ (row as unknown as AttributeRow).group || '—' }}</span>
            </template>
            <template #cell-type="{ row }">
                <span class="text-xs text-ink-700">{{ t(`attributes_settings.type_${(row as unknown as AttributeRow).type}`) }}</span>
            </template>
            <template #cell-flags="{ row }">
                <div class="flex gap-1">
                    <StatusBadge v-if="(row as unknown as AttributeRow).required" tone="sage" size="sm">{{ t('attributes_settings.flag_required') }}</StatusBadge>
                    <StatusBadge v-if="(row as unknown as AttributeRow).searchable" tone="archived" size="sm">{{ t('attributes_settings.flag_searchable') }}</StatusBadge>
                    <StatusBadge v-if="(row as unknown as AttributeRow).filterable" tone="archived" size="sm">{{ t('attributes_settings.flag_filterable') }}</StatusBadge>
                </div>
            </template>
            <template #empty>
                <PageEmpty :title="t('attributes_settings.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="attributes" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('attributes_settings.create_attribute')"
        :description="t('attributes_settings.create_description')"
    >
        <div class="flex flex-col gap-3">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('attributes_settings.field_name') }}</FieldLabel>
                    <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('attributes_settings.name_placeholder')" />
                    <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel :hint="t('attributes_settings.handle_hint')">{{ t('attributes_settings.field_handle') }}</FieldLabel>
                    <TextInput v-model="createForm.handle" mono :invalid="!!createForm.errors.handle" :placeholder="t('attributes_settings.handle_placeholder')" />
                    <div v-if="createForm.errors.handle" class="mt-1 text-[11px] text-danger">{{ createForm.errors.handle }}</div>
                </div>
                <div>
                    <FieldLabel>{{ t('attributes_settings.field_group') }}</FieldLabel>
                    <Select v-model="createForm.attribute_group_id">
                        <option :value="null">{{ t('attributes_settings.no_group') }}</option>
                        <option v-for="group in attributeGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </Select>
                </div>
                <div>
                    <FieldLabel required>{{ t('attributes_settings.field_type') }}</FieldLabel>
                    <Select v-model="createForm.type">
                        <option v-for="fieldType in fieldTypes" :key="fieldType.value" :value="fieldType.value">{{ fieldType.label }}</option>
                    </Select>
                    <div v-if="createForm.errors.type" class="mt-1 text-[11px] text-danger">{{ createForm.errors.type }}</div>
                </div>
            </div>
            <div>
                <FieldLabel required>{{ t('attributes_settings.field_model_types') }}</FieldLabel>
                <div class="flex flex-wrap gap-x-4 gap-y-2 mt-1">
                    <label v-for="modelType in modelTypes" :key="modelType.value" class="flex items-center gap-2 cursor-pointer">
                        <Checkbox :model-value="createForm.model_types.includes(modelType.value)" @update:model-value="toggleModelType(modelType.value)" />
                        <span class="text-[12.5px] text-ink-900">{{ modelType.label }}</span>
                    </label>
                </div>
                <div v-if="createForm.errors.model_types" class="mt-1 text-[11px] text-danger">{{ createForm.errors.model_types }}</div>
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
