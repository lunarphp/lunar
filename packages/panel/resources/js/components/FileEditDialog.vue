<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import TextInput from './TextInput.vue';
import Textarea from './Textarea.vue';
import type { FileItem } from './media';

const props = defineProps<{
    open: boolean;
    file: FileItem | null;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const draft = reactive({
    name: '',
    caption: '',
});

const errors = ref<Record<string, string>>({});
const saving = ref(false);

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen || !props.file) {
            return;
        }

        draft.name = props.file.name ?? '';
        draft.caption = props.file.caption ?? '';
        errors.value = {};
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const save = (): void => {
    if (!props.file) {
        return;
    }

    saving.value = true;

    router.put(
        props.file.update_url,
        {
            name: draft.name.trim() || null,
            caption: draft.caption.trim() || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => close(),
            onError: (bag) => {
                errors.value = bag;
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
};

const confirmDeleteOpen = ref(false);

const confirmDelete = (): void => {
    if (props.file) {
        router.delete(props.file.destroy_url, { preserveScroll: true });
        confirmDeleteOpen.value = false;
        close();
    }
};
</script>

<template>
    <Dialog
        :open="open"
        size="md"
        :title="t('media.edit_file')"
        :description="t('media.edit_file_description')"
        @update:open="$emit('update:open', $event)"
    >
        <div v-if="file" class="flex flex-col gap-4">
            <div>
                <FieldLabel for="file-name">{{ t('media.field_name') }}</FieldLabel>
                <TextInput id="file-name" v-model="draft.name" :placeholder="file.file_name" :invalid="!!errors.name" />
                <div v-if="errors.name" class="mt-1 text-[11px] text-danger">{{ errors.name }}</div>
            </div>
            <div>
                <FieldLabel for="file-caption">{{ t('media.field_caption') }}</FieldLabel>
                <Textarea
                    id="file-caption"
                    v-model="draft.caption"
                    :rows="2"
                    :placeholder="t('media.field_caption_placeholder')"
                    :invalid="!!errors.caption"
                />
                <div v-if="errors.caption" class="mt-1 text-[11px] text-danger">{{ errors.caption }}</div>
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" class="!text-danger mr-auto" icon="trash" @click="confirmDeleteOpen = true">
                {{ t('media.delete_file') }}
            </Button>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="saving" @click="save">{{ t('common.save_changes') }}</Button>
        </template>
    </Dialog>

    <ConfirmDialog
        v-model:open="confirmDeleteOpen"
        :title="t('media.confirm_delete_file_title')"
        :description="t('media.confirm_delete_file_body')"
        tone="danger"
        :confirm-label="t('common.delete')"
        @confirm="confirmDelete"
    />
</template>
