import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { createI18n } from 'vue-i18n';
import MediaManager from './MediaManager.vue';

// Panel props feed the uploader hint; each test can set the shared limit.
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

// Real interpolation so the KB to MB conversion is observable in the hint.
const i18n = createI18n({
    legacy: false,
    locale: 'en',
    messages: { en: { media: { file_hint: 'JPG, PNG or WebP. Max {size} MB each.' } } },
});

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
        global: { plugins: [i18n] },
    });

describe('MediaManager', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        pageProps.panel = { media_max_kb: 8192 };
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

    it('renders the max upload size from the shared panel limit', () => {
        const wrapper = mountManager();

        expect(wrapper.text()).toContain('Max 8 MB each.');
    });

    it('reflects a changed limit and formats a fractional megabyte', () => {
        pageProps.panel = { media_max_kb: 2560 };

        const wrapper = mountManager();

        expect(wrapper.text()).toContain('Max 2.5 MB each.');
    });

    it('falls back to 8 MB when the limit is absent', () => {
        pageProps.panel = {};

        const wrapper = mountManager();

        expect(wrapper.text()).toContain('Max 8 MB each.');
    });
});
