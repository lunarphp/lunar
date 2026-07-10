<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Button from '../../components/Button.vue';
import AddressCard from '../../components/AddressCard.vue';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import Checkbox from '../../components/Checkbox.vue';
import Combobox from '../../components/Combobox.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import PageEmpty from '../../components/PageEmpty.vue';
import PanelSlot from '../../components/PanelSlot.vue';
import Section from '../../components/Section.vue';
import Select from '../../components/Select.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import Tabs from '../../components/Tabs.vue';
import Textarea from '../../components/Textarea.vue';
import TextInput from '../../components/TextInput.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';

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
        created_at: string;
        customer_groups: OptionItem[];
    };
    customerGroups: OptionItem[];
    countries: OptionItem[];
    addresses: Address[];
    users: LinkedUser[];
    activities: ActivityEntry[];
    urls: {
        index: string;
        update: string;
        destroy: string;
        addressesStore: string;
        usersStore: string;
    };
}>();

const flashSuccess = computed(() => (usePage().props.flash as { success?: string } | undefined)?.success);

const initials = (): string => ((props.customer.first_name?.[0] ?? '?') + (props.customer.last_name?.[0] ?? '')).toUpperCase();
const fullName = computed(() => [props.customer.title, props.customer.first_name, props.customer.last_name].filter(Boolean).join(' '));

// Personal details + customer groups
const detailsForm = useForm({
    title: props.customer.title ?? '',
    first_name: props.customer.first_name,
    last_name: props.customer.last_name,
    company_name: props.customer.company_name ?? '',
    tax_identifier: props.customer.tax_identifier ?? '',
    account_ref: props.customer.account_ref ?? '',
    customer_group_ids: props.customer.customer_groups.map((group) => group.id),
});

const titleOptions = ['', 'Mr', 'Ms', 'Mrs', 'Mx', 'Dr'];

const groupName = (id: number): string => props.customerGroups.find((group) => group.id === id)?.name ?? String(id);

const addableGroupOptions = computed(() => {
    const taken = new Set(detailsForm.customer_group_ids);

    return props.customerGroups.filter((group) => !taken.has(group.id)).map((group) => ({ value: group.id, label: group.name }));
});

const addGroupSelection = ref<string | number | null>(null);
const onAddGroup = (value: string | number): void => {
    detailsForm.customer_group_ids.push(Number(value));
    addGroupSelection.value = null;
};
const removeGroup = (id: number): void => {
    detailsForm.customer_group_ids = detailsForm.customer_group_ids.filter((groupId) => groupId !== id);
};

const submitDetails = (): void => {
    detailsForm.put(props.urls.update, { preserveScroll: true });
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
            return 'Delete this customer?';
        case 'address':
            return 'Delete this address?';
        case 'user':
            return 'Unlink this user?';
        default:
            return 'Are you sure?';
    }
});

const confirmDescription = computed(() => {
    switch (confirmTarget.value?.kind) {
        case 'customer':
            return 'This cannot be undone.';
        case 'address':
            return 'This address will be permanently removed from the customer.';
        case 'user':
            return `${confirmTarget.value?.user?.email} will no longer be able to sign in as this customer.`;
        default:
            return '';
    }
});

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

const cancelEditAddress = (): void => {
    editingAddressId.value = null;
};

