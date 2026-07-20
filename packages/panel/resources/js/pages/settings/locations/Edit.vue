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

type Location = {
    id: number;
    name: string;
    handle: string;
    default: boolean;
};

const props = defineProps<{
    location: Location;
    hasFulfilments: boolean;
    hasStock: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('locations.title'), href: props.urls.index },
    { label: props.location.name, current: true },
]);

const form = useForm({
    name: props.location.name,
    handle: props.location.handle,
    default: props.location.default,
});

const submit = (): void => {
    form.put(props.urls.update);
};

// The default location cannot be deleted or un-defaulted; promote another location instead.
const deleteBlockedReason = computed<string>(() => {
    if (props.location.default) return t('locations.delete_blocked_default');
    if (props.hasFulfilments || props.hasStock) return t('locations.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('locations.edit_title', { name: location.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('locations.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('locations.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel required :hint="t('locations.handle_hint')">{{ t('locations.field_handle') }}</FieldLabel>
                    <TextInput v-model="form.handle" mono :invalid="!!form.errors.handle" />
                    <div v-if="form.errors.handle" class="mt-1 text-[11px] text-danger">{{ form.errors.handle }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('locations.section_state')">
            <label class="flex items-center gap-3" :class="location.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                <Toggle :on="form.default" :disabled="location.default" @toggle="form.default = !form.default" />
                <div>
                    <div class="text-[12.5px] text-ink-900 font-medium">{{ t('locations.default_location') }}</div>
                    <div class="text-[11px] text-ink-500">
                        {{ location.default ? t('locations.default_locked_hint') : t('locations.default_location_hint') }}
                    </div>
                </div>
            </label>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('locations.confirm_delete_title')"
        :description="t('locations.confirm_delete_body', { name: location.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
