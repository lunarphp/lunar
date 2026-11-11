<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, ConfirmDialog, FieldLabel, Section, SettingsShell, TargetChipList, TargetPickerDialog, TextInput } from '@lunarphp/panel';
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

// The chips are the form state: saving sends their ids as the product set.
const chips = ref<TargetChip[]>(props.products.map((chip) => ({ ...chip })));

const form = useForm({ name: props.list.name, products: [] as number[] });

const submit = (): void => {
    form.transform((data) => ({ ...data, products: chips.value.map((chip) => chip.id) }))
        .put(props.urls.update, { preserveScroll: true });
};

const picking = ref(false);

const addProducts = (targets: TargetOption[]): void => {
    targets.forEach((target) => {
        if (!chips.value.some((chip) => chip.id === target.id)) {
            chips.value.push({ id: target.id, label: target.label, hint: target.hint });
        }
    });
};

const removeProduct = (_kind: string, id: number): void => {
    chips.value = chips.value.filter((chip) => chip.id !== id);
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
                    :chips="{ products: chips }"
                    :kinds="['products']"
                    :label="t('shipping::exclusion_lists.kind_products')"
                    @add="picking = true"
                    @remove="removeProduct"
                />
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
