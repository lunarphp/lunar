import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import MediaManager from './MediaManager.vue';

vi.mock('@inertiajs/vue3', () => ({
    router: {
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
}));

import { router } from '@inertiajs/vue3';

const item = (id: number, primary = false) => ({
    id,
    url: `media-${id}.jpg`,
    original_url: `media-${id}-original.jpg`,
    name: null,
    alt: `Image ${id}`,
    caption: null,
    focal: { x: 25, y: 75 },
    primary,
    update_url: `/panel/brands/1/media/${id}`,
    destroy_url: `/panel/brands/1/media/${id}`,
});

const mountManager = (items = [item(1, true), item(2)]) =>
    mount(MediaManager, {
        props: {
            items,
            storeUrl: '/panel/brands/1/media',
            reorderUrl: '/panel/brands/1/media/reorder',
        },
    });

describe('MediaManager', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('shows the drop-zone empty state without items', () => {
        const wrapper = mountManager([]);

        expect(wrapper.text()).toContain('media.empty_title');
        expect(wrapper.findAll('img')).toHaveLength(0);
    });

    it('renders tiles with the focal point applied as object-position', () => {
        const wrapper = mountManager();

        const image = wrapper.find('img');

        expect(image.attributes('style')).toContain('object-position: 25% 75%');
        expect(wrapper.text()).toContain('media.hero');
    });

    it('persists a drag reorder on drop', async () => {
        const wrapper = mountManager();

        const tiles = wrapper.findAll('[draggable="true"]');

        await tiles[1].trigger('dragstart');
        await tiles[0].trigger('dragover');
        await tiles[1].trigger('dragend');

        expect(router.post).toHaveBeenCalledWith(
            '/panel/brands/1/media/reorder',
            { ids: [2, 1] },
            expect.anything(),
        );
    });

    it('does not persist when the order is unchanged', async () => {
        const wrapper = mountManager();

        const tiles = wrapper.findAll('[draggable="true"]');

        await tiles[0].trigger('dragstart');
        await tiles[0].trigger('dragend');

        expect(router.post).not.toHaveBeenCalled();
    });
});
