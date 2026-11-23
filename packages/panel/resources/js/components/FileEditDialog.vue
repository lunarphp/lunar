<script setup lang="ts">
import { reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import TextInput from './TextInput.vue';
import Textarea from './Textarea.vue';
import type { FileItem } from './media';

const props = defineProps<{
    open: boolean;
    file: FileItem | null;
}>();

// Applying and removing edit the page draft; Save on the page commits them.
const emit = defineEmits<{
    'update:open': [value: boolean];
    save: [properties: Pick<FileItem, 'name' | 'caption'>];
    remove: [];
}>();

const { t } = useI18n();

const draft = reactive({
    name: '',
    caption: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen || !props.file) {
            return;
        }

        draft.name = props.file.name ?? '';
        draft.caption = props.file.caption ?? '';
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const save = (): void => {
    emit('save', { name: draft.name.trim() || null, caption: draft.caption.trim() || null });
    close();
};

const remove = (): void => {
    emit('remove');
    close();
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
                <TextInput id="file-name" v-model="draft.name" :placeholder="file.file_name" />
            </div>
            <div>
                <FieldLabel for="file-caption">{{ t('media.field_caption') }}</FieldLabel>
                <Textarea
                    id="file-caption"
                    v-model="draft.caption"
                    :rows="2"
                    :placeholder="t('media.field_caption_placeholder')"
                />
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" class="!text-danger mr-auto" icon="trash" @click="remove">
                {{ t('media.delete_file') }}
            </Button>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" @click="save">{{ t('common.apply') }}</Button>
        </template>
    </Dialog>

</template>
