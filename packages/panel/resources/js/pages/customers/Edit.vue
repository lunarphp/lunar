<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Deferred, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../../components/Button.vue';
import AddressCard from '../../components/AddressCard.vue';
import AddressFormFields from '../../components/AddressFormFields.vue';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Combobox from '../../components/Combobox.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Dialog from '../../components/Dialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftConflictDialog from '../../components/DraftConflictDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import FilterDropdown, { type FilterOption } from '../../components/FilterDropdown.vue';
import Icon from '../../components/Icon.vue';
import PageEmpty from '../../components/PageEmpty.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import Section from '../../components/Section.vue';
import Select from '../../components/Select.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import Tabs from '../../components/Tabs.vue';
import Textarea from '../../components/Textarea.vue';
import TextInput from '../../components/TextInput.vue';
import TimeSeriesChart, { type ChartPoint } from '../../components/TimeSeriesChart.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import { useEditDraft, type DraftState } from '../../composables/useEditDraft';

interface OptionItem {
    id: number;
    name: string;
}

interface Address {
    id: number;
    title: string | null;
    first_name: string;
    last_name: string;
    company_name: string | null;
    tax_identifier: string | null;
    line_one: string;
    line_two: string | null;
    line_three: string | null;
    city: string;
    state: string | null;
    postcode: string | null;
    country_id: number;
    delivery_instructions: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    shipping_default: boolean;
    billing_default: boolean;
    update_url: string;
    destroy_url: string;
}

interface LinkedUser {
    id: number;
    name: string | null;
    email: string;
    unlink_url: string;
}

interface ActivityEntry {
    description: string;
    created_at: string;
    causer_name: string | null;
    avatar: string | null;
    changes: string[];
}

interface OrderRow {
    id: number;
    reference: string;
    status: string;
    status_label: string;
    placed_at: string;
    total: string;
}

const props = defineProps<{
    customer: {
        id: number;
        title: string | null;
        first_name: string;
        last_name: string;
        company_name: string | null;
        tax_identifier: string | null;
        account_ref: string | null;
        admin_notes: string | null;
        created_at: string;
        customer_groups: OptionItem[];
    };
    customerGroups: OptionItem[];
    countries: OptionItem[];
    addresses: Address[];
    users: LinkedUser[];
    activities: ActivityEntry[];
    orders: OrderRow[];
    stats: {
        orders: number;
        totalSpend: string | null;
        avgOrder: string | null;
        latestOrderAt: string | null;
    };
    // Deferred prop: absent on first paint, delivered by a follow-up request.
    orderChart?: {
        range: string;
        buckets: ChartPoint[];
    };
    draft: DraftState | null;
    urls: {
        index: string;
        activityLog: string;
        update: string;
        destroy: string;
        addressesStore: string;
        usersStore: string;
        notesUpdate: string;
        draft: string;
        draftCommit: string;
    };
}>();

const { t, te } = useI18n();

const initials = (): string => ((props.customer.first_name?.[0] ?? '?') + (props.customer.last_name?.[0] ?? '')).toUpperCase();
const fullName = computed(() => [props.customer.title, props.customer.first_name, props.customer.last_name].filter(Boolean).join(' '));

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.sales') },
    { label: t('nav.customers'), href: props.urls.index },
    { label: fullName.value, current: true },
]);

// Most customers have a single storefront user; surface its email in the header.
// With several linked users there is no single address to show, so fall back
// to the user count.
const headerUsers = computed(() => {
    if (props.users.length === 1) {
        return props.users[0].email;
    }

    return props.users.length > 1 ? t('customers.user_count', props.users.length) : null;
});

// Personal details + customer groups, driven by an autosaving edit draft:
// dirty fields persist server-side and commit with field-level conflict
// detection instead of a last-write-wins PUT. The save cluster lives in the
// sticky breadcrumb bar via <DraftActions :form="draftForm" />.
const draftForm = useEditDraft({
    initial: {
        title: props.customer.title ?? '',
        first_name: props.customer.first_name,
        last_name: props.customer.last_name,
        company_name: props.customer.company_name ?? '',
        tax_identifier: props.customer.tax_identifier ?? '',
        account_ref: props.customer.account_ref ?? '',
        customer_group_ids: props.customer.customer_groups.map((group) => group.id),
    },
    draft: props.draft,
    urls: { draft: props.urls.draft, commit: props.urls.draftCommit },
});

