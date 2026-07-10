<script setup lang="ts">
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Button from '../../../components/Button.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import DataTable from '../../../components/DataTable.vue';
import DataTableActions from '../../../components/DataTableActions.vue';
import Dialog from '../../../components/Dialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import PageEmpty from '../../../components/PageEmpty.vue';
import Select from '../../../components/Select.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Channel = {
    id: number;
    name: string;
    handle: string;
    url: string | null;
    default: boolean;
    status: string | null;
    urls: { edit: string; destroy: string };
};

const props = defineProps<{
    channels: Channel[];
    urls: { store: string };
}>();

const columns = [
    { key: 'handle', label: 'Handle', width: 'minmax(0, 0.8fr)' },
    { key: 'name', label: 'Name', width: 'minmax(0, 1.2fr)' },
    { key: 'url', label: 'URL', width: 'minmax(0, 1.4fr)' },
    { key: 'status', label: 'Status', width: '110px' },
    { key: 'actions', label: '', width: '80px', align: 'right' as const },
];

const rowTo = (row: Record<string, unknown>): string => (row as unknown as Channel).urls.edit;

const creating = ref(false);
const createForm = useForm({
    name: '',
    url: '',
    default: false as boolean,
    status: 'active',
});

const openCreate = (): void => {
    createForm.reset();
    createForm.clearErrors();
    creating.value = true;
};

const submitCreate = (): void => {
    createForm.post(props.urls.store, {
        onSuccess: () => {
            createForm.reset();
            creating.value = false;
        },
    });
};

const deleting = ref<Channel | null>(null);

const confirmDelete = (): void => {
    if (!deleting.value) {
        return;
    }

    router.delete(deleting.value.urls.destroy);
    deleting.value = null;
};
</script>

<template>
    <SettingsShell title="Channels">
        <div class="flex justify-end mb-4">
            <Button variant="primary" icon="plus" size="sm" @click="openCreate">Create channel</Button>
        </div>

        <DataTable :columns="columns" :rows="channels" :row-to="rowTo">
            <template #cell-handle="{ row }">
                <span class="font-mono text-xs text-ink-700">{{ (row as unknown as Channel).handle }}</span>
            </template>
            <template #cell-name="{ row }">
                <span class="text-[12.5px] text-ink-900 font-medium">{{ (row as unknown as Channel).name }}</span>
                <StatusBadge v-if="(row as unknown as Channel).default" tone="sage" size="sm" class="ml-2">Default</StatusBadge>
            </template>
            <template #cell-url="{ row }">
                <span class="text-xs text-ink-500 truncate block">{{ (row as unknown as Channel).url || '—' }}</span>
            </template>
            <template #cell-status="{ row }">
                <StatusBadge :tone="(row as unknown as Channel).status === 'active' ? 'sage' : 'archived'" size="sm" dot>
                    {{ (row as unknown as Channel).status === 'active' ? 'Active' : 'Inactive' }}
                </StatusBadge>
            </template>
            <template #cell-actions="{ row }">
                <DataTableActions
                    :edit-to="(row as unknown as Channel).urls.edit"
                    :on-delete="!(row as unknown as Channel).default ? () => (deleting = row as unknown as Channel) : null"
                    :locked="(row as unknown as Channel).default"
                    lock-reason="Default channel cannot be deleted"
                />
            </template>
            <template #empty>
                <PageEmpty title="No channels" />
            </template>
        </DataTable>
    </SettingsShell>

    <Dialog
        v-model:open="creating"
        title="Create channel"
        description="Channels represent surfaces where customers buy from your store."
    >
        <div class="flex flex-col gap-3">
            <div>
                <FieldLabel required>Name</FieldLabel>
                <TextInput v-model="createForm.name" :invalid="!!createForm.errors.name" placeholder="e.g. Webstore" />
                <div v-if="createForm.errors.name" class="mt-1 text-[11px] text-danger">{{ createForm.errors.name }}</div>
            </div>
            <div>
                <FieldLabel>URL</FieldLabel>
                <TextInput v-model="createForm.url" type="url" :invalid="!!createForm.errors.url" placeholder="https://example.com" />
                <div v-if="createForm.errors.url" class="mt-1 text-[11px] text-danger">{{ createForm.errors.url }}</div>
            </div>
            <div>
                <FieldLabel>Status</FieldLabel>
                <Select v-model="createForm.status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </Select>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <Toggle :on="createForm.default" @toggle="createForm.default = !createForm.default" />
                <span class="text-[12.5px] text-ink-900 font-medium">Default channel</span>
            </label>
        </div>
        <template #footer>
            <Button variant="ghost" @click="creating = false">Cancel</Button>
            <Button variant="primary" :disabled="createForm.processing" @click="submitCreate">Create</Button>
        </template>
    </Dialog>

    <ConfirmDialog
        :open="!!deleting"
        title="Delete channel?"
        :description="deleting ? `&quot;${deleting.name}&quot; will be permanently removed.` : ''"
        confirm-label="Delete"
        tone="danger"
        @update:open="(v) => !v && (deleting = null)"
        @confirm="confirmDelete"
    />
</template>
</content>
