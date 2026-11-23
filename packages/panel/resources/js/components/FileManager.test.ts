import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import { mount } from '@vue/test-utils';
import { createI18n } from 'vue-i18n';
import { useEditDraft, type EditDraftForm } from '../composables/useEditDraft';
import FileManager from './FileManager.vue';

const pageProps: { panel: { media_max_kb?: number } } = { panel: { media_max_kb: 8192 } };

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: vi.fn(), on: vi.fn(() => () => {}) },
    usePage: () => ({ props: pageProps }),
}));

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: { patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

const i18n = createI18n({
    legacy: false,
    locale: 'en',
    missingWarn: false,
    fallbackWarn: false,
    messages: {
        en: {
            media: {
                files_hint: 'Max {size} MB each.',
                files_description: '{count} files · drag to reorder',
            },
        },
    },
});

const files: Record<number, string> = { 1: 'a.pdf', 2: 'b.pdf' };

const preview = (id: number) => ({
    id,
    file_name: files[id],
    mime_type: 'application/pdf',
    size: 2 * 1024 * 1024,
    extension: 'pdf',
    original_url: `/media/${id}/${files[id]}`,
});

function mountManager(ids = [1, 2]) {
    let form: EditDraftForm<Record<string, unknown>> | undefined;

    const Page = defineComponent({
        setup() {
            form = useEditDraft({
                initial: { 'media:downloads': ids.map((id) => ({ id, name: null, alt: null, caption: null, focal: null })) },
                draft: null,
                urls: { draft: '/p/1/draft', commit: '/p/1/draft/commit' },
            });

            return () => h(FileManager, {
                group: {
                    collection: 'downloads',
                    title: 'Downloads',
                    description: '',
                    type: 'file' as const,
                    accept: 'application/pdf',
                    library: ids.map(preview),
                    urls: { store: '/panel/products/1/media' },
                },
            });
        },
    });

    return { wrapper: mount(Page, { global: { plugins: [i18n] }, attachTo: document.body }), form: form! };
}

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
        document.body.innerHTML = '';
    });

    it('shows the drop-zone empty state without items', () => {
        expect(mountManager([]).wrapper.text()).toContain('media.files_empty_title');
    });

    it('renders a row per file with name, type and a download link', () => {
        const { wrapper } = mountManager();

        expect(wrapper.text()).toContain('a.pdf');
        expect(wrapper.text()).toContain('PDF · 2.0 MB');

        const download = wrapper.find('a[href="/media/1/a.pdf"]');
        expect(download.exists()).toBe(true);
        expect(download.attributes('target')).toBe('_blank');
    });

    it('sets the accept attribute from the group', () => {
        expect(mountManager().wrapper.find('input[type="file"]').attributes('accept')).toBe('application/pdf');
    });

    it('drafts a drag reorder', async () => {
        const { wrapper, form } = mountManager();

        const rows = wrapper.findAll('[draggable="true"]');
        const list = wrapper.find('.flex.flex-col');

        stubRowGeometry(list.element);

        await rows[0].trigger('dragstart');
        await list.trigger('dragover', { clientY: slotY(1) });
        await rows[0].trigger('dragend');

        expect((form.values['media:downloads'] as { id: number }[]).map((row) => row.id)).toEqual([2, 1]);
    });

    it('stays clean when the order is unchanged', async () => {
        const { wrapper, form } = mountManager();

        const rows = wrapper.findAll('[draggable="true"]');
        stubRowGeometry(wrapper.find('.flex.flex-col').element);

        await rows[0].trigger('dragstart');
        await rows[0].trigger('dragend');

        expect(form.dirtyKeys.value).toEqual([]);
    });

    it('renders the max upload size from the shared panel limit', () => {
        expect(mountManager().wrapper.text()).toContain('Max 8 MB each.');
    });
});
