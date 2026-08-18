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

type TaxClass = {
    id: number;
    name: string;
    default: boolean;
};

const props = defineProps<{
    taxClass: TaxClass;
    hasVariants: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('tax_classes.title'), href: props.urls.index },
    { label: props.taxClass.name, current: true },
]);

const form = useForm({
    name: props.taxClass.name,
    default: props.taxClass.default,
});

const submit = (): void => {
    form.put(props.urls.update);
};

// The default tax class cannot be deleted or un-defaulted; promote another class instead.
const deleteBlockedReason = computed<string>(() => {
    if (props.taxClass.default) return t('tax_classes.delete_blocked_default');
    if (props.hasVariants) return t('tax_classes.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('tax_classes.edit_title', { name: taxClass.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('tax_classes.section_details')">
            <div class="flex flex-col gap-4">
                <div class="max-w-[360px]">
                    <FieldLabel required>{{ t('tax_classes.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <label class="flex items-center gap-3" :class="taxClass.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                    <Toggle :on="form.default" :disabled="taxClass.default" @toggle="form.default = !form.default" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('tax_classes.default_tax_class') }}</div>
                        <div class="text-[11px] text-ink-500">
                            {{ taxClass.default ? t('tax_classes.default_locked_hint') : t('tax_classes.default_tax_class_hint') }}
                        </div>
                    </div>
                </label>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('tax_classes.confirm_delete_title')"
        :description="t('tax_classes.confirm_delete_body', { name: taxClass.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