const {
    values: details,
    errors: detailsErrors,
    conflicts: draftConflicts,
    committing: draftCommitting,
    commit: commitDetails,
    resolve: resolveDraft,
} = draftForm;

const conflictOpen = computed({
    get: () => draftConflicts.value.length > 0,
    set: (value: boolean) => {
        if (!value) {
            draftConflicts.value = [];
        }
    },
});

const onResolveConflicts = (resolutions: Record<string, unknown>, rebase: Record<string, unknown>): void => {
    void resolveDraft(resolutions, rebase);
};

// Stored values stay canonical; only the visible labels are translated. A stored
// title outside the base list (e.g. entered at checkout) is kept as its own option
// so it still displays and survives a save untouched.
const titleOptions = computed(() => {
    const options = [
        { value: '', label: '—' },
        { value: 'Mr', label: t('customers.title_mr') },
        { value: 'Ms', label: t('customers.title_ms') },
        { value: 'Mrs', label: t('customers.title_mrs') },
        { value: 'Mx', label: t('customers.title_mx') },
        { value: 'Dr', label: t('customers.title_dr') },
    ];

    const stored = props.customer.title;

    if (stored && !options.some((option) => option.value === stored)) {
        options.splice(1, 0, { value: stored, label: stored });
    }

    return options;
});

const groupName = (id: number): string => props.customerGroups.find((group) => group.id === id)?.name ?? String(id);

const addableGroupOptions = computed(() => {
    const taken = new Set(details.customer_group_ids);

    return props.customerGroups.filter((group) => !taken.has(group.id)).map((group) => ({ value: group.id, label: group.name }));
});

const addGroupSelection = ref<string | number | null>(null);
const onAddGroup = (value: string | number): void => {
    details.customer_group_ids.push(Number(value));
    addGroupSelection.value = null;
};
const removeGroup = (id: number): void => {
    details.customer_group_ids = details.customer_group_ids.filter((groupId) => groupId !== id);
};

const submitDetails = (): void => {
    void commitDetails();
};

// Confirmation dialog (shared across customer delete / address delete / user unlink)
type ConfirmKind = 'customer' | 'address' | 'user';
const confirmTarget = ref<{ kind: ConfirmKind; address?: Address; user?: LinkedUser } | null>(null);

const confirmOpen = computed({
    get: () => confirmTarget.value !== null,
    set: (value: boolean) => {
        if (!value) {
            confirmTarget.value = null;
        }
    },
});

const confirmTitle = computed(() => {
    switch (confirmTarget.value?.kind) {
        case 'customer':
            return t('customers.confirm_delete_customer_title');
        case 'address':
            return t('customers.confirm_delete_address_title');
        case 'user':
            return t('customers.confirm_unlink_user_title');
        default:
            return t('common.are_you_sure');
    }
});

const confirmDescription = computed(() => {
    switch (confirmTarget.value?.kind) {
        case 'customer':
            return t('customers.confirm_delete_customer_body');
        case 'address':
            return t('customers.confirm_delete_address_body');
        case 'user':
            return t('customers.confirm_unlink_user_body', { email: confirmTarget.value?.user?.email ?? '' });
        default:
            return '';
    }
});

// Unlinking is not a deletion — the confirm button says so.
const confirmLabel = computed(() =>
    confirmTarget.value?.kind === 'user' ? t('customers.unlink_user') : t('common.delete'));

const confirmDestroy = (): void => {
    const target = confirmTarget.value;

    if (!target) {
        return;
    }

    if (target.kind === 'customer') {
        router.delete(props.urls.destroy);
    } else if (target.kind === 'address' && target.address) {
        router.delete(target.address.destroy_url, { preserveScroll: true });
    } else if (target.kind === 'user' && target.user) {
        router.delete(target.user.unlink_url, { preserveScroll: true });
    }
};

const destroyCustomer = (): void => {
    confirmTarget.value = { kind: 'customer' };
};

// Addresses tab
const countryName = (id: number): string => props.countries.find((country) => country.id === id)?.name ?? '';

