<script setup lang="ts">
import { reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import CropPreview from './CropPreview.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import FocalPointEditor from './FocalPointEditor.vue';
import TextInput from './TextInput.vue';
import Textarea from './Textarea.vue';
import type { MediaItem } from './media';

export type { MediaItem };

const props = defineProps<{
    open: boolean;
    media: MediaItem | null;
}>();

// Applying and removing edit the page draft; Save on the page commits them.
const emit = defineEmits<{
    'update:open': [value: boolean];
    save: [properties: Pick<MediaItem, 'name' | 'alt' | 'caption' | 'focal'>];
    remove: [];
}>();

const { t } = useI18n();

const draft = reactive({
    name: '',
    alt: '',
    caption: '',
    focal: { x: 50, y: 50 },
});

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
    },
    { immediate: true },
);

const close = (): void => emit('update:open', false);

const save = (): void => {
    emit('save', {
        name: draft.name.trim() || null,
        alt: draft.alt.trim() || null,
        caption: draft.caption.trim() || null,
        focal: { ...draft.focal },
    });
    close();
};

const remove = (): void => {
    emit('remove');
    close();
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
                    <TextInput id="media-name" v-model="draft.name" />
                </div>
                <div>
                    <FieldLabel for="media-alt" required>{{ t('media.field_alt') }}</FieldLabel>
                    <TextInput
                        id="media-alt"
                        v-model="draft.alt"
                        :placeholder="t('media.field_alt_placeholder')"
                    />
                </div>
                <div>
                    <FieldLabel for="media-caption">{{ t('media.field_caption') }}</FieldLabel>
                    <Textarea
                        id="media-caption"
                        v-model="draft.caption"
                        :rows="2"
                        :placeholder="t('media.field_caption_placeholder')"
                    />
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
            <Button variant="ghost" class="!text-danger mr-auto" icon="trash" @click="remove">
                {{ t('media.delete_image') }}
            </Button>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!draft.alt.trim()" @click="save">{{ t('common.apply') }}</Button>
        </template>
    </Dialog>

</template>