const submitEditAddress = (address: Address): void => {
    addressForm.put(address.update_url, {
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

// Tabs
const activeTab = ref<'addresses' | 'users' | 'activity'>('addresses');
const tabDefs = computed(() => [
    { value: 'addresses', label: 'Addresses', count: props.addresses.length },
    { value: 'users', label: 'Users', count: props.users.length },
    { value: 'activity', label: 'Activity' },
]);
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Customer detail" class="contents">
            <!-- Hero strip -->
            <div class="flex items-start gap-3 sm:gap-4 px-4 sm:px-5 lg:px-7 pt-[18px] pb-3.5 border-b border-line bg-paper">
                <div class="w-11 h-11 rounded-full overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700 text-[13px] font-semibold">
                    {{ initials() }}
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="m-0 text-lg sm:text-xl font-semibold tracking-[-0.015em] truncate">{{ fullName }}</h1>
                    <div class="text-xs text-ink-500 mt-[3px] flex gap-2 items-center flex-wrap">
                        <span v-if="customer.company_name" class="text-ink-700">{{ customer.company_name }}</span>
                        <span v-if="customer.company_name" class="text-ink-500">·</span>
                        <template v-for="group in customer.customer_groups" :key="group.id">
                            <StatusBadge size="sm">{{ group.name }}</StatusBadge>
                        </template>
                        <span class="text-ink-500">·</span>
                        <span>Joined {{ new Date(customer.created_at).toLocaleDateString() }}</span>
                    </div>
                </div>
                <div class="hidden sm:flex gap-1.5 shrink-0">
                    <Button icon="trash" class="!text-danger" @click="destroyCustomer">Delete customer</Button>
                </div>
            </div>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <div v-if="flashSuccess" class="mb-4 rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink">
                    {{ flashSuccess }}
                </div>

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <form @submit.prevent="submitDetails">
                            <Section title="Personal details">
                                <template #desc>Name, company, and account identifiers used across orders and invoices.</template>
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <label class="flex flex-col gap-1 sm:col-span-2">
                                        <span class="text-[11.5px] text-ink-500">Title</span>
                                        <Select v-model="detailsForm.title">
                                            <option v-for="option in titleOptions" :key="option || 'none'" :value="option">{{ option || '—' }}</option>
                                        </Select>
                                    </label>
                                    <div class="sm:col-span-5">
                                        <FieldLabel required>First name</FieldLabel>
                                        <TextInput v-model="detailsForm.first_name" :invalid="!!detailsForm.errors.first_name" />
                                        <div v-if="detailsForm.errors.first_name" class="mt-1 text-[11px] text-danger">{{ detailsForm.errors.first_name }}</div>
                                    </div>
                                    <div class="sm:col-span-5">
                                        <FieldLabel required>Last name</FieldLabel>
                                        <TextInput v-model="detailsForm.last_name" :invalid="!!detailsForm.errors.last_name" />
                                        <div v-if="detailsForm.errors.last_name" class="mt-1 text-[11px] text-danger">{{ detailsForm.errors.last_name }}</div>
                                    </div>
                                    <div class="sm:col-span-12">
                                        <FieldLabel>Company name</FieldLabel>
                                        <TextInput v-model="detailsForm.company_name" placeholder="Optional" :invalid="!!detailsForm.errors.company_name" />
                                    </div>
                                    <div class="sm:col-span-6">
                                        <FieldLabel>Tax identifier</FieldLabel>
                                        <TextInput v-model="detailsForm.tax_identifier" mono :invalid="!!detailsForm.errors.tax_identifier" />
                                    </div>
                                    <div class="sm:col-span-6">
                                        <FieldLabel>Account reference</FieldLabel>
                                        <TextInput v-model="detailsForm.account_ref" mono :invalid="!!detailsForm.errors.account_ref" />
                                    </div>
                                </div>
                            </Section>

                            <Section title="Customer groups">
                                <template #desc>Groups control storefront permissions, pricing rules, and segmentation.</template>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span
                                        v-for="id in detailsForm.customer_group_ids"
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
                                    <span v-if="!detailsForm.customer_group_ids.length" class="text-[12px] text-ink-500">No groups assigned.</span>
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
                            </Section>

                            <div class="pt-2 pb-6">
                                <Button type="submit" variant="primary" :disabled="detailsForm.processing">Save changes</Button>
                            </div>
                        </form>

                        <PanelSlot name="customers.edit:main:after" :customer="customer" />

                        <!-- Tabbed: addresses, users, activity -->
                        <div class="pt-2">
                            <Tabs v-model="activeTab" :tabs="tabDefs">
                                <template #actions>
                                    <Button v-if="activeTab === 'addresses'" size="sm" icon="plus" @click="showNewAddressForm = !showNewAddressForm">Add address</Button>
                                </template>

                                <template #addresses>
                                    <div v-if="addresses.length" class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                        <template v-for="address in addresses" :key="address.id">
                                            <form
                                                v-if="editingAddressId === address.id"
                                                class="bg-surface border border-line rounded-md p-3.5 flex flex-col gap-3"
                                                @submit.prevent="submitEditAddress(address)"
                                            >
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <FieldLabel required>First name</FieldLabel>
                                                        <TextInput v-model="addressForm.first_name" />
                                                    </div>
                                                    <div>
                                                        <FieldLabel required>Last name</FieldLabel>
                                                        <TextInput v-model="addressForm.last_name" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <FieldLabel>Company name</FieldLabel>
                                                    <TextInput v-model="addressForm.company_name" />
                                                </div>
                                                <div>
                                                    <FieldLabel required>Address line 1</FieldLabel>
                                                    <TextInput v-model="addressForm.line_one" />
                                                </div>
                                                <div>
                                                    <FieldLabel>Address line 2</FieldLabel>
                                                    <TextInput v-model="addressForm.line_two" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <FieldLabel required>City</FieldLabel>
                                                        <TextInput v-model="addressForm.city" />
                                                    </div>
                                                    <div>
                                                        <FieldLabel>Postcode</FieldLabel>
                                                        <TextInput v-model="addressForm.postcode" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <FieldLabel required>Country</FieldLabel>
                                                    <Select
                                                        :model-value="addressForm.country_id"
                                                        @update:model-value="(value) => (addressForm.country_id = value ? Number(value) : '')"
                                                    >
                                                        <option value="">Select a country</option>
                                                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                                                    </Select>
                                                </div>
                                                <div>
                                                    <FieldLabel>Delivery instructions</FieldLabel>
                                                    <Textarea v-model="addressForm.delivery_instructions" :rows="2" />
                                                </div>
                                                <div class="flex gap-4">
                                                    <label class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                                                        <Checkbox v-model="addressForm.shipping_default" />
                                                        Default shipping
                                                    </label>
                                                    <label class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                                                        <Checkbox v-model="addressForm.billing_default" />
                                                        Default billing
                                                    </label>
                                                </div>
                                                <div class="flex gap-2">
                                                    <Button type="submit" variant="primary" size="sm" :disabled="addressForm.processing">Save address</Button>
                                                    <Button type="button" size="sm" @click="cancelEditAddress">Cancel</Button>
                                                </div>
                                            </form>
                                            <AddressCard v-else :address="cardAddress(address)">
                                                <template #actions>
                                                    <Button variant="ghost" size="sm" icon="edit" @click="startEditAddress(address)">Edit</Button>
                                                    <Button v-if="!address.billing_default" variant="ghost" size="sm" @click="setAddressDefault(address, 'billing_default')">Set default billing</Button>
                                                    <Button v-if="!address.shipping_default" variant="ghost" size="sm" @click="setAddressDefault(address, 'shipping_default')">Set default shipping</Button>
                                                    <div class="flex-1" />
                                                    <Button variant="ghost" size="sm" icon="trash" aria-label="Remove address" @click="destroyAddress(address)" />
                                                </template>
                                            </AddressCard>
                                        </template>
                                    </div>
                                    <PageEmpty v-else title="No saved addresses">Addresses entered at checkout will appear here.</PageEmpty>

                                    <form
                                        v-if="showNewAddressForm"
                                        class="mt-3 rounded-md border border-dashed border-line-strong p-4"
                                        @submit.prevent="submitNewAddress"
                                    >
                                        <h3 class="text-[13px] font-semibold text-ink-900 mb-3">Add address</h3>
                                        <div class="flex flex-col gap-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <FieldLabel required>First name</FieldLabel>
                                                    <TextInput v-model="newAddressForm.first_name" :invalid="!!newAddressForm.errors.first_name" />
                                                </div>
                                                <div>
                                                    <FieldLabel required>Last name</FieldLabel>
                                                    <TextInput v-model="newAddressForm.last_name" :invalid="!!newAddressForm.errors.last_name" />
                                                </div>
                                            </div>
                                            <div>
                                                <FieldLabel>Company name</FieldLabel>
                                                <TextInput v-model="newAddressForm.company_name" />
                                            </div>
                                            <div>
                                                <FieldLabel required>Address line 1</FieldLabel>
                                                <TextInput v-model="newAddressForm.line_one" :invalid="!!newAddressForm.errors.line_one" />
                                            </div>
                                            <div>
                                                <FieldLabel>Address line 2</FieldLabel>
                                                <TextInput v-model="newAddressForm.line_two" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <FieldLabel required>City</FieldLabel>
                                                    <TextInput v-model="newAddressForm.city" :invalid="!!newAddressForm.errors.city" />
                                                </div>
                                                <div>
                                                    <FieldLabel>Postcode</FieldLabel>
                                                    <TextInput v-model="newAddressForm.postcode" />
                                                </div>
                                            </div>
                                            <div>
                                                <FieldLabel required>Country</FieldLabel>
                                                <Select
                                                    :model-value="newAddressForm.country_id"
                                                    @update:model-value="(value) => (newAddressForm.country_id = value ? Number(value) : '')"
                                                >
                                                    <option value="">Select a country</option>
                                                    <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                                                </Select>
                                                <div v-if="newAddressForm.errors.country_id" class="mt-1 text-[11px] text-danger">{{ newAddressForm.errors.country_id }}</div>
                                            </div>
                                            <div>
                                                <FieldLabel>Delivery instructions</FieldLabel>
                                                <Textarea v-model="newAddressForm.delivery_instructions" :rows="2" />
                                            </div>
                                            <div class="flex gap-4">
                                                <label class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                                                    <Checkbox v-model="newAddressForm.shipping_default" />
                                                    Default shipping
                                                </label>
                                                <label class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer">
                                                    <Checkbox v-model="newAddressForm.billing_default" />
                                                    Default billing
                                                </label>
                                            </div>
                                            <div class="flex gap-2">
                                                <Button type="submit" variant="primary" size="sm" :disabled="newAddressForm.processing">Add address</Button>
                                                <Button type="button" size="sm" @click="showNewAddressForm = false">Cancel</Button>
                                            </div>
                                        </div>
                                    </form>
                                </template>

                                <template #users>
                                    <p class="text-xs text-ink-500 mb-3 max-w-[640px]">People who can sign in as this customer.</p>
                                    <div v-if="users.length" class="bg-surface border border-line rounded-xl shadow-sm overflow-hidden">
                                        <table class="w-full border-collapse text-[13px]">
                                            <thead>
                                                <tr class="text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium bg-surface-2 border-b border-line">
                                                    <th class="text-left font-medium px-4 py-2.5">Name</th>
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
                                                        <Button variant="ghost" size="sm" icon="trash" aria-label="Unlink user" @click="unlinkUser(user)" />
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <PageEmpty v-else title="No users yet">Invite a user so this customer can sign in.</PageEmpty>

                                    <form class="mt-3 flex items-end gap-2 rounded-md border border-dashed border-line-strong p-4" @submit.prevent="submitLinkUser">
                                        <div class="flex-1">
                                            <FieldLabel>Link user by email</FieldLabel>
                                            <TextInput v-model="linkUserForm.email" type="email" :invalid="!!linkUserForm.errors.email" />
                                            <div v-if="linkUserForm.errors.email" class="mt-1 text-[11px] text-danger">{{ linkUserForm.errors.email }}</div>
                                        </div>
                                        <Button type="submit" :disabled="linkUserForm.processing">Link</Button>
                                    </form>
                                </template>

                                <template #activity>
                                    <ActivityTimeline :events="activities" :reverse="false" />
                                    <PageEmpty v-if="!activities.length" title="No activity recorded yet" />
                                </template>
                            </Tabs>
                        </div>
                    </div>

                    <aside>
                        <div class="lg:sticky lg:top-4 flex flex-col gap-0">
                            <SideCard title="At a glance">
                                <div class="flex flex-col gap-2 text-[12px]">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">Type</span>
                                        <span class="text-ink-900 font-medium">{{ customer.company_name ? 'Business' : 'Individual' }}</span>
                                    </div>
                                    <div v-if="customer.account_ref" class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">Account ref</span>
                                        <span class="text-ink-900 font-mono">{{ customer.account_ref }}</span>
                                    </div>
                                    <div v-if="customer.tax_identifier" class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">Tax ID</span>
                                        <span class="text-ink-900 font-mono">{{ customer.tax_identifier }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-ink-500">Joined</span>
                                        <span class="text-ink-900 [font-variant-numeric:tabular-nums]">{{ new Date(customer.created_at).toLocaleDateString() }}</span>
                                    </div>
                                    <div v-if="customer.customer_groups.length" class="border-t border-line pt-2 mt-1 flex flex-wrap gap-1">
                                        <StatusBadge v-for="group in customer.customer_groups" :key="group.id" size="sm">{{ group.name }}</StatusBadge>
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard title="Default addresses">
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <div class="text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium mb-1">Billing</div>
                                        <div v-if="defaultBilling" class="text-[12.5px] text-ink-700 leading-[1.5]">
                                            <div class="text-ink-900">{{ defaultBilling.first_name }} {{ defaultBilling.last_name }}</div>
                                            <div>{{ defaultBilling.line_one }}<span v-if="defaultBilling.line_two">, {{ defaultBilling.line_two }}</span></div>
                                            <div>{{ defaultBilling.city }}<span v-if="defaultBilling.state">, {{ defaultBilling.state }}</span></div>
                                            <div>{{ defaultBilling.postcode }}</div>
                                            <div class="text-ink-500">{{ countryName(defaultBilling.country_id) }}</div>
                                        </div>
                                        <div v-else class="text-[12px] text-ink-500 italic">None set.</div>
                                    </div>
                                    <div class="border-t border-line pt-2.5">
                                        <div class="text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium mb-1">Shipping</div>
                                        <div v-if="defaultShipping" class="text-[12.5px] text-ink-700 leading-[1.5]">
                                            <div class="text-ink-900">{{ defaultShipping.first_name }} {{ defaultShipping.last_name }}</div>
                                            <div>{{ defaultShipping.line_one }}<span v-if="defaultShipping.line_two">, {{ defaultShipping.line_two }}</span></div>
                                            <div>{{ defaultShipping.city }}<span v-if="defaultShipping.state">, {{ defaultShipping.state }}</span></div>
                                            <div>{{ defaultShipping.postcode }}</div>
                                            <div class="text-ink-500">{{ countryName(defaultShipping.country_id) }}</div>
                                        </div>
                                        <div v-else class="text-[12px] text-ink-500 italic">None set.</div>
                                    </div>
                                </div>
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
                confirm-label="Delete"
                @confirm="confirmDestroy"
            />
        </div>
    </PanelLayout>
</template>
