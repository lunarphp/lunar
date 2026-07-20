<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import Checkbox from '../../../components/Checkbox.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Section from '../../../components/Section.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type StaffMember = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
    admin: boolean;
    roles: string[];
};

type RoleOption = { handle: string; label: string };

const props = defineProps<{
    staff: StaffMember;
    roles: RoleOption[];
    isSelf: boolean;
    isLastAdmin: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('staff.title'), href: props.urls.index },
    { label: props.staff.full_name, current: true },
]);

const form = useForm({
    first_name: props.staff.first_name,
    last_name: props.staff.last_name,
    email: props.staff.email,
    password: '',
    admin: props.staff.admin,
    roles: [...props.staff.roles],
});

const toggleRole = (handle: string): void => {
    const index = form.roles.indexOf(handle);
    if (index >= 0) {
        form.roles.splice(index, 1);
    } else {
        form.roles.push(handle);
    }
};

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        password: data.password || null,
    })).put(props.urls.update);
};

// Your own account and the last admin are protected.
const deleteBlockedReason = computed<string>(() => {
    if (props.isSelf) return t('staff.delete_blocked_self');
    if (props.isLastAdmin) return t('staff.delete_blocked_last_admin');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('staff.edit_title', { name: staff.full_name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('staff.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('staff.field_first_name') }}</FieldLabel>
                    <TextInput v-model="form.first_name" :invalid="!!form.errors.first_name" />
                    <div v-if="form.errors.first_name" class="mt-1 text-[11px] text-danger">{{ form.errors.first_name }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('staff.field_last_name') }}</FieldLabel>
                    <TextInput v-model="form.last_name" :invalid="!!form.errors.last_name" />
                    <div v-if="form.errors.last_name" class="mt-1 text-[11px] text-danger">{{ form.errors.last_name }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('staff.field_email') }}</FieldLabel>
                    <TextInput v-model="form.email" type="email" :invalid="!!form.errors.email" />
                    <div v-if="form.errors.email" class="mt-1 text-[11px] text-danger">{{ form.errors.email }}</div>
                </div>
                <div>
                    <FieldLabel :hint="t('staff.password_hint')">{{ t('staff.field_password') }}</FieldLabel>
                    <TextInput v-model="form.password" type="password" :invalid="!!form.errors.password" />
                    <div v-if="form.errors.password" class="mt-1 text-[11px] text-danger">{{ form.errors.password }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('staff.section_access')">
            <div class="flex flex-col gap-4">
                <label class="flex items-center gap-3" :class="isLastAdmin ? 'cursor-not-allowed' : 'cursor-pointer'">
                    <Toggle :on="form.admin" :disabled="isLastAdmin" @toggle="form.admin = !form.admin" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('staff.admin') }}</div>
                        <div class="text-[11px] text-ink-500">
                            {{ isLastAdmin ? t('staff.last_admin_hint') : t('staff.admin_hint') }}
                        </div>
                    </div>
                </label>
                <div v-if="roles.length">
                    <FieldLabel>{{ t('staff.field_roles') }}</FieldLabel>
                    <div class="flex flex-wrap gap-x-4 gap-y-2 mt-1">
                        <label v-for="role in roles" :key="role.handle" class="flex items-center gap-2 cursor-pointer">
                            <Checkbox :model-value="form.roles.includes(role.handle)" @update:model-value="toggleRole(role.handle)" />
                            <span class="text-[12.5px] text-ink-900">{{ role.label }}</span>
                        </label>
                    </div>
                    <div v-if="form.errors.roles" class="mt-1 text-[11px] text-danger">{{ form.errors.roles }}</div>
                </div>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('staff.confirm_delete_title')"
        :description="t('staff.confirm_delete_body', { name: staff.full_name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
