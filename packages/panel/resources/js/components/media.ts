import type { MediaItem } from './MediaEditDialog.vue';

export type { MediaItem };

/** A downloadable, non-image media item (e.g. a PDF in a documents group). */
export interface FileItem {
    id: number;
    file_name: string;
    mime_type: string | null;
    size: number;
    extension: string;
    original_url: string;
    name: string | null;
    caption: string | null;
    update_url: string;
    destroy_url: string;
}

interface MediaGroupBase {
    collection: string;
    title: string;
    description: string;
    accept: string;
    urls: { store: string; reorder: string };
}

export interface ImageMediaGroup extends MediaGroupBase {
    type: 'image';
    items: MediaItem[];
}

export interface FileMediaGroup extends MediaGroupBase {
    type: 'file';
    items: FileItem[];
}

export type MediaGroup = ImageMediaGroup | FileMediaGroup;
