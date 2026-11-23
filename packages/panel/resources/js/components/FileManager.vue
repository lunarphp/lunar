<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Icon from './Icon.vue';
import FileEditDialog from './FileEditDialog.vue';
import Section from './Section.vue';
import type { FileItem, FileMediaGroup, FilePreview } from './media';
import { useDragSort } from '../composables/useDragSort';
import { useMediaGroup } from '../composables/useMediaGroup';

const props = defineProps<{
    group: FileMediaGroup;
}>();

const { t } = useI18n();

// Shared upload ceiling, from the same panel prop that backs the server rule.
const maxUploadKb = computed(
    () => ((usePage().props.panel as { media_max_kb?: number } | undefined)?.media_max_kb ?? 8192),
);

// The files are the draft's media:{collection} list, like images.
const media = useMediaGroup<FilePreview>(() => props.group);
const ordered = media.items;
const uploading = media.uploading;
const uploadError = media.uploadError;

const fileInput = ref<HTMLInputElement | null>(null);

const pickFiles = (): void => fileInput.value?.click();

const upload = async (files: FileList | null): Promise<void> => {
    await media.upload(files);

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const dropActive = ref(false);

const onDrop = (event: DragEvent): void => {
    dropActive.value = false;
    void upload(event.dataTransfer?.files ?? null);
};

const rowSort = useDragSort({
    onCommit: (_listId, from, to) => media.move(from, to),
});

const editingId = ref<number | null>(null);
const editing = computed<FileItem | null>(() => ordered.value.find((item) => item.id === editingId.value) ?? null);
const dialogOpen = computed({
    get: () => editing.value !== null,
    set: (value: boolean) => {
        if (!value) {
            editingId.value = null;
        }
    },
});

const onSave = (properties: Pick<FileItem, 'name' | 'caption'>): void => {
    if (editingId.value !== null) {
        media.update(editingId.value, properties);
    }
};

const onRemove = (): void => {
    if (editingId.value !== null) {
        media.remove(editingId.value);
    }
};

const formatSize = (bytes: number): string => {
    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    if (bytes >= 1024) {
        return `${Math.round(bytes / 1024)} KB`;
    }

    return `${bytes} B`;
};

const displayName = (item: FileItem): string => item.name?.trim() || item.file_name;
</script>

<template>
    <Section :title="group.title">
        <template #desc>
            {{ group.description || (ordered.length ? t('media.files_description', { count: ordered.length }) : t('media.files_description_empty')) }}
        </template>
        <template #actions>
            <Button icon="upload" size="sm" :disabled="uploading" @click="pickFiles">{{ t('media.upload') }}</Button>
        </template>

        <input
            ref="fileInput"
            type="file"
            :accept="group.accept"
            multiple
            class="hidden"
            @change="upload(($event.target as HTMLInputElement).files)"
        />

        <div
            v-if="!ordered.length"
            :class="[
                'text-center px-5 py-7 border border-dashed rounded-md transition-colors duration-100',
                dropActive ? 'border-sage bg-sage-soft/40' : 'border-line-strong bg-surface-2',
            ]"
            @dragover.prevent="dropActive = true"
            @dragleave="dropActive = false"
            @drop.prevent="onDrop"
        >
            <div class="w-9 h-9 mx-auto mb-2 bg-surface border border-line rounded-lg grid place-items-center text-ink-500"><Icon name="fileText" /></div>
            <div class="text-[13px] font-medium mb-0.5">{{ t('media.files_empty_title') }}</div>
            <div class="text-xs text-ink-500 max-w-[280px] mx-auto mb-2.5">{{ t('media.files_empty_body') }}</div>
            <Button :disabled="uploading" icon="upload" @click="pickFiles">{{ t('media.upload_files') }}</Button>
        </div>

        <template v-else>
            <div
                class="flex flex-col gap-1.5"
                @dragover.prevent="rowSort.over($event, 'files')"
                @drop.prevent
            >
                <div
                    v-for="(item, index) in ordered"
                    :key="item.id"
                    :class="[
                        'group flex items-center gap-2.5 px-2.5 py-2 rounded-md border bg-surface transition-[border-color,box-shadow,opacity] duration-100 hover:border-ink-300',
                        rowSort.isDragging('files', index) ? 'opacity-60 border-sage z-10' : 'border-line',
                    ]"
                    :style="rowSort.style('files', index)"
                    draggable="true"
                    @dragstart="rowSort.start($event, 'files', index)"
                    @dragend="rowSort.end()"
                >
                    <span class="text-ink-400 cursor-grab shrink-0"><Icon name="grip" cls="sm" /></span>
                    <span class="w-8 h-8 shrink-0 grid place-items-center bg-surface-2 border border-line rounded text-ink-500"><Icon name="fileText" cls="sm" /></span>
                    <div class="min-w-0 flex-1">
                        <div class="text-[13px] font-medium truncate">{{ displayName(item) }}</div>
                        <div class="text-[11px] text-ink-500 truncate">{{ item.extension.toUpperCase() }} · {{ formatSize(item.size) }}</div>
                        <div v-if="media.errors.value[item.id]" class="text-[11px] text-danger" role="alert">{{ media.errors.value[item.id] }}</div>
                    </div>
                    <a
                        :href="item.original_url"
                        target="_blank"
                        rel="noopener"
                        class="w-[26px] h-[26px] grid place-items-center bg-paper border border-line rounded-[5px] text-ink-500 hover:text-ink-900 hover:border-ink-300 shrink-0"
                        :aria-label="t('media.download')"
                        @click.stop
                    >
                        <Icon name="download" cls="sm" />
                    </a>
                    <button
                        type="button"
                        class="w-[26px] h-[26px] grid place-items-center bg-paper border border-line rounded-[5px] text-ink-500 hover:text-ink-900 hover:border-ink-300 shrink-0 cursor-pointer"
                        :aria-label="t('media.edit_file')"
                        @click.stop="editingId = item.id"
                    >
                        <Icon name="edit" cls="sm" />
                    </button>
                </div>
            </div>
            <div class="text-[11.5px] text-ink-500 mt-2.5">{{ t('media.files_hint', { size: Math.round(maxUploadKb / 1024) }) }}</div>
        </template>

        <div v-if="uploadError" class="mt-2 text-[11px] text-danger">{{ uploadError }}</div>

        <FileEditDialog v-model:open="dialogOpen" :file="editing" @save="onSave" @remove="onRemove" />
    </Section>
</template>
