import { afterEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import { mount } from '@vue/test-utils';
import { useEditDraft, type EditDraftForm } from '../composables/useEditDraft';
import VariantMediaPicker, { type VariantMediaItem } from './VariantMediaPicker.vue';

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: { patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: vi.fn(), on: vi.fn(() => () => {}) },
    usePage: () => ({ props: {} }),
}));

const pool: VariantMediaItem[] = [
    { id: 1, url: '/a.jpg', name: 'A', alt: null },
    { id: 2, url: '/b.jpg', name: 'B', alt: null },
    { id: 3, url: '/c.jpg', name: 'C', alt: null },
];

function mountPicker(items: VariantMediaItem[] = pool, ids: number[] = [1, 2]) {
    let form: EditDraftForm<Record<string, unknown>> | undefined;

    const Page = defineComponent({
        setup() {
            form = useEditDraft({ initial: { 'variant_media:ids': ids }, draft: null, urls: { draft: '/v/1/draft', commit: '/v/1/draft/commit' } });

            return () => h(VariantMediaPicker, { pool: items });
        },
    });

    return { wrapper: mount(Page, { attachTo: document.body }), form: form! };
}

describe('VariantMediaPicker', () => {
    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('renders the empty state without a pool', () => {
        expect(mountPicker([], []).wrapper.text()).toContain('products.variant_media_empty');
    });

    it('marks the first selected tile as primary and orders the rest', () => {
        const { wrapper, form } = mountPicker();

        expect(wrapper.text()).toContain('products.variant_media_primary');
        expect(wrapper.text()).toContain('2');
        expect(form.dirtyKeys.value).toEqual([]);
    });

    it('drafts adding and removing tiles in order', async () => {
        const { wrapper, form } = mountPicker();
        const toggles = wrapper.findAll('button[aria-pressed]');

        await toggles[2].trigger('click');
        expect(form.values['variant_media:ids']).toEqual([1, 2, 3]);

        await toggles[0].trigger('click');
        expect(form.values['variant_media:ids']).toEqual([2, 3]);
        expect(form.dirtyKeys.value).toEqual(['variant_media:ids']);
    });

    it('moves a selected image earlier', async () => {
        const { wrapper, form } = mountPicker();

        await wrapper.findAll('button[aria-label="products.variant_media_earlier"]')[1].trigger('click');

        expect(form.values['variant_media:ids']).toEqual([2, 1]);
    });

    it('shows the commit error under the picker', async () => {
        const { wrapper, form } = mountPicker();

        form.errors.value = { 'variant_media:ids.0': 'The image is not in the product pool.' };
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('The image is not in the product pool.');
    });
});
