<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import Button from '../../components/Button.vue';
import Checkbox from '../../components/Checkbox.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
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

const toggleGroup = (id: number): void => {
    const index = form.customer_group_ids.indexOf(id);

    if (index === -1) {
        form.customer_group_ids.push(id);
    } else {
        form.customer_group_ids.splice(index, 1);
    }
};

const submit = (): void => {
    form.post(props.urls.store);
};
</script>

<template>
    <PanelLayout>
    <div class="bg-canvas font-sans py-10">
        <div class="mx-auto flex max-w-xl flex-col gap-6 px-6">
            <div class="flex items-center gap-2">
                <Link :href="urls.index" class="text-ink-500 hover:text-ink-900">
                    <Icon name="arrowLeft" />
                </Link>
                <h1 class="text-2xl font-semibold tracking-[-0.02em] text-ink-900">New customer</h1>
            </div>

            <form class="rounded-lg border border-line bg-paper p-6" @submit.prevent="submit">
                <div class="flex flex-col gap-3.5">
                    <div>
                        <FieldLabel>Title</FieldLabel>
                        <TextInput v-model="form.title" :invalid="!!form.errors.title" />
                        <div v-if="form.errors.title" class="mt-1 text-[11px] text-danger">{{ form.errors.title }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <FieldLabel required>First name</FieldLabel>
                            <TextInput v-model="form.first_name" :invalid="!!form.errors.first_name" />
                            <div v-if="form.errors.first_name" class="mt-1 text-[11px] text-danger">{{ form.errors.first_name }}</div>
                        </div>
                        <div>
                            <FieldLabel required>Last name</FieldLabel>
                            <TextInput v-model="form.last_name" :invalid="!!form.errors.last_name" />
                            <div v-if="form.errors.last_name" class="mt-1 text-[11px] text-danger">{{ form.errors.last_name }}</div>
                        </div>
                    </div>
                    <div>
                        <FieldLabel>Company name</FieldLabel>
                        <TextInput v-model="form.company_name" :invalid="!!form.errors.company_name" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <FieldLabel>Tax identifier</FieldLabel>
                            <TextInput v-model="form.tax_identifier" :invalid="!!form.errors.tax_identifier" />
                        </div>
                        <div>
                            <FieldLabel>Account ref</FieldLabel>
                            <TextInput v-model="form.account_ref" :invalid="!!form.errors.account_ref" />
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
                                    :model-value="form.customer_group_ids.includes(group.id)"
                                    @update:model-value="() => toggleGroup(group.id)"
                                />
                                {{ group.name }}
                            </label>
                        </div>
                    </div>

                    <div class="mt-2">
                        <Button type="submit" variant="primary" :disabled="form.processing">Create customer</Button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </PanelLayout>
</template>
