<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Section from '../../../components/Section.vue';
import Select from '../../../components/Select.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Channel = {
    id: number;
    name: string;
    handle: string;
    url: string | null;
    default: boolean;
    status: string | null;
};

const props = defineProps<{
    channel: Channel;
    hasOrderHistory: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('channels.title'), href: props.urls.index },
    { label: props.channel.name, current: true },
]);

const form = useForm({
    name: props.channel.name,
    url: props.channel.url ?? '',
    default: props.channel.default,
    status: props.channel.status ?? 'active',
});

const submit = (): void => {
    form.put(props.urls.update);
};

// The default channel cannot be deleted or un-defaulted; promote another channel instead.
const deleteBlockedReason = computed<string>(() => {
    if (props.channel.default) return t('channels.delete_blocked_default');
    if (props.hasOrderHistory) return t('channels.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('channels.edit_title', { name: channel.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('channels.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('channels.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel :hint="t('channels.handle_hint')">{{ t('channels.field_handle') }}</FieldLabel>
                    <TextInput :model-value="channel.handle" disabled mono />
                </div>
                <div class="sm:col-span-2">
                    <FieldLabel>{{ t('channels.field_url') }}</FieldLabel>
                    <TextInput v-model="form.url" type="url" :invalid="!!form.errors.url" placeholder="https://example.com" />
                    <div v-if="form.errors.url" class="mt-1 text-[11px] text-danger">{{ form.errors.url }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('channels.section_state')">
            <div class="flex flex-col gap-4">
                <label class="flex items-center gap-3" :class="channel.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                    <Toggle :on="form.default" :disabled="channel.default" @toggle="form.default = !form.default" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('channels.default_channel') }}</div>
                        <div class="text-[11px] text-ink-500">
                            {{ channel.default ? t('channels.default_locked_hint') : t('channels.default_channel_hint') }}
                        </div>
                    </div>
                </label>
                <div class="max-w-[220px]">
                    <FieldLabel>{{ t('channels.field_status') }}</FieldLabel>
                    <Select v-model="form.status">
                        <option value="active">{{ t('common.active') }}</option>
                        <option value="inactive">{{ t('common.inactive') }}</option>
                    </Select>
                </div>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('channels.confirm_delete_title')"
        :description="t('channels.confirm_delete_body', { name: channel.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
