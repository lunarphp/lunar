<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Button from '../../components/Button.vue';
import FieldLabel from '../../components/FieldLabel.vue';
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
    customerGroups: CustomerGroupOption[];
    filters: { q?: string; customer_group_id?: string | number; sort?: string; direction?: string };
    urls: { index: string; create: string };
}>();

const flashSuccess = computed(() => (usePage().props.flash as { success?: string } | undefined)?.success);

const q = ref(props.filters.q ?? '');
const customerGroupId = ref(props.filters.customer_group_id ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

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

const sortBy = (column: string): void => {
    direction.value = sort.value === column && direction.value === 'asc' ? 'desc' : 'asc';
    sort.value = column;
    reload();
};

const columns: { key: string; label: string }[] = [
    { key: 'first_name', label: 'Name' },
    { key: 'company_name', label: 'Company' },
    { key: 'created_at', label: 'Created' },
];
</script>

<template>
    <PanelLayout>
    <div class="bg-canvas font-sans py-10">
        <div class="mx-auto flex max-w-5xl flex-col gap-6 px-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">Customers</h1>
                <Link :href="urls.create">
                    <Button variant="primary" icon="plus">New customer</Button>
                </Link>
            </div>

            <div v-if="flashSuccess" class="rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink">
                {{ flashSuccess }}
            </div>

            <div class="flex items-end gap-3">
                <form class="flex-1" @submit.prevent="reload">
                    <FieldLabel>Search</FieldLabel>
                    <TextInput v-model="q" placeholder="Name, company, tax ID, account ref…" @keyup.enter="reload" />
                </form>
                <div class="w-56">
                    <FieldLabel>Customer group</FieldLabel>
                    <select
                        v-model="customerGroupId"
                        class="h-8 w-full rounded-md border border-line-strong bg-surface px-2.5 text-[13px] text-ink-900 outline-none focus:border-sage focus:ring-3 focus:ring-sage/35"
                        @change="reload"
                    >
                        <option value="">All groups</option>
                        <option v-for="group in customerGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </select>
                </div>
                <Button @click="reload">Search</Button>
            </div>

            <div class="overflow-x-auto rounded-lg border border-line bg-paper">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-line text-[11px] uppercase tracking-wide text-ink-500">
                            <th
                                v-for="column in columns"
                                :key="column.key"
                                class="cursor-pointer select-none px-4 py-2 font-medium hover:text-ink-900"
                                @click="sortBy(column.key)"
                            >
                                {{ column.label }}
                                <span v-if="sort === column.key">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                            </th>
                            <th class="px-4 py-2 font-medium">Groups</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="customer in customers.data"
                            :key="customer.id"
                            class="border-b border-line last:border-0 hover:bg-surface-2"
                        >
                            <td class="px-4 py-2.5">
                                <Link :href="customer.edit_url" class="font-medium text-ink-900 hover:underline">
                                    {{ customer.full_name }}
                                </Link>
                                <div v-if="customer.account_ref" class="text-[11px] text-ink-400">{{ customer.account_ref }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-ink-700">{{ customer.company_name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-ink-500">{{ new Date(customer.created_at).toLocaleDateString() }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="group in customer.customer_groups"
                                        :key="group.id"
                                        class="rounded-full border border-line-strong bg-surface-2 px-2 py-0.5 text-[11px] text-ink-700"
                                    >{{ group.name }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!customers.data.length">
                            <td colspan="4" class="px-4 py-8 text-center text-ink-400">No customers found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between text-[12px] text-ink-500">
                <span v-if="customers.total">Showing {{ customers.from }}–{{ customers.to }} of {{ customers.total }}</span>
                <div class="flex gap-2">
                    <Button
                        size="sm"
                        icon="chevronLeft"
                        :disabled="!customers.prev_page_url"
                        @click="customers.prev_page_url && router.get(customers.prev_page_url, {}, { preserveState: true, preserveScroll: true })"
                    />
                    <span class="flex items-center px-2">Page {{ customers.current_page }} / {{ customers.last_page }}</span>
                    <Button
                        size="sm"
                        icon="chevronRight"
                        :disabled="!customers.next_page_url"
                        @click="customers.next_page_url && router.get(customers.next_page_url, {}, { preserveState: true, preserveScroll: true })"
                    />
                </div>
            </div>
        </div>
    </div>
    </PanelLayout>
</template>
