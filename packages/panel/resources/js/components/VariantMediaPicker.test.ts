import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { router } from '@inertiajs/vue3';
import VariantMediaPicker, { type VariantMediaItem } from './VariantMediaPicker.vue';

const pool: VariantMediaItem[] = [
    { id: 1, url: '/a.jpg', name: 'A', alt: null, selected: true, position: 1 },
    { id: 2, url: '/b.jpg', name: 'B', alt: null, selected: true, position: 2 },
    { id: 3, url: '/c.jpg', name: 'C', alt: null, selected: false, position: null },
];

const mountPicker = (items: VariantMediaItem[] = pool) =>
    mount(VariantMediaPicker, {
        props: { pool: items, syncUrl: '/variant/media' },
        global: {
            stubs: { Tooltip: { template: '<span><slot /></span>' } },
        },
    });

describe('VariantMediaPicker', () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('renders the empty state without a pool', () => {
        const wrapper = mountPicker([]);

        expect(wrapper.text()).toContain('products.variant_media_empty');
    });

    it('marks the first selected tile as primary and orders the rest', () => {
        const wrapper = mountPicker();

        // Primary badge label plus the second-position index badge.
        expect(wrapper.text()).toContain('products.variant_media_primary');
        expect(wrapper.text()).toContain('2');
    });

    it('is not dirty until the selection changes', () => {
        const wrapper = mountPicker();

        expect(wrapper.text()).not.toContain('common.save');
    });

    it('adds an unselected tile and exposes the save button', async () => {
        const wrapper = mountPicker();

        const tiles = wrapper.findAll('button[aria-pressed]');
        // Third tile (id 3) is unselected; click to add it.
        await tiles[2].trigger('click');

        expect(wrapper.text()).toContain('common.save');
    });

    it('posts the ordered selection with the first image primary', async () => {
        const put = vi.spyOn(router, 'put').mockImplementation(() => undefined as never);

        const wrapper = mountPicker();

        // Add tile 3, then save.
        await wrapper.findAll('button[aria-pressed]')[2].trigger('click');

        const save = wrapper.findAll('button').find((button) => button.text().includes('common.save'));
        await save?.trigger('click');

        expect(put).toHaveBeenCalledWith('/variant/media', { ids: [1, 2, 3] }, expect.anything());
    });

    it('removes a selected tile', async () => {
        const wrapper = mountPicker();

        // Toggle tile 1 off.
        await wrapper.findAll('button[aria-pressed]')[0].trigger('click');

        expect(wrapper.text()).toContain('common.save');
    });
});
