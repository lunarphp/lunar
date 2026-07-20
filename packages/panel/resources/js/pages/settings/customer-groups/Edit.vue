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

type CustomerGroup = {
    id: number;
    name: string;
    handle: string;
    default: boolean;
};

const props = defineProps<{
    customerGroup: CustomerGroup;
    hasCustomers: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('customer_groups.title'), href: props.urls.index },
    { label: props.customerGroup.name, current: true },
]);

const form = useForm({
    name: props.customerGroup.name,
    handle: props.customerGroup.handle,
    default: props.customerGroup.default,
});

const submit = (): void => {
    form.put(props.urls.update);
};

// The default group cannot be deleted or un-defaulted; promote another group instead.
const deleteBlockedReason = computed<string>(() => {
    if (props.customerGroup.default) return t('customer_groups.delete_blocked_default');
    if (props.hasCustomers) return t('customer_groups.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('customer_groups.edit_title', { name: customerGroup.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('customer_groups.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('customer_groups.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel required :hint="t('customer_groups.handle_hint')">{{ t('customer_groups.field_handle') }}</FieldLabel>
                    <TextInput v-model="form.handle" mono :invalid="!!form.errors.handle" />
                    <div v-if="form.errors.handle" class="mt-1 text-[11px] text-danger">{{ form.errors.handle }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('customer_groups.section_state')">
            <label class="flex items-center gap-3" :class="customerGroup.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                <Toggle :on="form.default" :disabled="customerGroup.default" @toggle="form.default = !form.default" />
                <div>
                    <div class="text-[12.5px] text-ink-900 font-medium">{{ t('customer_groups.default_group') }}</div>
                    <div class="text-[11px] text-ink-500">
                        {{ customerGroup.default ? t('customer_groups.default_locked_hint') : t('customer_groups.default_group_hint') }}
                    </div>
                </div>
            </label>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('customer_groups.confirm_delete_title')"
        :description="t('customer_groups.confirm_delete_body', { name: customerGroup.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