const cardAddress = (address: Address) => ({
    ...address,
    country_name: countryName(address.country_id),
    contact_email: address.contact_email ?? undefined,
});

const emptyAddress = () => ({
    title: '',
    first_name: '',
    last_name: '',
    company_name: '',
    tax_identifier: '',
    line_one: '',
    line_two: '',
    line_three: '',
    city: '',
    state: '',
    postcode: '',
    country_id: '' as number | string,
    delivery_instructions: '',
    contact_email: '',
    contact_phone: '',
    shipping_default: false,
    billing_default: false,
});

const showNewAddressForm = ref(false);
const newAddressForm = useForm(emptyAddress());

const openNewAddressForm = (): void => {
    newAddressForm.clearErrors();
    showNewAddressForm.value = true;
};

const submitNewAddress = (): void => {
    newAddressForm.post(props.urls.addressesStore, {
        preserveScroll: true,
        onSuccess: () => {
            newAddressForm.reset();
            showNewAddressForm.value = false;
        },
    });
};

const editingAddressId = ref<number | null>(null);
const addressForm = useForm(emptyAddress());

const startEditAddress = (address: Address): void => {
    editingAddressId.value = address.id;
    addressForm.clearErrors();
    addressForm.title = address.title ?? '';
    addressForm.first_name = address.first_name;
    addressForm.last_name = address.last_name;
    addressForm.company_name = address.company_name ?? '';
    addressForm.tax_identifier = address.tax_identifier ?? '';
    addressForm.line_one = address.line_one;
    addressForm.line_two = address.line_two ?? '';
    addressForm.line_three = address.line_three ?? '';
    addressForm.city = address.city;
    addressForm.state = address.state ?? '';
    addressForm.postcode = address.postcode ?? '';
    addressForm.country_id = address.country_id;
    addressForm.delivery_instructions = address.delivery_instructions ?? '';
    addressForm.contact_email = address.contact_email ?? '';
    addressForm.contact_phone = address.contact_phone ?? '';
    addressForm.shipping_default = address.shipping_default;
    addressForm.billing_default = address.billing_default;
};

const editingAddress = computed(() => props.addresses.find((address) => address.id === editingAddressId.value) ?? null);

const editAddressOpen = computed({
    get: () => editingAddressId.value !== null,
    set: (value: boolean) => {
        if (!value) {
            editingAddressId.value = null;
        }
    },
});

const submitEditAddress = (): void => {
    if (!editingAddress.value) {
        return;
    }

    addressForm.put(editingAddress.value.update_url, {
        preserveScroll: true,
        onSuccess: () => {
            editingAddressId.value = null;
        },
    });
};

const destroyAddress = (address: Address): void => {
    confirmTarget.value = { kind: 'address', address };
};

const setAddressDefault = (address: Address, field: 'shipping_default' | 'billing_default'): void => {
    router.put(
        address.update_url,
        {
            title: address.title,
            first_name: address.first_name,
            last_name: address.last_name,
            company_name: address.company_name,
            tax_identifier: address.tax_identifier,
            line_one: address.line_one,
            line_two: address.line_two,
            line_three: address.line_three,
            city: address.city,
            state: address.state,
            postcode: address.postcode,
            country_id: address.country_id,
            delivery_instructions: address.delivery_instructions,
            contact_email: address.contact_email,
            contact_phone: address.contact_phone,
            shipping_default: address.shipping_default,
            billing_default: address.billing_default,
            [field]: true,
        },
        { preserveScroll: true },
    );
};

const defaultBilling = computed(() => props.addresses.find((address) => address.billing_default));
const defaultShipping = computed(() => props.addresses.find((address) => address.shipping_default));

// Users tab
const linkUserForm = useForm({ email: '' });

const submitLinkUser = (): void => {
    linkUserForm.post(props.urls.usersStore, {
        preserveScroll: true,
        onSuccess: () => linkUserForm.reset(),
    });
};

const unlinkUser = (user: LinkedUser): void => {
    confirmTarget.value = { kind: 'user', user };
};

const userInitials = (user: LinkedUser): string =>
    (user.name ?? user.email)
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

// Activity tab: activity descriptions are lang keys where one exists (spatie's
// created/updated plus the customer action events); anything else shows as-is.
const activityLabel = (description: string): string => {
    const key = `customers.activity_${description.replaceAll('-', '_')}`;

    return te(key) ? t(key) : description;
};

