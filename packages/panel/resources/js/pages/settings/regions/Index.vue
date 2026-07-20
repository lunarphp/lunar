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
import Select from '../../../components/Select.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Region = {
    id: number;
    name: string;
    handle: string;
    default: boolean;
    channel: string | null;
    currency: string | null;
    language: string | null;
    countries_count: number;
    urls: { edit: string };
};

type RegionColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

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
    regions: Paginated<Region>;
    columns: RegionColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    channels: { id: number; name: string }[];
    currencies: { id: number; code: string }[];
    languages: { id: number; code: string }[];
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

const rowTo = (row: Record<string, unknown>): string => (row as unknown as Region).urls.edit;

const creating = ref(false);
const createForm = useForm({
    name: '',
    channel_id: null as number | null,
    currency_id: null as number | null,
    language_id: null as number | null,
});

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    createForm.channel_id = props.channels[0]?.id ?? null;
    createForm.currency_id = props.currencies[0]?.id ?? null;
    createForm.language_id = props.languages[0]?.id ?? null;
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
    <SettingsShell :title="t('regions.title')" :description="t('regions.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('regions.create_region') }}</Button>
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
            :rows="regions.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-name="{ row }">
                <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as Region).name }}</span>
                <StatusBadge v-if="(row as unknown as Region).default" tone="sage" size="sm" class="ml-2">{{ t('regions.default_badge') }}</StatusBadge>
            </template>
            <template #cell-channel="{ row }">
                <span class="text-xs text-ink-700">{{ (row as unknown as Region).channel || '—' }}</span>
            </template>
            <template #cell-currency="{ row }">
                <span class="font-mono text-xs text-ink-700">{{ (row as unknown as Region).currency || '—' }}</span>
            </template>
            <template #cell-language="{ row }">
                <span class="font-mono text-xs text-ink-700">{{ (row as unknown as Region).language || '—' }}</span>
            </template>
            <template #cell-countries_count="{ row }">
                <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ (row as unknown as Region).countries_count }}</span>
            </template>
            <template #empty>
                <PageEmpty :title="t('regions.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="regions" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('regions.create_region')"
        :description="t('regions.create_description')"
    >
        <div class="flex flex-col gap-3">
            <div>
                <FieldLabel required>{{ t('regions.field_name') }}</FieldLabel>
                <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('regions.name_placeholder')" />
                <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
            </div>
            <div class="grid sm:grid-cols-3 gap-3">
                <div>
                    <FieldLabel required>{{ t('regions.field_channel') }}</FieldLabel>
                    <Select v-model="createForm.channel_id">
                        <option v-for="channel in channels" :key="channel.id" :value="channel.id">{{ channel.name }}</option>
                    </Select>
                    <div v-if="createForm.errors.channel_id" class="mt-1 text-[11px] text-danger">{{ createForm.errors.channel_id }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('regions.field_currency') }}</FieldLabel>
                    <Select v-model="createForm.currency_id">
                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.code }}</option>
                    </Select>
                    <div v-if="createForm.errors.currency_id" class="mt-1 text-[11px] text-danger">{{ createForm.errors.currency_id }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('regions.field_language') }}</FieldLabel>
                    <Select v-model="createForm.language_id">
                        <option v-for="language in languages" :key="language.id" :value="language.id">{{ language.code }}</option>
                    </Select>
                    <div v-if="createForm.errors.language_id" class="mt-1 text-[11px] text-danger">{{ createForm.errors.language_id }}</div>
                </div>
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
