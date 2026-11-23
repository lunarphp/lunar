import { computed, reactive, ref, watch, type ComputedRef, type Ref } from 'vue';
import { http, ValidationError } from '../lib/http';
import { useFormSlice } from './useFormSlice';
import type { MediaValue } from '../components/media';

interface GroupProps<P extends { id: number }> {
    collection: string;
    library: P[];
    urls: { store: string };
}

export interface MediaGroupState<P extends { id: number }> {
    /** The drafted list joined with each item's preview, in order. */
    items: ComputedRef<Array<MediaValue & P>>;
    /** The first commit error for an item's row, by item id. */
    errors: ComputedRef<Record<number, string>>;
    uploading: Ref<boolean>;
    uploadError: Ref<string>;
    upload: (files: FileList | File[] | null) => Promise<void>;
    move: (from: number, to: number) => void;
    update: (id: number, properties: Partial<Omit<MediaValue, 'id'>>) => void;
    remove: (id: number) => void;
}

/**
 * One media collection bound to the page draft's `media:{collection}` list.
 * Reordering, editing and removing are draft edits that land with Save;
 * uploads post to the staging endpoint and append the staged item.
 */
export function useMediaGroup<P extends { id: number }>(group: () => GroupProps<P>): MediaGroupState<P> {
    const slice = useFormSlice<Record<string, MediaValue[]>>('media');
    const collection = (): string => group().collection;

    // Previews by id: the page's library plus uploads staged since it loaded.
    const previews = reactive(new Map<number, P>()) as Map<number, P>;

    watch(() => group().library, (library) => {
        library.forEach((preview) => previews.set(preview.id, preview));
    }, { immediate: true, deep: true });

    const list = (): MediaValue[] => slice.values[collection()] ?? [];
    const setList = (next: MediaValue[]): void => {
        slice.values[collection()] = next;
    };

    const items = computed(() =>
        list().flatMap((value) => {
            const preview = previews.get(value.id);

            return preview ? [{ ...preview, ...value }] : [];
        }));

    const errors = computed(() => {
        const prefix = `${collection()}.`;
        const byId: Record<number, string> = {};

        for (const [key, message] of Object.entries(slice.errors.value)) {
            if (!key.startsWith(prefix)) {
                continue;
            }

            const id = list()[Number(key.slice(prefix.length).split('.')[0])]?.id;

            if (id !== undefined && byId[id] === undefined) {
                byId[id] = message;
            }
        }

        return byId;
    });

    const uploading = ref(false);
    const uploadError = ref('');

    const upload = async (files: FileList | File[] | null): Promise<void> => {
        if (!files?.length) {
            return;
        }

        const form = new FormData();
        form.append('collection', collection());
        Array.from(files).forEach((file) => form.append('files[]', file));

        uploading.value = true;
        uploadError.value = '';

        try {
            const { items: staged } = await http.post<{ items: P[] }>(group().urls.store, form);

            staged.forEach((preview) => previews.set(preview.id, preview));
            setList([
                ...list(),
                ...staged.map((preview) => ({ id: preview.id, name: null, alt: null, caption: null, focal: null })),
            ]);
        } catch (error) {
            uploadError.value = error instanceof ValidationError
                ? Object.values(error.errors).flat()[0] ?? ''
                : String((error as Error)?.message ?? error);
        } finally {
            uploading.value = false;
        }
    };

    const move = (from: number, to: number): void => {
        const next = [...list()];
        next.splice(to, 0, ...next.splice(from, 1));
        setList(next);
    };

    const update = (id: number, properties: Partial<Omit<MediaValue, 'id'>>): void => {
        setList(list().map((value) => (value.id === id ? { ...value, ...properties } : value)));
    };

    const remove = (id: number): void => {
        setList(list().filter((value) => value.id !== id));
    };

    return { items, errors, uploading, uploadError, upload, move, update, remove };
}
