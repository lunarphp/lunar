<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import Checkbox from '../../../components/Checkbox.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import Section from '../../../components/Section.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Role = {
    id: number;
    name: string;
    firstParty: boolean;
    permissions: string[];
    staff_count: number;
};

type PermissionNode = {
    handle: string;
    label: string;
    description: string;
    children: PermissionNode[];
};

const props = defineProps<{
    role: Role;
    permissionGroups: PermissionNode[];
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('roles.title'), href: props.urls.index },
    { label: props.role.name, current: true },
]);

const form = useForm({
    permissions: [...props.role.permissions],
});

const has = (handle: string): boolean => form.permissions.includes(handle);

// Unticking a parent also drops its children, mirroring the manifest's
// parent/child permission grouping.
const toggle = (node: PermissionNode): void => {
    if (has(node.handle)) {
        const drop = [node.handle, ...node.children.map((child) => child.handle)];
        form.permissions = form.permissions.filter((handle) => !drop.includes(handle));
    } else {
        form.permissions.push(node.handle);
    }
};

const toggleChild = (parent: PermissionNode, child: PermissionNode): void => {
    if (has(child.handle)) {
        form.permissions = form.permissions.filter((handle) => handle !== child.handle);
    } else {
        form.permissions.push(child.handle);
        // A child implies its parent.
        if (!has(parent.handle)) {
            form.permissions.push(parent.handle);
        }
    }
};

const submit = (): void => {
    form.put(props.urls.update, { preserveScroll: true });
};

const deleteBlockedReason = computed<string>(() => {
    if (props.role.firstParty) return t('roles.delete_blocked_first_party');
    if (props.role.staff_count > 0) return t('roles.delete_blocked_staff');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('roles.edit_title', { name: role.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('roles.section_permissions')">
            <template #desc>{{ t('roles.permissions_desc') }}</template>
            <template v-if="role.firstParty" #actions>
                <StatusBadge tone="archived" size="md">{{ t('roles.first_party_badge') }}</StatusBadge>
            </template>

            <div class="flex flex-col gap-4">
                <div v-for="group in permissionGroups" :key="group.handle" class="border border-line rounded-xl p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <Checkbox :model-value="has(group.handle)" @update:model-value="toggle(group)" />
                        <div>
                            <div class="text-[12.5px] text-ink-900 font-medium">{{ group.label }}</div>
                            <div class="text-[11px] text-ink-500">{{ group.description }}</div>
                        </div>
                    </label>

                    <div v-if="group.children.length" class="mt-3 ml-7 flex flex-col gap-2">
                        <label v-for="child in group.children" :key="child.handle" class="flex items-start gap-3 cursor-pointer">
                            <Checkbox :model-value="has(child.handle)" @update:model-value="toggleChild(group, child)" />
                            <div>
                                <div class="text-[12.5px] text-ink-900">{{ child.label }}</div>
                                <div class="text-[11px] text-ink-500">{{ child.description }}</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('roles.confirm_delete_title')"
        :description="t('roles.confirm_delete_body', { name: role.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
