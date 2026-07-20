<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import CropPreview from './CropPreview.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import FocalPointEditor from './FocalPointEditor.vue';
import TextInput from './TextInput.vue';
import Textarea from './Textarea.vue';

export interface MediaItem {
    id: number;
    url: string;
    original_url: string;
    name: string | null;
    alt: string | null;
    caption: string | null;
    focal: { x: number; y: number } | null;
    primary: boolean;
    update_url: string;
    destroy_url: string;
}

const props = defineProps<{
    open: boolean;
    media: MediaItem | null;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const draft = reactive({
    name: '',
    alt: '',
    caption: '',
    focal: { x: 50, y: 50 },
});

const errors = ref<Record<string, string>>({});
const saving = ref(false);

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen || !props.media) {
            return;
        }

        draft.name = props.media.name ?? '';
        draft.alt = props.media.alt ?? '';
        draft.caption = props.media.caption ?? '';
        draft.focal = { x: props.media.focal?.x ?? 50, y: props.media.focal?.y ?? 50 };
        errors.value = {};
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const save = (): void => {
    if (!props.media) {
        return;
    }

    saving.value = true;

    router.put(
        props.media.update_url,
        {
            name: draft.name.trim() || null,
            alt: draft.alt.trim(),
            caption: draft.caption.trim() || null,
            focal: draft.focal,
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
    if (props.media) {
        router.delete(props.media.destroy_url, { preserveScroll: true });
        confirmDeleteOpen.value = false;
        close();
    }
};

const RATIOS = ['1:1', '4:5', '16:9', '9:16'];
</script>

<template>
    <Dialog
        :open="open"
        size="lg"
        :title="t('media.edit_image')"
        :description="t('media.edit_description')"
        @update:open="$emit('update:open', $event)"
    >
        <div v-if="media" class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-5">
            <div>
                <FieldLabel :hint="t('media.focal_hint')">{{ t('media.focal_point') }}</FieldLabel>
                <FocalPointEditor v-model="draft.focal" :src="media.original_url" :alt="draft.alt" />
            </div>

            <div class="flex flex-col gap-4 min-w-0">
                <div>
                    <FieldLabel for="media-name">{{ t('media.field_name') }}</FieldLabel>
                    <TextInput id="media-name" v-model="draft.name" :invalid="!!errors.name" />
                    <div v-if="errors.name" class="mt-1 text-[11px] text-danger">{{ errors.name }}</div>
                </div>
                <div>
                    <FieldLabel for="media-alt" required>{{ t('media.field_alt') }}</FieldLabel>
                    <TextInput
                        id="media-alt"
                        v-model="draft.alt"
                        :placeholder="t('media.field_alt_placeholder')"
                        :invalid="!!errors.alt"
                    />
                    <div v-if="errors.alt" class="mt-1 text-[11px] text-danger">{{ errors.alt }}</div>
                </div>
                <div>
                    <FieldLabel for="media-caption">{{ t('media.field_caption') }}</FieldLabel>
                    <Textarea
                        id="media-caption"
                        v-model="draft.caption"
                        :rows="2"
                        :placeholder="t('media.field_caption_placeholder')"
                        :invalid="!!errors.caption"
                    />
                    <div v-if="errors.caption" class="mt-1 text-[11px] text-danger">{{ errors.caption }}</div>
                </div>
                <div>
                    <FieldLabel :hint="t('media.crop_previews_hint')">{{ t('media.crop_previews') }}</FieldLabel>
                    <div class="grid grid-cols-4 gap-2">
                        <CropPreview
                            v-for="ratio in RATIOS"
                            :key="ratio"
                            :src="media.original_url"
                            :alt="draft.alt"
                            :focal="draft.focal"
                            :ratio="ratio"
                            :label="ratio"
                        />
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" class="!text-danger mr-auto" icon="trash" @click="confirmDeleteOpen = true">
                {{ t('media.delete_image') }}
            </Button>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!draft.alt.trim() || saving" @click="save">{{ t('common.save_changes') }}</Button>
        </template>
    </Dialog>

    <ConfirmDialog
        v-model:open="confirmDeleteOpen"
        :title="t('media.confirm_delete_title')"
        :description="t('media.confirm_delete_body')"
        tone="danger"
        :confirm-label="t('common.delete')"
        @confirm="confirmDelete"
    />
</template>
