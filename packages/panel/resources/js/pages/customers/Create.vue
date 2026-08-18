<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import Combobox from '../../components/Combobox.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import Select from '../../components/Select.vue';
import TextInput from '../../components/TextInput.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
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

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.sales') },
    { label: t('nav.customers'), href: props.urls.index },
    { label: t('customers.new_title'), current: true },
]);

// Stored values stay canonical; only the visible labels are translated.
const titleOptions = computed(() => [
    { value: '', label: '—' },
    { value: 'Mr', label: t('customers.title_mr') },
    { value: 'Ms', label: t('customers.title_ms') },
    { value: 'Mrs', label: t('customers.title_mrs') },
    { value: 'Mx', label: t('customers.title_mx') },
    { value: 'Dr', label: t('customers.title_dr') },
]);

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
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader :title="t('customers.new_title')">
                <template #icon>
                    <Link
                        :href="urls.index"
                        class="text-ink-500 hover:text-ink-900 shrink-0 self-center"
                        :aria-label="t('customers.back_to_customers')"
                    >
                        <Icon name="arrowLeft" />
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[720px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />
                <form class="bg-surface border border-line rounded-xl shadow-sm p-5" @submit.prevent="submit">
                    <div class="pb-5 border-b border-line mb-5">
                        <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('customers.personal_details') }}</h2>
                        <div class="text-xs text-ink-500 leading-normal max-w-[560px]">
                            {{ t('customers.personal_details_desc') }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-2">
                            <FieldLabel for="customer-title">{{ t('customers.field_title') }}</FieldLabel>
                            <Select id="customer-title" v-model="form.title">
                                <option v-for="option in titleOptions" :key="option.value || 'none'" :value="option.value">{{ option.label }}</option>
                            </Select>
                        </div>
                        <div class="sm:col-span-5">
                            <FieldLabel for="customer-first-name" required>{{ t('customers.field_first_name') }}</FieldLabel>
                            <TextInput id="customer-first-name" v-model="form.first_name" :invalid="!!form.errors.first_name" />
                            <div v-if="form.errors.first_name" class="mt-1 text-[11px] text-danger">{{ form.errors.first_name }}</div>
                        </div>
                        <div class="sm:col-span-5">
                            <FieldLabel for="customer-last-name" required>{{ t('customers.field_last_name') }}</FieldLabel>
                            <TextInput id="customer-last-name" v-model="form.last_name" :invalid="!!form.errors.last_name" />
                            <div v-if="form.errors.last_name" class="mt-1 text-[11px] text-danger">{{ form.errors.last_name }}</div>
                        </div>
                        <div class="sm:col-span-12">
                            <FieldLabel for="customer-company-name">{{ t('customers.field_company_name') }}</FieldLabel>
                            <TextInput id="customer-company-name" v-model="form.company_name" :placeholder="t('common.optional')" :invalid="!!form.errors.company_name" />
                            <div v-if="form.errors.company_name" class="mt-1 text-[11px] text-danger">{{ form.errors.company_name }}</div>
                        </div>
                        <div class="sm:col-span-6">
                            <FieldLabel for="customer-tax-identifier">{{ t('customers.field_tax_identifier') }}</FieldLabel>
                            <TextInput id="customer-tax-identifier" v-model="form.tax_identifier" mono placeholder="GB000000000" :invalid="!!form.errors.tax_identifier" />
                            <div v-if="form.errors.tax_identifier" class="mt-1 text-[11px] text-danger">{{ form.errors.tax_identifier }}</div>
                        </div>
                        <div class="sm:col-span-6">
                            <FieldLabel for="customer-account-ref">{{ t('customers.field_account_ref') }}</FieldLabel>
                            <TextInput id="customer-account-ref" v-model="form.account_ref" mono placeholder="ACC-00000" :invalid="!!form.errors.account_ref" />
                            <div v-if="form.errors.account_ref" class="mt-1 text-[11px] text-danger">{{ form.errors.account_ref }}</div>
                        </div>
                    </div>

                    <div v-if="customerGroups.length" class="mt-5 pt-5 border-t border-line">
                        <FieldLabel>{{ t('customers.customer_groups') }}</FieldLabel>
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
                                    :aria-label="t('customers.remove_group', { name: groupName(id) })"
                                    @click="removeGroup(id)"
                                >
                                    <Icon name="x" cls="sm" />
                                </button>
                            </span>
                            <span v-if="!form.customer_group_ids.length" class="text-[12px] text-ink-500">{{ t('customers.no_groups') }}</span>
                            <div class="ml-auto min-w-[180px] max-w-[220px] flex-1">
                                <Combobox
                                    v-if="addableGroupOptions.length"
                                    v-model="addGroupSelection"
                                    :options="addableGroupOptions"
                                    :placeholder="t('customers.add_to_group')"
                                    @change="onAddGroup"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-line">
                        <Button type="submit" variant="primary" :disabled="form.processing">{{ t('customers.create_customer') }}</Button>
                    </div>
                </form>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
