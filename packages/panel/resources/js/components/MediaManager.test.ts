import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { createI18n } from 'vue-i18n';
import { useEditDraft, type EditDraftForm } from '../composables/useEditDraft';
import MediaManager from './MediaManager.vue';

// Panel props feed the uploader hint; each test can set the shared limit.
const pageProps: { panel: { media_max_kb?: number } } = { panel: { media_max_kb: 8192 } };

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: vi.fn(), on: vi.fn(() => () => {}) },
    usePage: () => ({ props: pageProps }),
}));

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: { patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

import { http } from '../lib/http';

// Real interpolation so the KB to MB conversion is observable in the hint.
const i18n = createI18n({
    legacy: false,
    locale: 'en',
    missingWarn: false,
    fallbackWarn: false,
    messages: { en: { media: { file_hint: 'JPG, PNG or WebP. Max {size} MB each.' } } },
});

const preview = (id: number) => ({ id, url: `media-${id}.jpg`, original_url: `media-${id}-original.jpg` });
const value = (id: number) => ({ id, name: null, alt: `Image ${id}`, caption: null, focal: { x: 25, y: 75 } });

function mountManager(ids = [1, 2]) {
    let form: EditDraftForm<Record<string, unknown>> | undefined;

    const Page = defineComponent({
        setup() {
            form = useEditDraft({
                initial: { 'media:images': ids.map(value) },
                draft: null,
                urls: { draft: '/b/1/draft', commit: '/b/1/draft/commit' },
            });

            return () => h(MediaManager, {
                group: {
                    collection: 'images',
                    title: 'Images',
                    description: '',
                    type: 'image' as const,
                    accept: 'image/*',
                    library: ids.map(preview),
                    urls: { store: '/panel/brands/1/media' },
                },
            });
        },
    });

    return { wrapper: mount(Page, { global: { plugins: [i18n] }, attachTo: document.body }), form: form! };
}

// jsdom reports zero-sized rects, so the grid-sort composable (which targets
// the cell nearest the pointer) has no geometry. Lay the cells out in a row so
// a dragover with a clientX/clientY resolves to a known tile.
const CELL = 50;

const rect = (left: number): DOMRect =>
    ({ left, top: 0, width: CELL, height: CELL, right: left + CELL, bottom: CELL, x: left, y: 0, toJSON: () => ({}) }) as DOMRect;

const stubCellGeometry = (grid: Element): void => {
    Array.from(grid.children).forEach((child, index) => {
        child.getBoundingClientRect = () => rect(index * CELL);
    });
};

// clientX at the centre of the target cell.
const cellX = (index: number): number => index * CELL + CELL / 2;

describe('MediaManager', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        pageProps.panel = { media_max_kb: 8192 };
        document.body.innerHTML = '';
    });

    it('shows the drop-zone empty state without items', () => {
        const { wrapper } = mountManager([]);

        expect(wrapper.text()).toContain('media.empty_title');
        expect(wrapper.findAll('img')).toHaveLength(0);
    });

    it('renders tiles with the focal point applied as object-position', () => {
        const { wrapper } = mountManager();

        expect(wrapper.find('img').attributes('style')).toContain('object-position: 25% 75%');
        expect(wrapper.text()).toContain('media.hero');
    });

    it('drafts a drag reorder without a request', async () => {
        const { wrapper, form } = mountManager();

        const tiles = wrapper.findAll('[draggable="true"]');
        const grid = wrapper.find('.grid');

        stubCellGeometry(grid.element);

        await tiles[1].trigger('dragstart');
        await grid.trigger('dragover', { clientX: cellX(0), clientY: CELL / 2 });
        await tiles[1].trigger('dragend');

        expect((form.values['media:images'] as { id: number }[]).map((row) => row.id)).toEqual([2, 1]);
        expect(form.dirtyKeys.value).toEqual(['media:images']);
        expect(http.post).not.toHaveBeenCalled();
    });

    it('stays clean when the order is unchanged', async () => {
        const { wrapper, form } = mountManager();

        const tiles = wrapper.findAll('[draggable="true"]');

        await tiles[0].trigger('dragstart');
        await tiles[0].trigger('dragend');

        expect(form.dirtyKeys.value).toEqual([]);
    });

    it('stages an upload and appends it to the draft list', async () => {
        vi.mocked(http.post).mockResolvedValueOnce({ items: [preview(9)] });
        const { wrapper, form } = mountManager();

        const input = wrapper.find('input[type="file"]');
        Object.defineProperty(input.element, 'files', { value: [new File(['x'], 'new.jpg', { type: 'image/jpeg' })] });
        await input.trigger('change');
        await vi.waitFor(() => expect(wrapper.findAll('img')).toHaveLength(3));

        const [url, body] = vi.mocked(http.post).mock.calls[0];
        expect(url).toBe('/panel/brands/1/media');
        expect((body as FormData).get('collection')).toBe('images');
        expect((form.values['media:images'] as { id: number }[]).map((row) => row.id)).toEqual([1, 2, 9]);
    });

    it('edits and removes an image through the dialog as draft edits', async () => {
        const { wrapper, form } = mountManager();

        await wrapper.findAll('button[aria-label="media.edit_image"]')[1].trigger('click');
        const alt = document.querySelector<HTMLInputElement>('#media-alt')!;
        alt.value = 'A jumper on a chair';
        alt.dispatchEvent(new Event('input'));
        await nextTick();
        Array.from(document.querySelectorAll('button')).find((button) => button.textContent?.includes('common.apply'))!.click();
        await nextTick();

        expect((form.values['media:images'] as { alt: string }[])[1].alt).toBe('A jumper on a chair');

        await wrapper.findAll('button[aria-label="media.edit_image"]')[0].trigger('click');
        Array.from(document.querySelectorAll('button')).find((button) => button.textContent?.includes('media.delete_image'))!.click();
        await nextTick();

        expect((form.values['media:images'] as { id: number }[]).map((row) => row.id)).toEqual([2]);
    });

    it('shows a commit error on the tile that caused it', async () => {
        const { wrapper, form } = mountManager();

        form.errors.value = { 'media:images.1.alt': 'The alt text is too long.' };
        await nextTick();

        const tiles = wrapper.findAll('[draggable="true"]');
        expect(tiles[1].text()).toContain('The alt text is too long.');
        expect(tiles[0].text()).not.toContain('The alt text is too long.');
    });

    it('renders the max upload size from the shared panel limit', () => {
        expect(mountManager().wrapper.text()).toContain('Max 8 MB each.');
    });

    it('reflects a changed limit and formats a fractional megabyte', () => {
        pageProps.panel = { media_max_kb: 2560 };

        expect(mountManager().wrapper.text()).toContain('Max 2.5 MB each.');
    });

    it('falls back to 8 MB when the limit is absent', () => {
        pageProps.panel = {};

        expect(mountManager().wrapper.text()).toContain('Max 8 MB each.');
    });
});
