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
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type StaffMember = {
    id: number;
    full_name: string;
    first_name: string;
    last_name: string;
    email: string;
    admin: boolean;
    roles: string[];
    urls: { edit: string };
};

type StaffColumn = { key: string; label: string; width?: string; align?: 'left' | 'right' | 'center' };

type ExtensionFilter = { key: string; label: string; component: string | null; options: Record<string, string> };

type RoleOption = { handle: string; label: string };

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
    staff: Paginated<StaffMember>;
    columns: StaffColumn[];
    tableActions: RowAction[];
    tableBulkActions: BulkAction[];
    tableFilters: ExtensionFilter[];
    tableFilterValues: Record<string, string>;
    roles: RoleOption[];
    filters: { q?: string };
    urls: { index: string; store: string };
}>();

const { t } = useI18n();

const selected = ref<(string | number)[]>([]);
const hasBulkActions = computed(() => props.tableBulkActions.length > 0);

const q = ref(props.filters.q ?? '');

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
            q: q.value || undefined,
            filter: Object.keys(active).length ? active : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(extensionFilterValues, reload);

// The search box debounces so a reload only fires once typing settles.
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const rowTo = (row: Record<string, unknown>): string => (row as unknown as StaffMember).urls.edit;

const creating = ref(false);
const createForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    admin: false as boolean,
    roles: [] as string[],
});

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    creating.value = true;
};

const toggleRole = (handle: string): void => {
    const index = createForm.roles.indexOf(handle);
    if (index >= 0) {
        createForm.roles.splice(index, 1);
    } else {
        createForm.roles.push(handle);
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
    <SettingsShell :title="t('staff.title')" :description="t('staff.description')" wide>
        <template #actions>
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">{{ t('staff.create_staff') }}</Button>
        </template>

        <!-- Toolbar: search + add-on filters, replaced in place by the bulk-action
             bar while rows are selected so the table below never shifts. -->
        <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
            <template v-if="!(hasBulkActions && selected.length)">
                <div class="max-w-[300px] w-full">
                    <TextInput v-model="q" :placeholder="t('staff.search_placeholder')" />
                </div>
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
            :rows="staff.data"
            :row-to="rowTo"
            :row-actions="props.tableActions"
            :selectable="hasBulkActions"
            :selected="selected"
            @update:selected="selected = $event"
        >
            <template #cell-full_name="{ row }">
                <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as StaffMember).full_name }}</span>
                <StatusBadge v-if="(row as unknown as StaffMember).admin" tone="sage" size="sm" class="ml-2">{{ t('staff.admin_badge') }}</StatusBadge>
            </template>
            <template #cell-email="{ row }">
                <span class="text-xs text-ink-700">{{ (row as unknown as StaffMember).email }}</span>
            </template>
            <template #cell-roles="{ row }">
                <div class="flex flex-wrap gap-1">
                    <StatusBadge v-for="role in (row as unknown as StaffMember).roles" :key="role" tone="archived" size="sm">{{ role }}</StatusBadge>
                    <span v-if="!(row as unknown as StaffMember).roles.length" class="text-xs text-ink-500">—</span>
                </div>
            </template>
            <template #empty>
                <PageEmpty :title="t('staff.empty_title')" />
            </template>
        </DataTable>

        <div class="mt-4">
            <Pagination :meta="staff" />
        </div>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        :title="t('staff.create_staff')"
        :description="t('staff.create_description')"
    >
        <div class="flex flex-col gap-3">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('staff.field_first_name') }}</FieldLabel>
                    <TextInput v-model="createForm.first_name" :invalid="!!createForm.errors.first_name" />
                    <div v-if="createForm.errors.first_name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.first_name }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('staff.field_last_name') }}</FieldLabel>
                    <TextInput v-model="createForm.last_name" :invalid="!!createForm.errors.last_name" />
                    <div v-if="createForm.errors.last_name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.last_name }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('staff.field_email') }}</FieldLabel>
                    <TextInput v-model="createForm.email" type="email" :invalid="!!createForm.errors.email" />
                    <div v-if="createForm.errors.email" class="mt-1 text-[11px] text-danger">{{ createForm.errors.email }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('staff.field_password') }}</FieldLabel>
                    <TextInput v-model="createForm.password" type="password" :invalid="!!createForm.errors.password" />
                    <div v-if="createForm.errors.password" class="mt-1 text-[11px] text-danger">{{ createForm.errors.password }}</div>
                </div>
            </div>
            <div v-if="roles.length">
                <FieldLabel>{{ t('staff.field_roles') }}</FieldLabel>
                <div class="flex flex-wrap gap-x-4 gap-y-2 mt-1">
                    <label v-for="role in roles" :key="role.handle" class="flex items-center gap-2 cursor-pointer">
                        <Checkbox :model-value="createForm.roles.includes(role.handle)" @update:model-value="toggleRole(role.handle)" />
                        <span class="text-[12.5px] text-ink-900">{{ role.label }}</span>
                    </label>
                </div>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <Toggle :on="createForm.admin" @toggle="createForm.admin = !createForm.admin" />
                <div>
                    <div class="text-[12.5px] text-ink-900 font-medium">{{ t('staff.admin') }}</div>
                    <div class="text-[11px] text-ink-500">{{ t('staff.admin_hint') }}</div>
                </div>
            </label>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">{{ t('common.create') }}</Button>
        </template>
    </Dialog>
</template>
