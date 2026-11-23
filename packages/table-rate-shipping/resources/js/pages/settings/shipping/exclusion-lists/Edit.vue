<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, ConfirmDialog, FieldLabel, Section, SettingsShell, TargetChipList, TargetPickerDialog, TextInput, useLeaveGuard } from '@lunarphp/panel';
import type { TargetChip, TargetOption } from '@lunarphp/panel';
import { noLayout } from '../../../../lib/layout';

defineOptions({ layout: noLayout });

const props = defineProps<{
    list: { id: number; name: string; zones: { id: number; name: string; url: string }[] };
    products: TargetChip[];
    urls: { update: string; destroy: string; index: string; search: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed(() => [
    { label: t('nav.settings') },
    { label: t('shipping::exclusion_lists.title'), href: props.urls.index },
    { label: props.list.name, current: true },
]);

// The product chips live in the form, so picking or removing one marks the
// page dirty until Save; saving sends their ids as the product set.
const form = useForm({ name: props.list.name, products: props.products.map((chip) => ({ ...chip })) });

useLeaveGuard(() => form.isDirty);

// Errors for the product set arrive keyed per item (`products.2`); show the first.
const productsError = computed<string | undefined>(() => {
    const errors = form.errors as Record<string, string | undefined>;

    return errors.products ?? Object.entries(errors).find(([key]) => key.startsWith('products.'))?.[1];
});

const submit = (): void => {
    form.transform((data) => ({ ...data, products: data.products.map((chip) => chip.id) }))
        .put(props.urls.update, { preserveScroll: true });
};

const picking = ref(false);

const addProducts = (targets: TargetOption[]): void => {
    targets.forEach((target) => {
        if (!form.products.some((chip) => chip.id === target.id)) {
            form.products.push({ id: target.id, label: target.label, hint: target.hint });
        }
    });
};

const removeProduct = (_kind: string, id: number): void => {
    form.products = form.products.filter((chip) => chip.id !== id);
};

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('shipping::exclusion_lists.edit_title', { name: list.name })" :breadcrumbs="breadcrumbs" wide>
        <template #actions>
            <Button variant="ghost" icon="trash" @click="deleting = true">{{ t('common.delete') }}</Button>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <div data-screen-label="Edit shipping exclusion list">
            <Section :title="t('shipping::exclusion_lists.section_details')">
                <div class="max-w-[420px]">
                    <FieldLabel required>{{ t('shipping::exclusion_lists.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
            </Section>

            <Section :title="t('shipping::exclusion_lists.section_products')">
                <template #desc>{{ t('shipping::exclusion_lists.products_desc') }}</template>
                <TargetChipList
                    :chips="{ products: form.products }"
                    :kinds="['products']"
                    :label="t('shipping::exclusion_lists.kind_products')"
                    @add="picking = true"
                    @remove="removeProduct"
                />
                <div v-if="productsError" class="mt-1 text-[11px] text-danger">{{ productsError }}</div>
            </Section>

            <Section :title="t('shipping::exclusion_lists.section_zones')">
                <template #desc>{{ t('shipping::exclusion_lists.zones_desc') }}</template>
                <div v-if="!list.zones.length" class="text-xs text-ink-500">{{ t('shipping::exclusion_lists.no_zones') }}</div>
                <div v-else class="flex flex-wrap gap-1.5">
                    <Link
                        v-for="zone in list.zones"
                        :key="zone.id"
                        :href="zone.url"
                        class="inline-flex items-center px-2.5 py-1 rounded-full bg-surface-2 border border-line text-xs text-ink-900 hover:border-ink-400"
                    >
                        {{ zone.name }}
                    </Link>
                </div>
            </Section>
        </div>
    </SettingsShell>

    <TargetPickerDialog v-model:open="picking" :search-url="urls.search" bucket="exclusions" :kinds="['products']" @add="addProducts" />

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('shipping::exclusion_lists.confirm_delete_title')"
        :description="t('shipping::exclusion_lists.confirm_delete_body', { name: list.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
