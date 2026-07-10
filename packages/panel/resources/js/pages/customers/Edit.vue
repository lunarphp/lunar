<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import Button from '../../components/Button.vue';
import Checkbox from '../../components/Checkbox.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';

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
    contact_mail: string | null;
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

const tabs = ['addresses', 'users', 'activity'] as const;
const activeTab = ref<'addresses' | 'users' | 'activity'>('addresses');

const detailsForm = useForm({
    title: props.customer.title ?? '',
    first_name: props.customer.first_name,
    last_name: props.customer.last_name,
    company_name: props.customer.company_name ?? '',
    tax_identifier: props.customer.tax_identifier ?? '',
    account_ref: props.customer.account_ref ?? '',
    customer_group_ids: props.customer.customer_groups.map((group) => group.id),
});

const toggleGroup = (id: number): void => {
    const index = detailsForm.customer_group_ids.indexOf(id);

    if (index === -1) {
        detailsForm.customer_group_ids.push(id);
    } else {
        detailsForm.customer_group_ids.splice(index, 1);
    }
};

const submitDetails = (): void => {
    detailsForm.put(props.urls.update, { preserveScroll: true });
};

const destroyCustomer = (): void => {
    if (confirm('Delete this customer? This cannot be undone.')) {
        router.delete(props.urls.destroy);
    }
};

// Addresses tab
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
    contact_mail: '',
    contact_phone: '',
    shipping_default: false,
    billing_default: false,
});

const newAddressForm = useForm(emptyAddress());

