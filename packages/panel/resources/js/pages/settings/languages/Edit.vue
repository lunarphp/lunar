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

type Language = {
    id: number;
    code: string;
    name: string;
    default: boolean;
};

const props = defineProps<{
    language: Language;
    hasUrls: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('languages.title'), href: props.urls.index },
    { label: props.language.name, current: true },
]);

const form = useForm({
    code: props.language.code,
    name: props.language.name,
    default: props.language.default,
});

const submit = (): void => {
    form.put(props.urls.update);
};

// The default language cannot be deleted or un-defaulted; promote another language instead.
const deleteBlockedReason = computed<string>(() => {
    if (props.language.default) return t('languages.delete_blocked_default');
    if (props.hasUrls) return t('languages.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('languages.edit_title', { name: language.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('languages.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required :hint="t('languages.code_hint')">{{ t('languages.field_code') }}</FieldLabel>
                    <TextInput v-model="form.code" mono :invalid="!!form.errors.code" />
                    <div v-if="form.errors.code" class="mt-1 text-[11px] text-danger">{{ form.errors.code }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('languages.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('languages.section_state')">
            <label class="flex items-center gap-3" :class="language.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                <Toggle :on="form.default" :disabled="language.default" @toggle="form.default = !form.default" />
                <div>
                    <div class="text-[12.5px] text-ink-900 font-medium">{{ t('languages.default_language') }}</div>
                    <div class="text-[11px] text-ink-500">
                        {{ language.default ? t('languages.default_locked_hint') : t('languages.default_language_hint') }}
                    </div>
                </div>
            </label>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('languages.confirm_delete_title')"
        :description="t('languages.confirm_delete_body', { name: language.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
