<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Section from '../../../components/Section.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type ProductOption = {
    id: number;
    name: string;
    handle: string | null;
    shared: boolean;
};

type OptionValue = {
    id: number | null;
    name: string;
    position: number;
    inUse: boolean;
};

const props = defineProps<{
    productOption: ProductOption;
    values: OptionValue[];
    hasProducts: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('product_options.title'), href: props.urls.index },
    { label: props.productOption.name, current: true },
]);

const form = useForm({
    name: props.productOption.name,
    handle: props.productOption.handle ?? '',
    shared: props.productOption.shared,
    values: props.values.map((value) => ({ ...value })),
});

const addValue = (): void => {
    const nextPosition = form.values.reduce((max, value) => Math.max(max, value.position), 0) + 1;

    form.values.push({ id: null, name: '', position: nextPosition, inUse: false });
};

const removeValue = (index: number): void => {
    form.values.splice(index, 1);
};

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        values: data.values.map(({ id, name, position }) => ({ id, name, position })),
    })).put(props.urls.update, { preserveScroll: true });
};

// Options linked to products or with values on variants cannot be deleted.
const deleteBlockedReason = computed<string>(() => {
    if (props.hasProducts) return t('product_options.delete_blocked');
    if (props.values.some((value) => value.inUse)) return t('product_options.delete_blocked_values');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('product_options.edit_title', { name: productOption.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('product_options.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('product_options.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel :hint="t('product_options.handle_hint')">{{ t('product_options.field_handle') }}</FieldLabel>
                    <TextInput v-model="form.handle" mono :invalid="!!form.errors.handle" />
                    <div v-if="form.errors.handle" class="mt-1 text-[11px] text-danger">{{ form.errors.handle }}</div>
                </div>
                <label class="sm:col-span-2 flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.shared" @toggle="form.shared = !form.shared" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('product_options.shared') }}</div>
                        <div class="text-[11px] text-ink-500">{{ t('product_options.shared_hint') }}</div>
                    </div>
                </label>
            </div>
        </Section>

        <Section :title="t('product_options.section_values', { count: form.values.length })">
            <template #desc>{{ t('product_options.values_desc') }}</template>
            <template #actions>
                <Button variant="primary" size="sm" icon="plus" @click="addValue">{{ t('product_options.add_value') }}</Button>
            </template>

            <div v-if="!form.values.length" class="px-6 py-8 text-center text-xs text-ink-500 border border-dashed border-line rounded-md">
                {{ t('product_options.no_values') }}
            </div>

            <div v-else class="flex flex-col gap-2">
                <div v-for="(value, index) in form.values" :key="value.id ?? `new-${index}`" class="grid sm:grid-cols-[1fr_110px_auto] gap-2 items-center">
                    <div>
                        <TextInput v-model="value.name" :placeholder="t('product_options.value_placeholder')" />
                        <div v-if="(form.errors as Record<string, string>)[`values.${index}.name`]" class="mt-1 text-[11px] text-danger">
                            {{ (form.errors as Record<string, string>)[`values.${index}.name`] }}
                        </div>
                    </div>
                    <TextInput v-model.number="value.position" type="number" min="1" />
                    <Tooltip :text="value.inUse ? t('product_options.value_delete_blocked') : ''">
                        <Button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            :aria-label="t('product_options.remove_value')"
                            class="!w-[26px] !h-[26px] text-ink-700 hover:text-danger"
                            :disabled="value.inUse"
                            @click="removeValue(index)"
                        />
                    </Tooltip>
                </div>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('product_options.confirm_delete_title')"
        :description="t('product_options.confirm_delete_body', { name: productOption.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