const timelineEvents = computed(() =>
    props.activities.map((activity) => ({
        label: activityLabel(activity.description),
        when: activity.created_at,
        actor: activity.causer_name ?? '',
        avatar: activity.avatar,
        changes: activity.changes,
    })));

// Admin notes side card: view by default, pencil toggles an inline editor.
const notesEditing = ref(false);
const notesForm = useForm({ admin_notes: props.customer.admin_notes ?? '' });

const startEditNotes = (): void => {
    notesForm.clearErrors();
    notesForm.admin_notes = props.customer.admin_notes ?? '';
    notesEditing.value = true;
};

const submitNotes = (): void => {
    notesForm.put(props.urls.notesUpdate, {
        preserveScroll: true,
        onSuccess: () => {
            notesEditing.value = false;
        },
    });
};

// Order-value chart: the range switcher partial-reloads only the chart prop;
// while data is in flight the previous render holds at reduced opacity.
const chartRange = ref(props.orderChart?.range ?? '12m');
const chartLoading = ref(false);

const chartRangeOptions = computed<FilterOption[]>(() => [
    { value: '12m', label: t('customers.chart_range_12m') },
    { value: '3y', label: t('customers.chart_range_3y') },
    { value: '5y', label: t('customers.chart_range_5y') },
    { value: '10y', label: t('customers.chart_range_10y') },
]);

const onChartRangeChange = (): void => {
    router.reload({
        data: { chart_range: chartRange.value },
        only: ['orderChart'],
        onStart: () => {
            chartLoading.value = true;
        },
        onFinish: () => {
            chartLoading.value = false;
        },
    });
};

const chartHasData = computed(() => (props.orderChart?.buckets ?? []).some((bucket) => bucket.value > 0));

watch(chartRange, onChartRangeChange);

// Order history tab
const orderStatusTone = (status: string): 'sage' | 'archived' | 'danger' | 'neutral' => {
    switch (status) {
        case 'open':
            return 'sage';
        case 'closed':
            return 'archived';
        case 'cancelled':
            return 'danger';
        default:
            return 'neutral';
    }
};

const formatDate = (value: string): string => new Date(value).toLocaleDateString();

