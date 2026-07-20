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
import Toggle from '../../../components/Toggle.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Language = {
    id: number;
    code: string;
    name: string;
    default: boolean;
    urls: { edit: string };
};

type LanguageColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

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
    languages: Paginated<Language>;
    columns: LanguageColumn[];
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

const rowTo = (row: Record<string, unknown>): string => (row as unknown as Language).urls.edit;

const creating = ref(false);
const createForm = useForm({
    code: '',
    name: '',
    default: false as boolean,
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
    <SettingsShell :title="t('languages.title')" :description="t('languages.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('languages.create_language') }}</Button>
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
            :rows="languages.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-code="{ row }">
                <span class="font-mono text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as Language).code }}</span>
            </template>
            <template #cell-name="{ row }">
                <span class="text-[12.5px] text-ink-900">{{ (row as unknown as Language).name }}</span>
            </template>
            <template #cell-flags="{ row }">
                <StatusBadge v-if="(row as unknown as Language).default" tone="sage" size="sm">{{ t('languages.default_badge') }}</StatusBadge>
            </template>
            <template #empty>
                <PageEmpty :title="t('languages.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="languages" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('languages.create_language')"
        :description="t('languages.create_description')"
    >
        <div class="flex flex-col gap-3">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required :hint="t('languages.code_hint')">{{ t('languages.field_code') }}</FieldLabel>
                    <TextInput v-model="createForm.code" mono :invalid="!!createForm.errors.code" placeholder="en" />
                    <div v-if="createForm.errors.code" class="mt-1 text-[11px] text-danger">{{ createForm.errors.code }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('languages.field_name') }}</FieldLabel>
                    <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('languages.name_placeholder')" />
                    <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <Toggle :on="createForm.default" @toggle="createForm.default = !createForm.default" />
                <span class="text-[12.5px] text-ink-900 font-medium">{{ t('languages.default_language') }}</span>
            </label>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
