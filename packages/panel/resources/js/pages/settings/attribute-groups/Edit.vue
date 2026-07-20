<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import DataTable from '../../../components/DataTable.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Section from '../../../components/Section.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type AttributeGroup = {
    id: number;
    name: string;
    handle: string;
    position: number;
    system: boolean;
};

type GroupAttribute = {
    id: number;
    name: string;
    handle: string;
    type: string;
    system: boolean;
    urls: { edit: string };
};

const props = defineProps<{
    attributeGroup: AttributeGroup;
    attributes: GroupAttribute[];
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('attribute_groups.title'), href: props.urls.index },
    { label: props.attributeGroup.name, current: true },
]);

const form = useForm({
    name: props.attributeGroup.name,
    handle: props.attributeGroup.handle,
    position: String(props.attributeGroup.position),
});

const submit = (): void => {
    form.put(props.urls.update);
};

// System groups belong to Lunar; groups with attributes must be emptied first.
const deleteBlockedReason = computed<string>(() => {
    if (props.attributeGroup.system) return t('attribute_groups.delete_blocked_system');
    if (props.attributes.length > 0) return t('attribute_groups.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};

const attributeColumns = [
    { key: 'name', label: t('attributes_settings.column_name'), width: 'minmax(0, 1.3fr)' },
    { key: 'handle', label: t('attributes_settings.column_handle'), width: 'minmax(0, 1fr)' },
    { key: 'type', label: t('attributes_settings.column_type'), width: '140px' },
];

const attributeTo = (row: Record<string, unknown>): string => (row as unknown as GroupAttribute).urls.edit;
</script>

<template>
    <SettingsShell :title="t('attribute_groups.edit_title', { name: attributeGroup.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('attribute_groups.section_details')">
            <div class="grid sm:grid-cols-3 gap-3">
                <div>
                    <FieldLabel required>{{ t('attribute_groups.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :disabled="attributeGroup.system" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel required :hint="t('attribute_groups.handle_hint')">{{ t('attribute_groups.field_handle') }}</FieldLabel>
                    <TextInput v-model="form.handle" mono :disabled="attributeGroup.system" :invalid="!!form.errors.handle" />
                    <div v-if="form.errors.handle" class="mt-1 text-[11px] text-danger">{{ form.errors.handle }}</div>
                </div>
                <div>
                    <FieldLabel>{{ t('attribute_groups.field_position') }}</FieldLabel>
                    <TextInput v-model="form.position" type="number" min="1" :invalid="!!form.errors.position" />
                    <div v-if="form.errors.position" class="mt-1 text-[11px] text-danger">{{ form.errors.position }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('attribute_groups.section_attributes', { count: attributes.length })">
            <template #desc>{{ t('attribute_groups.attributes_desc') }}</template>

            <DataTable :columns="attributeColumns" :rows="attributes" :row-to="attributeTo">
                <template #cell-name="{ row }">
                    <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as GroupAttribute).name }}</span>
                    <StatusBadge v-if="(row as unknown as GroupAttribute).system" tone="archived" size="sm" class="ml-2">{{ t('attributes_settings.system_badge') }}</StatusBadge>
                </template>
                <template #cell-handle="{ row }">
                    <span class="font-mono text-xs text-ink-700">{{ (row as unknown as GroupAttribute).handle }}</span>
                </template>
                <template #cell-type="{ row }">
                    <span class="text-xs text-ink-700">{{ t(`attributes_settings.type_${(row as unknown as GroupAttribute).type}`) }}</span>
                </template>
                <template #empty>
                    <PageEmpty :title="t('attribute_groups.empty_attributes_title')" />
                </template>
            </DataTable>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('attribute_groups.confirm_delete_title')"
        :description="t('attribute_groups.confirm_delete_body', { name: attributeGroup.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
