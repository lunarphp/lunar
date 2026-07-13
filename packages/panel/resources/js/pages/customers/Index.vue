<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Button from '../../components/Button.vue';
import DataTable from '../../components/DataTable.vue';
import { type RowAction } from '../../components/RowActions.vue';
import Icon from '../../components/Icon.vue';
import Pagination from '../../components/Pagination.vue';
import PageEmpty from '../../components/PageEmpty.vue';
import Select from '../../components/Select.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import TextInput from '../../components/TextInput.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';

interface CustomerGroupOption {
    id: number;
    name: string;
}

interface CustomerRow {
    id: number;
    full_name: string;
    company_name: string | null;
    account_ref: string | null;
    created_at: string;
    customer_groups: CustomerGroupOption[];
    edit_url: string;
    // Extension-contributed columns land here under their own key.
    [key: string]: unknown;
}

interface CustomerColumn {
    key: string;
    label: string;
    width?: string;
    align?: 'left' | 'right' | 'center';
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    customers: Paginated<CustomerRow>;
    columns: CustomerColumn[];
    tableActions: RowAction[];
    customerGroups: CustomerGroupOption[];
    filters: { q?: string; customer_group_id?: string | number; sort?: string; direction?: string };
    urls: { index: string; create: string };
}>();

const flashSuccess = computed(() => (usePage().props.flash as { success?: string } | undefined)?.success);

const q = ref(props.filters.q ?? '');
const customerGroupId = ref(props.filters.customer_group_id ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

const sortOptions: { value: string; label: string }[] = [
    { value: 'created_at', label: 'Recently created' },
    { value: 'first_name', label: 'First name' },
    { value: 'last_name', label: 'Last name' },
    { value: 'company_name', label: 'Company name' },
];

const reload = (): void => {
    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            customer_group_id: customerGroupId.value || undefined,
            sort: sort.value,
            direction: direction.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const toggleDirection = (): void => {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    reload();
};

const hasActiveFilters = computed(() => !!q.value.trim() || !!customerGroupId.value);
const clearFilters = (): void => {
    q.value = '';
    customerGroupId.value = '';
    reload();
};

const initials = (name: string): string =>
    name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

const formatDate = (value: string): string => new Date(value).toLocaleDateString();
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Customers" class="contents">
            <!-- Hero header -->
            <div class="flex items-start gap-3 sm:gap-4 px-4 sm:px-5 lg:px-7 pt-[18px] pb-3.5 border-b border-line bg-paper">
                <div class="w-11 h-11 rounded-md overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700">
                    <Icon name="users" />
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="m-0 text-lg sm:text-xl font-semibold tracking-[-0.015em] truncate">Customers</h1>
                    <div class="text-xs text-ink-500 mt-[3px] max-w-[640px]">
                        Everyone who's registered or been invited to a B2B account. Manage groups and keep contact details current.
                    </div>
                </div>
                <div class="hidden sm:flex gap-1.5 shrink-0">
                    <Link :href="urls.create">
                        <Button variant="primary" icon="plus">Add customer</Button>
                    </Link>
                </div>
            </div>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <div v-if="flashSuccess" class="mb-4 rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink">
                    {{ flashSuccess }}
                </div>

                <!-- Toolbar -->
                <div class="flex flex-wrap items-end gap-2 mb-4">
                    <div class="flex-1 max-w-[280px] min-w-[180px]">
                        <TextInput v-model="q" placeholder="Name, company, tax ID, account ref…" @keyup.enter="reload">
                            <template #prefix><Icon name="search" cls="sm" /></template>
                        </TextInput>
                    </div>
                    <div class="w-48">
                        <Select v-model="customerGroupId" @change="reload">
                            <option value="">All groups</option>
                            <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                        </Select>
                    </div>
                    <div class="w-44">
                        <Select v-model="sort" @change="reload">
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </Select>
                    </div>
                    <Button variant="ghost" :aria-label="direction === 'asc' ? 'Sort ascending' : 'Sort descending'" @click="toggleDirection">
                        {{ direction === 'asc' ? 'Asc' : 'Desc' }}
                    </Button>
                    <Button @click="reload">Search</Button>
                    <div class="flex-1" />
                    <span class="text-[11.5px] text-ink-500 whitespace-nowrap">{{ customers.total }} total</span>
                    <Link :href="urls.create" class="sm:hidden">
                        <Button variant="primary" icon="plus">New</Button>
                    </Link>
                </div>

                <DataTable :columns="props.columns" :rows="customers.data" :row-to="(row) => row.edit_url as string" :row-actions="props.tableActions">
                    <template #empty>
                        <PageEmpty title="No customers match these filters">
                            Try clearing the search or filters to see more customers.
                            <div v-if="hasActiveFilters" class="mt-3">
                                <Button @click="clearFilters">Clear filters</Button>
                            </div>
                        </PageEmpty>
                    </template>

                    <template #cell-full_name="{ row }">
                        <div class="min-w-0 flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full border border-line bg-surface-2 grid place-items-center text-ink-700 text-[10.5px] font-semibold shrink-0">
                                {{ initials(row.full_name as string) }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-[12.5px] text-ink-900 truncate">{{ row.full_name }}</div>
                                <div v-if="row.account_ref" class="text-[11px] text-ink-500 truncate font-mono">{{ row.account_ref }}</div>
                            </div>
                        </div>
                    </template>

                    <template #cell-company_name="{ value }">
                        <span v-if="value" class="text-[12.5px] text-ink-900 truncate">{{ value }}</span>
                        <span v-else class="text-[12.5px] text-ink-400">—</span>
                    </template>

                    <template #cell-customer_groups="{ value }">
                        <div class="min-w-0 flex flex-wrap gap-1">
                            <StatusBadge v-for="group in (value as CustomerGroupOption[])" :key="group.id" size="sm">{{ group.name }}</StatusBadge>
                            <span v-if="!(value as CustomerGroupOption[]).length" class="text-[12.5px] text-ink-400">—</span>
                        </div>
                    </template>

                    <template #cell-created_at="{ value }">
                        <span class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ formatDate(value as string) }}</span>
                    </template>
                </DataTable>

                <div class="mt-4">
                    <Pagination :meta="customers" />
                </div>
            </div>
        </div>
    </PanelLayout>
</template>