const submitNewAddress = (): void => {
    newAddressForm.post(props.urls.addressesStore, {
        preserveScroll: true,
        onSuccess: () => newAddressForm.reset(),
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
    addressForm.contact_mail = address.contact_mail ?? '';
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
    if (confirm('Delete this address?')) {
        router.delete(address.destroy_url, { preserveScroll: true });
    }
};

// Users tab
const linkUserForm = useForm({ email: '' });

const submitLinkUser = (): void => {
    linkUserForm.post(props.urls.usersStore, {
        preserveScroll: true,
        onSuccess: () => linkUserForm.reset(),
    });
};

const unlinkUser = (user: LinkedUser): void => {
    if (confirm(`Unlink ${user.email}?`)) {
        router.delete(user.unlink_url, { preserveScroll: true });
    }
};
</script>

<template>
    <div class="min-h-screen bg-canvas font-sans py-10">
        <div class="mx-auto flex max-w-3xl flex-col gap-6 px-6">
            <div class="flex items-center gap-2">
                <Link :href="urls.index" class="text-ink-500 hover:text-ink-900">
                    <Icon name="arrowLeft" />
                </Link>
                <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">
                    {{ customer.first_name }} {{ customer.last_name }}
                </h1>
            </div>

            <div v-if="flashSuccess" class="rounded-md border border-sage-border bg-sage-soft px-3 py-2 text-[12px] text-sage-ink">
                {{ flashSuccess }}
            </div>

            <!-- Personal details -->
            <section class="rounded-lg border border-line bg-paper p-6">
                <h2 class="text-[15px] font-semibold text-ink-900">Personal details</h2>

                <form class="mt-4 flex flex-col gap-3.5" @submit.prevent="submitDetails">
                    <div>
                        <FieldLabel>Title</FieldLabel>
                        <TextInput v-model="detailsForm.title" :invalid="!!detailsForm.errors.title" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <FieldLabel required>First name</FieldLabel>
                            <TextInput v-model="detailsForm.first_name" :invalid="!!detailsForm.errors.first_name" />
                            <div v-if="detailsForm.errors.first_name" class="mt-1 text-[11px] text-danger">{{ detailsForm.errors.first_name }}</div>
                        </div>
                        <div>
                            <FieldLabel required>Last name</FieldLabel>
                            <TextInput v-model="detailsForm.last_name" :invalid="!!detailsForm.errors.last_name" />
                            <div v-if="detailsForm.errors.last_name" class="mt-1 text-[11px] text-danger">{{ detailsForm.errors.last_name }}</div>
                        </div>
                    </div>
                    <div>
                        <FieldLabel>Company name</FieldLabel>
                        <TextInput v-model="detailsForm.company_name" :invalid="!!detailsForm.errors.company_name" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <FieldLabel>Tax identifier</FieldLabel>
                            <TextInput v-model="detailsForm.tax_identifier" :invalid="!!detailsForm.errors.tax_identifier" />
                        </div>
                        <div>
                            <FieldLabel>Account ref</FieldLabel>
                            <TextInput v-model="detailsForm.account_ref" :invalid="!!detailsForm.errors.account_ref" />
                        </div>
                    </div>

                    <div v-if="customerGroups.length">
                        <FieldLabel>Customer groups</FieldLabel>
                        <div class="flex flex-col gap-1.5">
                            <label
                                v-for="group in customerGroups"
                                :key="group.id"
                                class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer"
                            >
                                <Checkbox
                                    :model-value="detailsForm.customer_group_ids.includes(group.id)"
                                    @update:model-value="() => toggleGroup(group.id)"
                                />
                                {{ group.name }}
                            </label>
                        </div>
                    </div>

                    <div>
                        <Button type="submit" variant="primary" :disabled="detailsForm.processing">Save changes</Button>
                    </div>
                </form>
            </section>

            <!-- Tabs -->
            <section class="rounded-lg border border-line bg-paper p-6">
                <div class="flex gap-1 border-b border-line pb-3">
                    <button
                        v-for="tab in tabs"
                        :key="tab"
                        type="button"
                        class="rounded-md px-3 py-1.5 text-[13px] font-medium capitalize transition-colors"
                        :class="activeTab === tab ? 'bg-ink-900 text-paper' : 'text-ink-500 hover:bg-surface-2'"
                        @click="activeTab = tab"
                    >
                        {{ tab }}
                    </button>
                </div>

                <!-- Addresses -->
                <div v-if="activeTab === 'addresses'" class="mt-5 flex flex-col gap-4">
                    <div v-for="address in addresses" :key="address.id" class="rounded-md border border-line p-4">
                        <template v-if="editingAddressId === address.id">
                            <form class="flex flex-col gap-3" @submit.prevent="submitEditAddress(address)">
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
                                    <select
                                        v-model.number="addressForm.country_id"
                                        class="h-8 w-full rounded-md border border-line-strong bg-surface px-2.5 text-[13px] text-ink-900 outline-none focus:border-sage focus:ring-3 focus:ring-sage/35"
                                    >
                                        <option value="">Select a country</option>
                                        <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                                    </select>
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
                        </template>
                        <template v-else>
                            <div class="flex items-start justify-between">
                                <div class="text-[13px] text-ink-900">
                                    <div class="font-medium">{{ address.first_name }} {{ address.last_name }}</div>
                                    <div v-if="address.company_name" class="text-ink-500">{{ address.company_name }}</div>
                                    <div class="text-ink-500">{{ address.line_one }}<span v-if="address.line_two">, {{ address.line_two }}</span></div>
                                    <div class="text-ink-500">{{ address.city }}<span v-if="address.postcode">, {{ address.postcode }}</span></div>
                                    <div class="mt-1 flex gap-1.5">
                                        <span v-if="address.shipping_default" class="rounded-full border border-line-strong bg-surface-2 px-2 py-0.5 text-[11px] text-ink-700">Default shipping</span>
                                        <span v-if="address.billing_default" class="rounded-full border border-line-strong bg-surface-2 px-2 py-0.5 text-[11px] text-ink-700">Default billing</span>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <Button size="sm" @click="startEditAddress(address)">Edit</Button>
                                    <Button size="sm" icon="trash" @click="destroyAddress(address)" />
                                </div>
                            </div>
                        </template>
                    </div>
                    <div v-if="!addresses.length" class="text-[13px] text-ink-400">No addresses yet.</div>

                    <div class="rounded-md border border-dashed border-line-strong p-4">
                        <h3 class="text-[13px] font-semibold text-ink-900">Add address</h3>
                        <form class="mt-3 flex flex-col gap-3" @submit.prevent="submitNewAddress">
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
                                <select
                                    v-model.number="newAddressForm.country_id"
                                    class="h-8 w-full rounded-md border border-line-strong bg-surface px-2.5 text-[13px] text-ink-900 outline-none focus:border-sage focus:ring-3 focus:ring-sage/35"
                                >
                                    <option value="">Select a country</option>
                                    <option v-for="country in countries" :key="country.id" :value="country.id">{{ country.name }}</option>
                                </select>
                                <div v-if="newAddressForm.errors.country_id" class="mt-1 text-[11px] text-danger">{{ newAddressForm.errors.country_id }}</div>
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
                            <div>
                                <Button type="submit" variant="primary" size="sm" :disabled="newAddressForm.processing">Add address</Button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users -->
                <div v-if="activeTab === 'users'" class="mt-5 flex flex-col gap-4">
                    <div v-for="user in users" :key="user.id" class="flex items-center justify-between rounded-md border border-line p-3">
                        <div class="text-[13px] text-ink-900">
                            <div class="font-medium">{{ user.name ?? user.email }}</div>
                            <div v-if="user.name" class="text-ink-500">{{ user.email }}</div>
                        </div>
                        <Button size="sm" @click="unlinkUser(user)">Unlink</Button>
                    </div>
                    <div v-if="!users.length" class="text-[13px] text-ink-400">No storefront users linked.</div>

                    <form class="flex items-end gap-2 rounded-md border border-dashed border-line-strong p-4" @submit.prevent="submitLinkUser">
                        <div class="flex-1">
                            <FieldLabel>Link user by email</FieldLabel>
                            <TextInput v-model="linkUserForm.email" type="email" :invalid="!!linkUserForm.errors.email" />
                            <div v-if="linkUserForm.errors.email" class="mt-1 text-[11px] text-danger">{{ linkUserForm.errors.email }}</div>
                        </div>
                        <Button type="submit" :disabled="linkUserForm.processing">Link</Button>
                    </form>
                </div>

                <!-- Activity -->
                <div v-if="activeTab === 'activity'" class="mt-5 flex flex-col gap-3">
                    <div v-for="(activity, index) in activities" :key="index" class="border-b border-line pb-2 last:border-0">
                        <div class="text-[13px] text-ink-900">{{ activity.description }}</div>
                        <div class="text-[11px] text-ink-400">
                            {{ new Date(activity.created_at).toLocaleString() }}
                            <span v-if="activity.causer_name">— {{ activity.causer_name }}</span>
                        </div>
                    </div>
                    <div v-if="!activities.length" class="text-[13px] text-ink-400">No activity recorded yet.</div>
                </div>
            </section>

            <div class="flex justify-end">
                <Button icon="trash" @click="destroyCustomer">Delete customer</Button>
            </div>
        </div>
    </div>
</template>