// Tabs
const activeTab = ref<'addresses' | 'users' | 'orders' | 'activity'>('addresses');
const tabDefs = computed(() => [
    { value: 'addresses', label: t('customers.tab_addresses'), count: props.addresses.length },
    { value: 'users', label: t('customers.tab_users'), count: props.users.length },
    { value: 'orders', label: t('customers.tab_order_history'), count: props.stats.orders },
    { value: 'activity', label: t('customers.tab_activity') },
]);
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Customer detail" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <DraftActions :form="draftForm" />
                </template>
            </Breadcrumbs>

            <PageHeader :title="fullName">
                <template #icon>
                    <div class="w-11 h-11 rounded-full overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700 text-[13px] font-semibold">
                        {{ initials() }}
                    </div>
                </template>
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <span v-if="headerUsers" class="text-ink-700">{{ headerUsers }}</span>
                        <span v-if="headerUsers" class="text-ink-500">·</span>
                        <span v-if="customer.company_name" class="text-ink-700">{{ customer.company_name }}</span>
                        <span v-if="customer.company_name" class="text-ink-500">·</span>
                        <template v-for="group in customer.customer_groups" :key="group.id">
                            <StatusBadge size="sm">{{ group.name }}</StatusBadge>
                        </template>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('customers.joined', { date: new Date(customer.created_at).toLocaleDateString() }) }}</span>
                    </div>
                </template>
                <template #actions>
                    <Button icon="trash" class="!text-danger" @click="destroyCustomer">{{ t('customers.delete_customer') }}</Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <!-- Order value over time; the range switcher reloads only this card. -->
                        <div class="bg-surface border border-line rounded-xl shadow-sm mb-6 overflow-hidden">
                            <div class="flex items-center gap-2.5 px-4 py-3 border-b border-line">
                                <h2 class="m-0 text-[13.5px] font-semibold tracking-[-0.01em]">{{ t('customers.chart_title') }}</h2>
                                <div class="flex-1" />
                                <FilterDropdown v-model="chartRange" :label="t('customers.chart_range_12m')" :options="chartRangeOptions" default-value="12m" />
                            </div>
                            <div class="px-4 py-3.5 transition-opacity duration-150" :class="chartLoading ? 'opacity-60' : ''">
                                <Deferred data="orderChart">
                                    <template #fallback>
                                        <div class="h-[168px] rounded-md bg-surface-2 animate-pulse" role="status" :aria-label="t('common.loading')" />
                                    </template>
                                    <TimeSeriesChart
                                        v-if="chartHasData"
                                        :points="orderChart?.buckets ?? []"
                                        :ariaLabel="t('customers.chart_title')"
                                    />
                                    <p v-else class="m-0 py-6 text-center text-[12px] text-ink-500 italic">{{ t('customers.chart_empty') }}</p>
                                </Deferred>
                            </div>
                        </div>

                        <form @submit.prevent="submitDetails">
                            <Section :title="t('customers.personal_details')">
                                <template #desc>{{ t('customers.personal_details_desc') }}</template>
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <div class="sm:col-span-2">
                                        <FieldLabel for="customer-title">{{ t('customers.field_title') }}</FieldLabel>
                                        <Select id="customer-title" v-model="details.title">
                                            <option v-for="option in titleOptions" :key="option.value || 'none'" :value="option.value">{{ option.label }}</option>
                                        </Select>
                                    </div>
                                    <div class="sm:col-span-5">
                                        <FieldLabel for="customer-first-name" required>{{ t('customers.field_first_name') }}</FieldLabel>
                                        <TextInput id="customer-first-name" v-model="details.first_name" :invalid="!!detailsErrors.first_name" />
                                        <div v-if="detailsErrors.first_name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.first_name }}</div>
                                    </div>
                                    <div class="sm:col-span-5">
                                        <FieldLabel for="customer-last-name" required>{{ t('customers.field_last_name') }}</FieldLabel>
                                        <TextInput id="customer-last-name" v-model="details.last_name" :invalid="!!detailsErrors.last_name" />
                                        <div v-if="detailsErrors.last_name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.last_name }}</div>
                                    </div>
                                    <div class="sm:col-span-12">
                                        <FieldLabel for="customer-company-name">{{ t('customers.field_company_name') }}</FieldLabel>
                                        <TextInput id="customer-company-name" v-model="details.company_name" :placeholder="t('common.optional')" :invalid="!!detailsErrors.company_name" />
                                        <div v-if="detailsErrors.company_name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.company_name }}</div>
                                    </div>
                                    <div class="sm:col-span-6">
                                        <FieldLabel for="customer-tax-identifier">{{ t('customers.field_tax_identifier') }}</FieldLabel>
                                        <TextInput id="customer-tax-identifier" v-model="details.tax_identifier" mono :invalid="!!detailsErrors.tax_identifier" />
                                        <div v-if="detailsErrors.tax_identifier" class="mt-1 text-[11px] text-danger">{{ detailsErrors.tax_identifier }}</div>
                                    </div>
                                    <div class="sm:col-span-6">
                                        <FieldLabel for="customer-account-ref">{{ t('customers.field_account_ref') }}</FieldLabel>
                                        <TextInput id="customer-account-ref" v-model="details.account_ref" mono :invalid="!!detailsErrors.account_ref" />
                                        <div v-if="detailsErrors.account_ref" class="mt-1 text-[11px] text-danger">{{ detailsErrors.account_ref }}</div>
                                    </div>
                                </div>
                            </Section>

                            <Section :title="t('customers.customer_groups')">
                                <template #desc>{{ t('customers.groups_desc') }}</template>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span
                                        v-for="id in details.customer_group_ids"
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
                                    <span v-if="!details.customer_group_ids.length" class="text-[12px] text-ink-500">{{ t('customers.no_groups') }}</span>
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
                            </Section>

                            <!-- The save cluster lives in the sticky breadcrumb bar; the
                                 hidden submit keeps Enter-to-save working in the form. -->
                            <button type="submit" class="hidden" aria-hidden="true" tabindex="-1" />
                        </form>

                        <PageZone region="main" position="after" :customer="customer" />

                        <!-- Tabbed: addresses, users, activity -->
                        <div class="pt-2">
                            <Tabs v-model="activeTab" :tabs="tabDefs">
                                <template #actions>
                                    <Button v-if="activeTab === 'addresses'" size="sm" icon="plus" @click="openNewAddressForm">{{ t('customers.add_address') }}</Button>
                                </template>

                                <template #addresses>
                                    <div v-if="addresses.length" class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                        <AddressCard v-for="address in addresses" :key="address.id" :address="cardAddress(address)">
                                            <template #actions>
                                                <Button variant="ghost" size="sm" icon="edit" @click="startEditAddress(address)">{{ t('common.edit') }}</Button>
                                                <Button v-if="!address.billing_default" variant="ghost" size="sm" @click="setAddressDefault(address, 'billing_default')">{{ t('customers.set_default_billing') }}</Button>
                                                <Button v-if="!address.shipping_default" variant="ghost" size="sm" @click="setAddressDefault(address, 'shipping_default')">{{ t('customers.set_default_shipping') }}</Button>
                                                <div class="flex-1" />
                                                <Button variant="ghost" size="sm" icon="trash" :aria-label="t('customers.remove_address')" @click="destroyAddress(address)" />
                                            </template>
                                        </AddressCard>
                                    </div>
                                    <PageEmpty v-else :title="t('customers.addresses_empty_title')">{{ t('customers.addresses_empty_body') }}</PageEmpty>

                                    <Dialog v-model:open="showNewAddressForm" :title="t('customers.add_address')" size="lg">
                                        <form id="new-address-form" @submit.prevent="submitNewAddress">
                                            <AddressFormFields :form="newAddressForm" :countries="countries" id-prefix="new-address" />
                                        </form>
                                        <template #footer>
                                            <Button type="button" @click="showNewAddressForm = false">{{ t('common.cancel') }}</Button>
                                            <Button type="submit" form="new-address-form" variant="primary" :disabled="newAddressForm.processing">{{ t('customers.add_address') }}</Button>
                                        </template>
                                    </Dialog>

                                    <Dialog v-model:open="editAddressOpen" :title="t('customers.edit_address')" size="lg">
                                        <form id="edit-address-form" @submit.prevent="submitEditAddress">
                                            <AddressFormFields :form="addressForm" :countries="countries" id-prefix="edit-address" />
                                        </form>
                                        <template #footer>
                                            <Button type="button" @click="editAddressOpen = false">{{ t('common.cancel') }}</Button>
                                            <Button type="submit" form="edit-address-form" variant="primary" :disabled="addressForm.processing">{{ t('customers.save_address') }}</Button>
                                        </template>
                                    </Dialog>
                                </template>

                                <template #users>
                                    <p class="text-xs text-ink-500 mb-3 max-w-[640px]">{{ t('customers.users_intro') }}</p>
                                    <div v-if="users.length" class="bg-surface border border-line rounded-xl shadow-sm overflow-hidden">
                                        <table class="w-full border-collapse text-[13px]">
                                            <thead>
                                                <tr class="text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium bg-surface-2 border-b border-line">
                                                    <th class="text-left font-medium px-4 py-2.5">{{ t('common.name') }}</th>
                                                    <th class="px-4 py-2.5 w-[40px]" />
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="user in users" :key="user.id" class="border-b border-line last:border-b-0">
                                                    <td class="px-4 py-2.5 align-middle">
                                                        <div class="flex items-center gap-2.5 min-w-0">
                                                            <div class="w-7 h-7 rounded-full border border-line bg-surface-2 grid place-items-center text-ink-700 text-[10.5px] font-semibold shrink-0">
                                                                {{ userInitials(user) }}
                                                            </div>
                                                            <div class="min-w-0">
                                                                <div class="text-ink-900 truncate">{{ user.name ?? user.email }}</div>
                                                                <div v-if="user.name" class="text-[11.5px] text-ink-500 truncate">{{ user.email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2.5 align-middle text-right">
                                                        <Button variant="ghost" size="sm" icon="trash" :aria-label="t('customers.unlink_user')" @click="unlinkUser(user)" />
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <PageEmpty v-else :title="t('customers.users_empty_title')">{{ t('customers.users_empty_body') }}</PageEmpty>

                                    <form class="mt-3 flex items-end gap-2 rounded-md border border-dashed border-line-strong p-4" @submit.prevent="submitLinkUser">
                                        <div class="flex-1">
                                            <FieldLabel for="link-user-email">{{ t('customers.link_user_label') }}</FieldLabel>
                                            <TextInput id="link-user-email" v-model="linkUserForm.email" type="email" :invalid="!!linkUserForm.errors.email" />
                                            <div v-if="linkUserForm.errors.email" class="mt-1 text-[11px] text-danger">{{ linkUserForm.errors.email }}</div>
                                        </div>
                                        <Button type="submit" :disabled="linkUserForm.processing">{{ t('customers.link_user_button') }}</Button>
                                    </form>
                                </template>

                                <template #orders>
                                    <div v-if="orders.length" class="bg-surface border border-line rounded-xl shadow-sm overflow-hidden">
                                        <table class="w-full border-collapse text-[13px]">
                                            <thead>
                                                <tr class="text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium bg-surface-2 border-b border-line">
                                                    <th class="text-left font-medium px-4 py-2.5">{{ t('customers.order_reference') }}</th>
                                                    <th class="text-left font-medium px-4 py-2.5">{{ t('customers.order_date') }}</th>
                                                    <th class="text-left font-medium px-4 py-2.5">{{ t('customers.order_status') }}</th>
                                                    <th class="text-right font-medium px-4 py-2.5">{{ t('customers.order_total') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="order in orders" :key="order.id" class="border-b border-line last:border-b-0">
                                                    <td class="px-4 py-2.5 align-middle font-mono text-[12px] text-ink-900">{{ order.reference }}</td>
                                                    <td class="px-4 py-2.5 align-middle text-ink-700 [font-variant-numeric:tabular-nums]">{{ formatDate(order.placed_at) }}</td>
                                                    <td class="px-4 py-2.5 align-middle">
                                                        <StatusBadge size="sm" :tone="orderStatusTone(order.status)">{{ order.status_label }}</StatusBadge>
                                                    </td>
                                                    <td class="px-4 py-2.5 align-middle text-right text-ink-900 [font-variant-numeric:tabular-nums]">{{ order.total }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <PageEmpty v-else :title="t('customers.orders_empty_title')">{{ t('customers.orders_empty_body') }}</PageEmpty>
                                </template>

                                <template #activity>
                                    <div v-if="activities.length" class="mb-3 flex justify-end">
                                        <a :href="urls.activityLog" class="text-[11.5px] font-medium text-ink-500 hover:text-ink-900">{{ t('customers.activity_see_all') }}</a>
                                    </div>
                                    <ActivityTimeline :events="timelineEvents" :reverse="false" />
                                    <PageEmpty v-if="!activities.length" :title="t('customers.activity_empty')" />
                                </template>
                            </Tabs>
                        </div>
                    </div>

                    <aside>
                        <div class="lg:sticky lg:top-[60px] flex flex-col gap-0">
                            <SideCard :title="t('customers.at_a_glance')">
                                <div class="flex flex-col gap-2 text-[12px]">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.side_type') }}</span>
                                        <span class="text-ink-900 font-medium">{{ customer.company_name ? t('customers.type_business') : t('customers.type_individual') }}</span>
                                    </div>
                                    <div v-if="customer.account_ref" class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.side_account_ref') }}</span>
                                        <span class="text-ink-900 font-mono">{{ customer.account_ref }}</span>
                                    </div>
                                    <div v-if="customer.tax_identifier" class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.side_tax_id') }}</span>
                                        <span class="text-ink-900 font-mono">{{ customer.tax_identifier }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.side_joined') }}</span>
                                        <span class="text-ink-900 [font-variant-numeric:tabular-nums]">{{ new Date(customer.created_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div v-if="customer.customer_groups.length" class="border-t border-line pt-2 mt-1 flex flex-wrap gap-1">
                                        <StatusBadge v-for="group in customer.customer_groups" :key="group.id" size="sm">{{ group.name }}</StatusBadge>
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('customers.lifetime_stats')">
                                <div class="flex flex-col gap-2 text-[12px]">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.stat_total_spend') }}</span>
                                        <span class="text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ stats.totalSpend ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.stat_orders') }}</span>
                                        <span class="text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ stats.orders }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.stat_avg_order') }}</span>
                                        <span class="text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ stats.avgOrder ?? '—' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">{{ t('customers.stat_latest_order') }}</span>
                                        <span class="text-ink-900 font-medium [font-variant-numeric:tabular-nums]">{{ stats.latestOrderAt ? formatDate(stats.latestOrderAt) : '—' }}</span>
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('customers.default_addresses')">
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <div class="text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium mb-1">{{ t('customers.billing') }}</div>
                                        <div v-if="defaultBilling" class="text-[12.5px] text-ink-700 leading-[1.5]">
                                            <div class="text-ink-900">{{ defaultBilling.first_name }} {{ defaultBilling.last_name }}</div>
                                            <div>{{ defaultBilling.line_one }}<span v-if="defaultBilling.line_two">, {{ defaultBilling.line_two }}</span></div>
                                            <div>{{ defaultBilling.city }}<span v-if="defaultBilling.state">, {{ defaultBilling.state }}</span></div>
                                            <div>{{ defaultBilling.postcode }}</div>
                                            <div class="text-ink-500">{{ countryName(defaultBilling.country_id) }}</div>
                                        </div>
                                        <div v-else class="text-[12px] text-ink-500 italic">{{ t('customers.none_set') }}</div>
                                    </div>
                                    <div class="border-t border-line pt-2.5">
                                        <div class="text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium mb-1">{{ t('customers.shipping') }}</div>
                                        <div v-if="defaultShipping" class="text-[12.5px] text-ink-700 leading-[1.5]">
                                            <div class="text-ink-900">{{ defaultShipping.first_name }} {{ defaultShipping.last_name }}</div>
                                            <div>{{ defaultShipping.line_one }}<span v-if="defaultShipping.line_two">, {{ defaultShipping.line_two }}</span></div>
                                            <div>{{ defaultShipping.city }}<span v-if="defaultShipping.state">, {{ defaultShipping.state }}</span></div>
                                            <div>{{ defaultShipping.postcode }}</div>
                                            <div class="text-ink-500">{{ countryName(defaultShipping.country_id) }}</div>
                                        </div>
                                        <div v-else class="text-[12px] text-ink-500 italic">{{ t('customers.none_set') }}</div>
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('customers.notes')">
                                <template #actions>
                                    <Button
                                        v-if="!notesEditing"
                                        variant="ghost"
                                        size="sm"
                                        icon="edit"
                                        :aria-label="t('customers.edit_notes')"
                                        @click="startEditNotes"
                                    />
                                </template>
                                <form v-if="notesEditing" class="flex flex-col gap-2" @submit.prevent="submitNotes">
                                    <Textarea
                                        v-model="notesForm.admin_notes"
                                        :rows="4"
                                        :aria-label="t('customers.notes')"
                                        :invalid="!!notesForm.errors.admin_notes"
                                    />
                                    <div v-if="notesForm.errors.admin_notes" class="text-[11px] text-danger">{{ notesForm.errors.admin_notes }}</div>
                                    <div class="flex gap-2">
                                        <Button type="submit" variant="primary" size="sm" :disabled="notesForm.processing">{{ t('common.save_changes') }}</Button>
                                        <Button type="button" size="sm" @click="notesEditing = false">{{ t('common.cancel') }}</Button>
                                    </div>
                                </form>
                                <template v-else>
                                    <p v-if="customer.admin_notes" class="m-0 text-[12.5px] text-ink-700 leading-[1.55] whitespace-pre-line">{{ customer.admin_notes }}</p>
                                    <p v-else class="m-0 text-[12px] text-ink-500 italic">{{ t('customers.notes_empty') }}</p>
                                </template>
                            </SideCard>
                        </div>
                    </aside>
                </div>
            </div>

            <ConfirmDialog
                v-model:open="confirmOpen"
                :title="confirmTitle"
                :description="confirmDescription"
                tone="danger"
                :confirm-label="confirmLabel"
                @confirm="confirmDestroy"
            />

            <DraftConflictDialog
                v-model:open="conflictOpen"
                :conflicts="draftConflicts"
                :busy="draftCommitting"
                @resolve="onResolveConflicts"
            />
        </div>
    </PanelLayout>
</template>
