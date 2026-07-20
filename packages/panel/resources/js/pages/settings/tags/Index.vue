<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BulkActionsToolbar, { type BulkAction } from '../../../components/BulkActionsToolbar.vue';
import Button from '../../../components/Button.vue';
import DataTable from '../../../components/DataTable.vue';
import FilterDropdown, { type FilterOption } from '../../../components/FilterDropdown.vue';
import { type RowAction } from '../../../components/RowActions.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Pagination from '../../../components/Pagination.vue';
import TextInput from '../../../components/TextInput.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Tag = {
    id: number;
    value: string;
    usage_count: number;
    urls: { update: string; destroy: string };
};

type TagColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

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
    tags: Paginated<Tag>;
    columns: TagColumn[];
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

// Inline create row above the table, following the prototype.
const createForm = useForm({ value: '' });

const submitCreate = (): void => {
    createForm.post(props.urls.store, {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

// One row at a time flips into inline edit mode.
const editingId = ref<number | null>(null);
const editValue = ref('');
const editErrors = ref<Record<string, string>>({});

const startEdit = (tag: Tag): void => {
    editingId.value = tag.id;
    editValue.value = tag.value;
    editErrors.value = {};
};

const cancelEdit = (): void => {
    editingId.value = null;
};

const saveEdit = (tag: Tag): void => {
    router.put(tag.urls.update, { value: editValue.value }, {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
        },
        onError: (errors) => {
            editErrors.value = errors;
        },
    });
};
</script>

<template>
    <SettingsShell :title="t('tags.title')" :description="t('tags.description')" wide>
        <!-- Inline create -->
        <div class="bg-surface border border-line rounded-xl shadow-sm p-3 grid sm:grid-cols-[1fr_auto] gap-2 items-end mb-4">
            <div>
                <FieldLabel required>{{ t('tags.field_value') }}</FieldLabel>
                <TextInput
                    v-model="createForm.value"
                    :invalid="!!createForm.errors.value"
                    :placeholder="t('tags.value_placeholder')"
                    @keyup.enter="submitCreate"
                />
                <div v-if="createForm.errors.value" class="mt-1 text-[11px] text-danger">{{ createForm.errors.value }}</div>
            </div>
            <Button variant="primary" icon="plus" :disabled="createForm.processing" @click="submitCreate">{{ t('tags.add_tag') }}</Button>
        </div>

        <!-- Bulk-action bar shows while rows are selected. -->
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
            :rows="tags.data"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-value="{ row }">
                <template v-if="editingId === (row as unknown as Tag).id">
                    <div class="flex items-center gap-2" @click.stop>
                        <TextInput
                            v-model="editValue"
                            :invalid="!!editErrors.value"
                            @keyup.enter="saveEdit(row as unknown as Tag)"
                            @keyup.esc="cancelEdit"
                        />
                        <Button variant="ghost" size="sm" @click="cancelEdit">{{ t('common.cancel') }}</Button>
                        <Button variant="primary" size="sm" @click="saveEdit(row as unknown as Tag)">{{ t('common.save') }}</Button>
                    </div>
                    <div v-if="editErrors.value" class="mt-1 text-[11px] text-danger">{{ editErrors.value }}</div>
                </template>
                <template v-else>
                    <button
                        type="button"
                        class="text-[12.5px] text-ink-900 font-medium hover:underline"
                        @click.stop="startEdit(row as unknown as Tag)"
                    >
                        {{ (row as unknown as Tag).value }}
                    </button>
                </template>
            </template>
            <template #cell-usage_count="{ row }">
                <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ (row as unknown as Tag).usage_count }}</span>
            </template>
            <template #empty>
                <PageEmpty :title="t('tags.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="tags" />
        </div>
    </SettingsShell>
</template>
