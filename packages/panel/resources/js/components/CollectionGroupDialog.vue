<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import TextInput from './TextInput.vue';

export interface CollectionGroupOption {
    id: number;
    name: string;
    handle: string;
    collections_count: number;
    urls: { update: string; destroy: string };
}

const props = defineProps<{
    open: boolean;
    // Null creates a new group; a group edits (and can delete) it.
    group: CollectionGroupOption | null;
    storeUrl: string;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const name = ref('');
const handle = ref('');
const errors = ref<Record<string, string>>({});
const saving = ref(false);
const confirmDeleteOpen = ref(false);

// The handle mirrors the name until staff edit it by hand.
let handleManuallyEdited = false;

const slugify = (value: string): string =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');

watch(
    () => props.open,
    (open) => {
        if (open) {
            name.value = props.group?.name ?? '';
            handle.value = props.group?.handle ?? '';
            handleManuallyEdited = props.group !== null;
            errors.value = {};
        }
    },
);

const onName = (value: string): void => {
    name.value = value;

    if (!handleManuallyEdited) {
        handle.value = slugify(value);
    }
};

const onHandle = (value: string): void => {
    handleManuallyEdited = true;
    handle.value = value;
};

const canDelete = computed(() => props.group !== null && props.group.collections_count === 0);

const submit = (): void => {
    const payload = { name: name.value, handle: handle.value || undefined };
    const options = {
        preserveScroll: true,
        onStart: () => {
            saving.value = true;
        },
        onSuccess: () => {
            emit('update:open', false);
        },
        onError: (pageErrors: Record<string, string>) => {
            errors.value = pageErrors;
        },
        onFinish: () => {
            saving.value = false;
        },
    };

    if (props.group) {
        router.put(props.group.urls.update, payload, options);
    } else {
        router.post(props.storeUrl, payload, options);
    }
};

const destroyGroup = (): void => {
    confirmDeleteOpen.value = false;

    if (!props.group) {
        return;
    }

    router.delete(props.group.urls.destroy, {
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
    });
};
</script>

<template>
    <Dialog
        :open="open"
        size="sm"
        :title="group ? t('collections.group_dialog_edit_title') : t('collections.group_dialog_create_title')"
        :description="t('collections.group_dialog_description')"
        @update:open="emit('update:open', $event)"
    >
        <form class="grid grid-cols-1 gap-3" @submit.prevent="submit">
            <div>
                <FieldLabel for="group-name" required>{{ t('collections.group_field_name') }}</FieldLabel>
                <TextInput id="group-name" :model-value="name" :invalid="!!errors.name" autofocus @update:model-value="onName" />
                <div v-if="errors.name" class="mt-1 text-[11px] text-danger">{{ errors.name }}</div>
            </div>
            <div>
                <FieldLabel for="group-handle" :hint="t('collections.group_field_handle_hint')">{{ t('collections.group_field_handle') }}</FieldLabel>
                <TextInput id="group-handle" :model-value="handle" mono :invalid="!!errors.handle" @update:model-value="onHandle" />
                <div v-if="errors.handle" class="mt-1 text-[11px] text-danger">{{ errors.handle }}</div>
            </div>
        </form>

        <template #footer>
            <template v-if="group">
                <Button
                    icon="trash"
                    class="!text-danger"
                    :disabled="!canDelete"
                    :title="canDelete ? undefined : t('collections.group_delete_protected_hint')"
                    @click="confirmDeleteOpen = true"
                >{{ t('collections.group_delete') }}</Button>
                <div class="flex-1" />
            </template>
            <Button variant="ghost" @click="emit('update:open', false)">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="saving || !name.trim()" @click="submit">
                {{ group ? t('collections.group_save') : t('collections.group_create') }}
            </Button>
        </template>
    </Dialog>

    <ConfirmDialog
        v-model:open="confirmDeleteOpen"
        :title="t('collections.confirm_delete_group_title')"
        :description="t('collections.confirm_delete_group')"
        tone="danger"
        :confirm-label="t('common.delete')"
        @confirm="destroyGroup"
    />
</template>
