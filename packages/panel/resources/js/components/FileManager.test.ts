import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { createI18n } from 'vue-i18n';
import FileManager from './FileManager.vue';

const pageProps: { panel: { media_max_kb?: number } } = { panel: { media_max_kb: 8192 } };

vi.mock('@inertiajs/vue3', () => ({
    router: {
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
    usePage: () => ({ props: pageProps }),
}));

import { router } from '@inertiajs/vue3';

const i18n = createI18n({
    legacy: false,
    locale: 'en',
    messages: {
        en: {
            media: {
                files_hint: 'Max {size} MB each.',
                files_description: '{count} files · drag to reorder',
            },
        },
    },
});

const file = (id: number, name: string) => ({
    id,
    file_name: name,
    mime_type: 'application/pdf',
    size: 2 * 1024 * 1024,
    extension: 'pdf',
    original_url: `/media/${id}/${name}`,
    name: null,
    caption: null,
    update_url: `/panel/products/1/media/${id}`,
    destroy_url: `/panel/products/1/media/${id}`,
});

const mountManager = (items = [file(1, 'a.pdf'), file(2, 'b.pdf')]) =>
    mount(FileManager, {
        props: {
            group: {
                collection: 'downloads',
                title: 'Downloads',
                description: '',
                type: 'file' as const,
                accept: 'application/pdf',
                items,
                urls: { store: '/panel/products/1/media', reorder: '/panel/products/1/media/reorder' },
            },
        },
        global: { plugins: [i18n] },
        attachTo: document.body,
    });

const ROW_HEIGHT = 30;

const rect = (top: number, height: number): DOMRect =>
    ({ top, height, bottom: top + height, left: 0, right: 0, width: 0, x: 0, y: top, toJSON: () => ({}) }) as DOMRect;

const stubRowGeometry = (container: Element): void => {
    container.getBoundingClientRect = () => rect(0, 0);
    Array.from(container.children).forEach((child, index) => {
        child.getBoundingClientRect = () => rect(index * ROW_HEIGHT, ROW_HEIGHT);
    });
};

const slotY = (index: number): number => index * ROW_HEIGHT + ROW_HEIGHT / 2;

describe('FileManager', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        pageProps.panel = { media_max_kb: 8192 };
    });

    it('shows the drop-zone empty state without items', () => {
        const wrapper = mountManager([]);

        expect(wrapper.text()).toContain('media.files_empty_title');
    });

    it('renders a row per file with name, type and a download link', () => {
        const wrapper = mountManager();

        expect(wrapper.text()).toContain('a.pdf');
        expect(wrapper.text()).toContain('PDF · 2.0 MB');

        const download = wrapper.find('a[href="/media/1/a.pdf"]');
        expect(download.exists()).toBe(true);
        expect(download.attributes('target')).toBe('_blank');
    });

    it('sets the accept attribute from the group', () => {
        const wrapper = mountManager();

        expect(wrapper.find('input[type="file"]').attributes('accept')).toBe('application/pdf');
    });

    it('persists a drag reorder with the collection', async () => {
        const wrapper = mountManager();

        const rows = wrapper.findAll('[draggable="true"]');
        const list = wrapper.find('.flex.flex-col');

        stubRowGeometry(list.element);

        await rows[0].trigger('dragstart');
        await list.trigger('dragover', { clientY: slotY(1) });
        await rows[0].trigger('dragend');

        expect(router.post).toHaveBeenCalledWith(
            '/panel/products/1/media/reorder',
            { collection: 'downloads', ids: [2, 1] },
            expect.anything(),
        );
    });

    it('does not persist when the order is unchanged', async () => {
        const wrapper = mountManager();

        const rows = wrapper.findAll('[draggable="true"]');
        const list = wrapper.find('.flex.flex-col');

        stubRowGeometry(list.element);

        await rows[0].trigger('dragstart');
        await rows[0].trigger('dragend');

        expect(router.post).not.toHaveBeenCalled();
    });

    it('renders the max upload size from the shared panel limit', () => {
        const wrapper = mountManager();

        expect(wrapper.text()).toContain('Max 8 MB each.');
    });
});
