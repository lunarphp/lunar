/** One item's drafted properties in a `media:{collection}` list. */
export interface MediaValue {
    id: number;
    name: string | null;
    alt: string | null;
    caption: string | null;
    focal: { x: number; y: number } | null;
}

/** An image's preview, from the record's media or the staff member's staged uploads. */
export interface ImagePreview {
    id: number;
    url: string;
    original_url: string;
}

/** A downloadable, non-image file's preview (e.g. a PDF in a documents group). */
export interface FilePreview {
    id: number;
    file_name: string;
    mime_type: string | null;
    size: number;
    extension: string;
    original_url: string;
}

export type MediaItem = MediaValue & ImagePreview;
export type FileItem = MediaValue & FilePreview;

interface MediaGroupBase {
    collection: string;
    title: string;
    description: string;
    accept: string;
    urls: { store: string };
}

export interface ImageMediaGroup extends MediaGroupBase {
    type: 'image';
    library: ImagePreview[];
}

export interface FileMediaGroup extends MediaGroupBase {
    type: 'file';
    library: FilePreview[];
}

export type MediaGroup = ImageMediaGroup | FileMediaGroup;
