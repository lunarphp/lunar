<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import Button from '../../components/Button.vue';
import Combobox from '../../components/Combobox.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import Select from '../../components/Select.vue';
import TextInput from '../../components/TextInput.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';

interface CustomerGroupOption {
    id: number;
    name: string;
}

const props = defineProps<{
    customerGroups: CustomerGroupOption[];
    urls: { store: string; index: string };
}>();

const form = useForm({
    title: '',
    first_name: '',
    last_name: '',
    company_name: '',
    tax_identifier: '',
    account_ref: '',
    customer_group_ids: [] as number[],
});

const titleOptions = ['', 'Mr', 'Ms', 'Mrs', 'Mx', 'Dr'];

const groupName = (id: number): string => props.customerGroups.find((group) => group.id === id)?.name ?? String(id);

const addableGroupOptions = computed(() => {
    const taken = new Set(form.customer_group_ids);

    return props.customerGroups.filter((group) => !taken.has(group.id)).map((group) => ({ value: group.id, label: group.name }));
});

const addGroupSelection = ref<string | number | null>(null);
const onAddGroup = (value: string | number): void => {
    form.customer_group_ids.push(Number(value));
    addGroupSelection.value = null;
};
const removeGroup = (id: number): void => {
    form.customer_group_ids = form.customer_group_ids.filter((groupId) => groupId !== id);
};

const submit = (): void => {
    form.post(props.urls.store);
};
</script>

<template>
    <PanelLayout>
        <div data-screen-label="New customer" class="contents">
            <!-- Hero header -->
            <div class="flex items-center gap-3 sm:gap-4 px-4 sm:px-5 lg:px-7 pt-[18px] pb-3.5 border-b border-line bg-paper">
                <Link :href="urls.index" class="text-ink-500 hover:text-ink-900 shrink-0">
                    <Icon name="arrowLeft" />
                </Link>
                <div class="flex-1 min-w-0">
                    <h1 class="m-0 text-lg sm:text-xl font-semibold tracking-[-0.015em] truncate">New customer</h1>
                </div>
            </div>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[720px] w-full mx-auto pt-5 pb-7">
                <form class="bg-surface border border-line rounded-xl shadow-sm p-5" @submit.prevent="submit">
                    <div class="pb-5 border-b border-line mb-5">
                        <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">Personal details</h2>
                        <div class="text-xs text-ink-500 leading-normal max-w-[560px]">
                            Name, company, and account identifiers used across orders and invoices.
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <label class="flex flex-col gap-1 sm:col-span-2">
                            <FieldLabel class="mb-0">Title</FieldLabel>
                            <Select v-model="form.title">
                                <option v-for="option in titleOptions" :key="option || 'none'" :value="option">{{ option || '—' }}</option>
                            </Select>
                        </label>
                        <div class="sm:col-span-5">
                            <FieldLabel required>First name</FieldLabel>
                            <TextInput v-model="form.first_name" :invalid="!!form.errors.first_name" />
                            <div v-if="form.errors.first_name" class="mt-1 text-[11px] text-danger">{{ form.errors.first_name }}</div>
                        </div>
                        <div class="sm:col-span-5">
                            <FieldLabel required>Last name</FieldLabel>
                            <TextInput v-model="form.last_name" :invalid="!!form.errors.last_name" />
                            <div v-if="form.errors.last_name" class="mt-1 text-[11px] text-danger">{{ form.errors.last_name }}</div>
                        </div>
                        <div class="sm:col-span-12">
                            <FieldLabel>Company name</FieldLabel>
                            <TextInput v-model="form.company_name" placeholder="Optional" :invalid="!!form.errors.company_name" />
                        </div>
                        <div class="sm:col-span-6">
                            <FieldLabel>Tax identifier</FieldLabel>
                            <TextInput v-model="form.tax_identifier" mono placeholder="GB000000000" :invalid="!!form.errors.tax_identifier" />
                        </div>
                        <div class="sm:col-span-6">
                            <FieldLabel>Account reference</FieldLabel>
                            <TextInput v-model="form.account_ref" mono placeholder="ACC-00000" :invalid="!!form.errors.account_ref" />
                        </div>
                    </div>

                    <div v-if="customerGroups.length" class="mt-5 pt-5 border-t border-line">
                        <FieldLabel>Customer groups</FieldLabel>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span
                                v-for="id in form.customer_group_ids"
                                :key="id"
                                class="inline-flex items-center gap-1 rounded-full border bg-surface-2 border-line text-ink-700 h-[22px] pl-2 pr-1 text-[11px] font-medium"
                            >
                                <span>{{ groupName(id) }}</span>
                                <button
                                    type="button"
                                    class="grid place-items-center w-[16px] h-[16px] rounded-full text-ink-500 hover:text-ink-900 hover:bg-surface focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                                    :aria-label="`Remove ${groupName(id)}`"
                                    @click="removeGroup(id)"
                                >
                                    <Icon name="x" cls="sm" />
                                </button>
                            </span>
                            <span v-if="!form.customer_group_ids.length" class="text-[12px] text-ink-500">No groups assigned.</span>
                            <div class="ml-auto min-w-[180px] max-w-[220px] flex-1">
                                <Combobox
                                    v-if="addableGroupOptions.length"
                                    v-model="addGroupSelection"
                                    :options="addableGroupOptions"
                                    placeholder="Add to group…"
                                    @change="onAddGroup"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-line">
                        <Button type="submit" variant="primary" :disabled="form.processing">Create customer</Button>
                    </div>
                </form>
            </div>
        </div>
    </PanelLayout>
</template>
