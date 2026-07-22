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
import Icon from '../../../components/Icon.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Pagination from '../../../components/Pagination.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import ValuePreviewChip, { type PreviewValue } from '../../../components/ValuePreviewChip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type ProductOption = {
    id: number;
    name: string;
    handle: string | null;
    type: string;
    shared: boolean;
    values_count: number;
    values_preview: PreviewValue[];
    products_count: number;
    urls: { edit: string };
};

type OptionColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };
type TypeOption = { value: string; icon: string };
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
    productOptions: Paginated<ProductOption>;
    columns: OptionColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    typeOptions: TypeOption[];
    filters: { type: string | null; unused: boolean; sharedOnly: boolean };
    defaultLocale: string;
    urls: { index: string; store: string };
}>();

const { t, te } = useI18n();

const TYPE_ICONS: Record<string, string> = { text: 'type', colour: 'palette', swatch: 'image' };
const typeIcon = (type: string): string => TYPE_ICONS[type] ?? 'help';
const typeLabel = (type: string): string => {
    const key = `product_options.type_${type}`;

    return te(key) ? t(key) : type;
};
const typeTone = (type: string): 'neutral' | 'sage' | 'warn' =>
    type === 'text' ? 'neutral' : props.typeOptions.some((option) => option.value === type) ? 'sage' : 'warn';

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

// --- First-party filters (shared + type + unused), pushed as query params ---
const sharedOnly = ref<boolean>(props.filters.sharedOnly);
const typeFilter = ref<string>(props.filters.type ?? '');
const unusedFilter = ref<boolean>(props.filters.unused);

const typeFilterOptions = computed<FilterOption[]>(() => [
    { value: '', label: t('product_options.filter_all_types') },
    ...props.typeOptions.map((option) => ({ value: option.value, label: typeLabel(option.value) })),
]);

const applyFilters = (): void => {
    router.get(
        props.urls.index,
        {
            // Shared-only is the default, so only carry the param when off.
            shared: sharedOnly.value ? undefined : 0,
            type: typeFilter.value || undefined,
            unused: unusedFilter.value ? 1 : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch([sharedOnly, typeFilter, unusedFilter], applyFilters);

// --- Add-on filters ---
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

const rowTo = (row: Record<string, unknown>): string => (row as unknown as ProductOption).urls.edit;

// --- Create ---
const CREATE_TYPES = ['text', 'colour', 'swatch'] as const;
const creating = ref(false);
const createForm = useForm({ name: '', handle: '', type: 'text' as string });

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    creating.value = true;
};

const submitCreate = (): void => {
    createForm
        .transform((data) => ({
            name: { [props.defaultLocale]: data.name },
            handle: data.handle,
            type: data.type,
        }))
        .post(props.urls.store, {
            onSuccess: () => {
                createForm.reset();
                creating.value = false;
            },
        });
};
</script>

<template>
    <SettingsShell :title="t('product_options.title')" :description="t('product_options.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('product_options.create_option') }}</Button>
        </template>

        <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
            <template v-if="!(hasBulkActions && selected.length)">
                <label class="flex items-center gap-2 px-3 h-[34px] border border-line rounded-md cursor-pointer hover:bg-surface-2">
                    <Toggle :on="sharedOnly" @toggle="sharedOnly = !sharedOnly" />
                    <span class="text-[12px] text-ink-700 whitespace-nowrap">{{ t('product_options.filter_shared') }}</span>
                </label>
                <FilterDropdown
                    v-model="typeFilter"
                    :label="t('product_options.column_type')"
                    :options="typeFilterOptions"
                    default-value=""
                />
                <label class="flex items-center gap-2 px-3 h-[34px] border border-line rounded-md cursor-pointer hover:bg-surface-2">
                    <Toggle :on="unusedFilter" @toggle="unusedFilter = !unusedFilter" />
                    <span class="text-[12px] text-ink-700 whitespace-nowrap">{{ t('product_options.filter_unused') }}</span>
                </label>
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
            :rows="productOptions.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-name="{ row }">
                <div class="min-w-0">
                    <div class="text-[12.5px] text-ink-900 font-medium truncate">
                        {{ (row as unknown as ProductOption).name }}
                        <StatusBadge v-if="(row as unknown as ProductOption).shared" tone="sage" size="sm" class="ml-1.5">{{ t('product_options.shared_badge') }}</StatusBadge>
                    </div>
                    <div class="text-[11px] text-ink-500 font-mono truncate">{{ (row as unknown as ProductOption).handle || '—' }}</div>
                </div>
            </template>
            <template #cell-type="{ row }">
                <StatusBadge :tone="typeTone((row as unknown as ProductOption).type)" size="sm" :icon="typeIcon((row as unknown as ProductOption).type)">
                    {{ typeLabel((row as unknown as ProductOption).type) }}
                </StatusBadge>
            </template>
            <template #cell-values="{ row }">
                <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                    <ValuePreviewChip
                        v-for="(value, index) in (row as unknown as ProductOption).values_preview"
                        :key="index"
                        :type="(row as unknown as ProductOption).type"
                        :value="value"
                        :size="24"
                    />
                    <span
                        v-if="(row as unknown as ProductOption).values_count > (row as unknown as ProductOption).values_preview.length"
                        class="inline-flex items-center h-[22px] px-2 rounded-full bg-canvas border border-line text-[11px] text-ink-500"
                    >+{{ (row as unknown as ProductOption).values_count - (row as unknown as ProductOption).values_preview.length }}</span>
                    <span v-if="!(row as unknown as ProductOption).values_count" class="text-[11px] text-ink-400 italic">{{ t('product_options.no_values_short') }}</span>
                </div>
            </template>
            <template #cell-products_count="{ row }">
                <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ (row as unknown as ProductOption).products_count }}</span>
            </template>
            <template #empty>
                <PageEmpty :title="t('product_options.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="productOptions" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('product_options.create_option')"
        :description="t('product_options.create_description')"
    >
        <div class="flex flex-col gap-4">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('product_options.field_name') }}</FieldLabel>
                    <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('product_options.name_placeholder')" />
                    <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel :hint="t('product_options.handle_hint')">{{ t('product_options.field_handle') }}</FieldLabel>
                    <TextInput v-model="createForm.handle" mono :invalid="!!createForm.errors.handle" :placeholder="t('product_options.handle_placeholder')" />
                    <div v-if="createForm.errors.handle" class="mt-1 text-[11px] text-danger">{{ createForm.errors.handle }}</div>
                </div>
            </div>
            <div>
                <FieldLabel>{{ t('product_options.field_type') }}</FieldLabel>
                <div class="grid sm:grid-cols-3 gap-2">
                    <button
                        v-for="type in CREATE_TYPES"
                        :key="type"
                        type="button"
                        :class="[
                            'flex items-center gap-2 px-2.5 py-2 border rounded-md text-[12px] transition-colors text-left',
                            createForm.type === type
                                ? 'border-ink-900/40 bg-surface-2 text-ink-900 font-medium'
                                : 'border-line bg-surface text-ink-700 hover:bg-surface-2',
                        ]"
                        @click="createForm.type = type"
                    >
                        <Icon :name="typeIcon(type)" cls="sm" />
                        <span>{{ typeLabel(type) }}</span>
                    </button>
                </div>
            </div>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
