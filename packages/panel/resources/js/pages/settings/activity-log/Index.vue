<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router, usePoll } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BulkActionsToolbar, { type BulkAction } from '../../../components/BulkActionsToolbar.vue';
import DataTable from '../../../components/DataTable.vue';
import FilterDropdown, { type FilterOption } from '../../../components/FilterDropdown.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Pagination from '../../../components/Pagination.vue';
import { type RowAction } from '../../../components/RowActions.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type ActivityRow = {
    id: number;
    description: string;
    event: string | null;
    subject_type: string | null;
    subject_id: number | string | null;
    causer_name: string | null;
    changes: Record<string, Record<string, unknown>>;
    created_at: string;
};

type ActivityColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

type ExtensionFilter = { key: string; label: string; component: string | null; options: Record<string, string> };

type Option = { value: string; label: string };

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
    activities: Paginated<ActivityRow>;
    columns: ActivityColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    subjectTypes: Option[];
    events: string[];
    filters: { subject_type?: string; event?: string };
    urls: { index: string };
}>();

const { t } = useI18n();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

const subjectType = ref(props.filters.subject_type ?? '');
const event = ref(props.filters.event ?? '');

// Add-on filter state, seeded from the server's current values ('' = off).
const extensionFilterValues = reactive<Record<string, string>>({ ...props.tableFilterValues });

const renderableExtensionFilters = computed(() =>
    props.tableFilters.filter((filter) => Object.keys(filter.options).length > 0));

const extensionFilterOptions = (filter: ExtensionFilter): FilterOption[] => [
    { value: '', label: t('common.all') },
    ...Object.entries(filter.options).map(([value, label]) => ({ value, label })),
];

const subjectTypeOptions: FilterOption[] = [
    { value: '', label: t('common.all') },
    ...props.subjectTypes.map((type) => ({ value: type.value, label: type.label })),
];

const eventOptions: FilterOption[] = [
    { value: '', label: t('common.all') },
    ...props.events.map((value) => ({ value, label: value })),
];

const reload = (): void => {
    const active = Object.fromEntries(Object.entries(extensionFilterValues).filter(([, value]) => value !== ''));

    router.get(
        props.urls.index,
        {
            subject_type: subjectType.value || undefined,
            event: event.value || undefined,
            filter: Object.keys(active).length ? active : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch([subjectType, event], reload);
watch(extensionFilterValues, reload);

// Live view: re-fetch only the paginated activities every 15s so new entries
// surface without a manual refresh. Inertia suspends polling while the tab is
// hidden and reloads preserve scroll/state and the current URL's filters/page.
usePoll(15000, { only: ['activities'] });

const eventTone = (row: ActivityRow): 'sage' | 'archived' | 'danger' => {
    if (row.event === 'created') return 'sage';
    if (row.event === 'deleted') return 'danger';
    return 'archived';
};

const formatWhen = (value: string): string => new Date(value).toLocaleString();
</script>

<template>
    <SettingsShell :title="t('activity_log.title')" :description="t('activity_log.description')" wide>
        <!-- Toolbar: first-party + add-on filters, replaced in place by the bulk-action
             bar while rows are selected so the table below never shifts. -->
        <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
            <template v-if="!(hasBulkActions && selected.length)">
                <FilterDropdown v-model="subjectType" :label="t('activity_log.column_subject')" :options="subjectTypeOptions" default-value="" />
                <FilterDropdown v-model="event" :label="t('activity_log.column_event')" :options="eventOptions" default-value="" />
                <!-- Add-on filters registered through the table extension resolver. -->
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
            :rows="activities.data"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-description="{ row }">
                <span class="text-[12.5px] text-ink-900">{{ (row as unknown as ActivityRow).description }}</span>
                <StatusBadge v-if="(row as unknown as ActivityRow).event" :tone="eventTone(row as unknown as ActivityRow)" size="sm" class="ml-2">
                    {{ (row as unknown as ActivityRow).event }}
                </StatusBadge>
            </template>
            <template #cell-subject="{ row }">
                <span v-if="(row as unknown as ActivityRow).subject_type" class="text-xs text-ink-700">
                    {{ (row as unknown as ActivityRow).subject_type }}
                    <span class="font-mono text-ink-500">#{{ (row as unknown as ActivityRow).subject_id }}</span>
                </span>
                <span v-else class="text-xs text-ink-500">—</span>
            </template>
            <template #cell-causer_name="{ row }">
                <span class="text-xs text-ink-700">{{ (row as unknown as ActivityRow).causer_name || t('activity_log.system') }}</span>
            </template>
            <template #cell-created_at="{ row }">
                <span class="text-xs text-ink-500 [font-variant-numeric:tabular-nums]">{{ formatWhen((row as unknown as ActivityRow).created_at) }}</span>
            </template>
            <template #empty>
                <PageEmpty :title="t('activity_log.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="activities" />
        </div>
    </SettingsShell>
</template>
