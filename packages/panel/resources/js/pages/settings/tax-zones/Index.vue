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
import Toggle from '../../../components/Toggle.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type TaxZone = {
    id: number;
    name: string;
    zone_type: string;
    active: boolean;
    default: boolean;
    rates_count: number;
    urls: { edit: string };
};

type TaxZoneColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

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
    taxZones: Paginated<TaxZone>;
    columns: TaxZoneColumn[];
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

const rowTo = (row: Record<string, unknown>): string => (row as unknown as TaxZone).urls.edit;

const zoneTypeLabel = (zone: TaxZone): string => t(`tax_zones.type_${zone.zone_type}`);

const creating = ref(false);
const createForm = useForm({
    name: '',
    zone_type: 'country',
    active: true as boolean,
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
    <SettingsShell :title="t('tax_zones.title')" :description="t('tax_zones.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('tax_zones.create_tax_zone') }}</Button>
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
            :rows="taxZones.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-name="{ row }">
                <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as TaxZone).name }}</span>
                <StatusBadge v-if="(row as unknown as TaxZone).default" tone="sage" size="sm" class="ml-2">{{ t('tax_zones.default_badge') }}</StatusBadge>
            </template>
            <template #cell-zone_type="{ row }">
                <span class="text-xs text-ink-700">{{ zoneTypeLabel(row as unknown as TaxZone) }}</span>
            </template>
            <template #cell-rates_count="{ row }">
                <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ (row as unknown as TaxZone).rates_count }}</span>
            </template>
            <template #cell-status="{ row }">
                <StatusBadge :tone="(row as unknown as TaxZone).active ? 'sage' : 'archived'" size="sm" dot>
                    {{ (row as unknown as TaxZone).active ? t('common.active') : t('common.inactive') }}
                </StatusBadge>
            </template>
            <template #empty>
                <PageEmpty :title="t('tax_zones.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="taxZones" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('tax_zones.create_tax_zone')"
        :description="t('tax_zones.create_description')"
    >
        <div class="flex flex-col gap-3">
            <div>
                <FieldLabel required>{{ t('tax_zones.field_name') }}</FieldLabel>
                <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" :placeholder="t('tax_zones.name_placeholder')" />
                <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
            </div>
            <div>
                <FieldLabel>{{ t('tax_zones.field_type') }}</FieldLabel>
                <Select v-model="createForm.zone_type">
                    <option value="country">{{ t('tax_zones.type_country') }}</option>
                    <option value="state">{{ t('tax_zones.type_state') }}</option>
                    <option value="postcode">{{ t('tax_zones.type_postcode') }}</option>
                </Select>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <Toggle :on="createForm.active" @toggle="createForm.active = !createForm.active" />
                <span class="text-[12.5px] text-ink-900 font-medium">{{ t('common.active') }}</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <Toggle :on="createForm.default" @toggle="createForm.default = !createForm.default" />
                <span class="text-[12.5px] text-ink-900 font-medium">{{ t('tax_zones.default_zone') }}</span>
            </label>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
