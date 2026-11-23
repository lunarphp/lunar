<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Icon from './Icon.vue';
import MediaEditDialog, { type MediaItem } from './MediaEditDialog.vue';
import Section from './Section.vue';
import type { ImageMediaGroup } from './media';
import { useGridSort } from '../composables/useGridSort';

const props = defineProps<{
    group: ImageMediaGroup;
}>();

const { t } = useI18n();

// Upload ceiling shared once via the panel prop; the same config value backs
// the server-side validation rule, so hint and limit can't drift apart.
const maxUploadKb = computed(
    () => ((usePage().props.panel as { media_max_kb?: number } | undefined)?.media_max_kb ?? 8192),
);
const maxUploadMb = computed(() => {
    const mb = maxUploadKb.value / 1024;
    return Number.isInteger(mb) ? String(mb) : mb.toFixed(1);
});

// Local order mirrors the prop; drag reordering mutates it optimistically and
// persists on drop, so tiles don't snap back while the request runs.
const ordered = ref<MediaItem[]>([...props.group.items]);

watch(
    () => props.group.items,
    (items) => {
        ordered.value = [...items];
    },
    { deep: true },
);

const fileInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const uploadError = ref('');

const pickFiles = (): void => fileInput.value?.click();

const upload = (files: FileList | null): void => {
    if (!files?.length) {
        return;
    }

    uploading.value = true;
    uploadError.value = '';

    router.post(
        props.group.urls.store,
        { collection: props.group.collection, files: Array.from(files) },
        {
            preserveScroll: true,
            forceFormData: true,
            onError: (errors) => {
                uploadError.value = Object.values(errors)[0] ?? '';
            },
            onFinish: () => {
                uploading.value = false;

                if (fileInput.value) {
                    fileInput.value.value = '';
                }
            },
        },
    );
};

const dropActive = ref(false);

const onDrop = (event: DragEvent): void => {
    dropActive.value = false;
    upload(event.dataTransfer?.files ?? null);
};

// Animated drag-to-reorder over the 2-D tile grid (first tile is the hero).
// The local `ordered` snapshot holds the new order while the request runs; the
// watch on `props.group.items` re-syncs once the reloaded props arrive.
const tileSort = useGridSort({
    onCommit: (_listId, from, to) => {
        const current = [...ordered.value];
        current.splice(to, 0, ...current.splice(from, 1));
        ordered.value = current;

        router.post(props.group.urls.reorder, {
            collection: props.group.collection,
            ids: current.map((item) => item.id),
        }, {
            preserveScroll: true,
        });
    },
});

const editing = ref<MediaItem | null>(null);
const dialogOpen = computed({
    get: () => editing.value !== null,
    set: (value: boolean) => {
        if (!value) {
            editing.value = null;
        }
    },
});

const focalStyle = (item: MediaItem): Record<string, string> => ({
    objectPosition: `${item.focal?.x ?? 50}% ${item.focal?.y ?? 50}%`,
});
</script>

<template>
    <Section :title="group.title">
        <template #desc>
            {{ ordered.length ? t('media.description', { count: ordered.length }) : t('media.description_empty') }}
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

        <!-- Empty state doubles as the drop zone. -->
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
            <div class="w-9 h-9 mx-auto mb-2 bg-surface border border-line rounded-lg grid place-items-center text-ink-500"><Icon name="image" /></div>
            <div class="text-[13px] font-medium mb-0.5">{{ t('media.empty_title') }}</div>
            <div class="text-xs text-ink-500 max-w-[280px] mx-auto mb-2.5">{{ t('media.empty_body') }}</div>
            <Button :disabled="uploading" icon="upload" @click="pickFiles">{{ t('media.upload_images') }}</Button>
        </div>

        <template v-else>
            <div
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5"
                @dragover.prevent="tileSort.over($event, 'media', ordered.length)"
            >
                <div
                    v-for="(item, index) in ordered"
                    :key="item.id"
                    :class="[
                        'group relative aspect-square rounded-md overflow-hidden border bg-surface-2 cursor-grab transition-[border-color,box-shadow,opacity] duration-100 hover:border-ink-300 hover:shadow-md',
                        index === 0 ? 'col-span-2 row-span-2' : '',
                        tileSort.isDragging('media', index) ? 'opacity-60 border-sage z-10' : 'border-line',
                    ]"
                    :style="tileSort.style('media', index)"
                    draggable="true"
                    @dragstart="tileSort.start($event, 'media', index)"
                    @dragend="tileSort.end()"
                    @drop.prevent
                >
                    <img
                        :src="item.url"
                        :alt="item.alt ?? ''"
                        class="w-full h-full object-cover block pointer-events-none"
                        :style="focalStyle(item)"
                        loading="lazy"
                    />
                    <div
                        :class="[
                            'absolute top-1.5 left-1.5 backdrop-blur-[4px] rounded-full text-[10px] font-medium px-1.5 py-0.5',
                            index === 0 ? 'bg-ink-900/85 text-paper' : 'bg-paper/95 border border-line text-ink-900',
                        ]"
                    >{{ index === 0 ? t('media.hero') : index + 1 }}</div>
                    <div class="absolute top-1.5 right-1.5 flex gap-1 opacity-0 transition-opacity duration-100 group-hover:opacity-100 focus-within:opacity-100">
                        <button
                            type="button"
                            class="w-[22px] h-[22px] grid place-items-center bg-paper/95 border border-line rounded-[5px] text-ink-500 hover:text-ink-900 hover:border-ink-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35 cursor-pointer"
                            :aria-label="t('media.edit_image')"
                            @click.stop="editing = item"
                        >
                            <Icon name="edit" cls="sm" />
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    :class="[
                        'aspect-square rounded-md overflow-hidden border border-dashed grid place-items-center cursor-pointer text-ink-500 text-center transition-colors duration-100 hover:bg-surface-2 hover:border-ink-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35',
                        dropActive ? 'border-sage bg-sage-soft/40' : 'border-line bg-surface',
                    ]"
                    :disabled="uploading"
                    @click="pickFiles"
                    @dragover.prevent="dropActive = true"
                    @dragleave="dropActive = false"
                    @drop.prevent="onDrop"
                >
                    <span class="flex flex-col items-center">
                        <Icon name="plus" />
                        <span class="text-[11px] mt-1">{{ t('media.add_image') }}</span>
                    </span>
                </button>
            </div>
            <div class="text-[11.5px] text-ink-500 mt-2.5">{{ t('media.file_hint', { size: maxUploadMb }) }}</div>
        </template>

        <div v-if="uploadError" class="mt-2 text-[11px] text-danger">{{ uploadError }}</div>

        <MediaEditDialog v-model:open="dialogOpen" :media="editing" />
    </Section>
</template>
