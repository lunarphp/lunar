import { afterEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount, type VueWrapper } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({
    Link: { template: '<a><slot /></a>' },
    usePage: () => ({ props: { panel: { path: 'panel' } } }),
}));

vi.mock('../../lib/http', async (importOriginal) => {
    const actual = await importOriginal<typeof import('../../lib/http')>();

    return { ...actual, http: { get: vi.fn() } };
});

import { http } from '../../lib/http';
import BundleCard from '../../../../../bundles/resources/js/components/BundleCard.vue';

// The bundles add-on's card in the product edit page's
// `products.edit:variants:after` zone, which hands it the page's `product` prop.
const summary = (id: number) => ({
    variant: { id, public_id: `v${id}`, sku: `SKU-${id}`, name: 'Mug', option: null, thumbnail: null, edit_url: `/panel/variants/${id}` },
    defined: false,
    bundle: null,
    default_language: 'en',
    urls: { show: '', define: '', destroy: '', components: '', groups: '', search: '' },
});

let wrapper: VueWrapper | null = null;

function mountCard(product: { id: number }): VueWrapper {
    wrapper = mount(BundleCard, {
        props: { product },
        global: {
            stubs: {
                BundleEditor: { props: ['summary'], template: '<div data-test="editor">{{ summary.variant.id }}</div>' },
            },
        },
    });

    return wrapper;
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    vi.mocked(http.get).mockReset();
});

describe('BundleCard on the product edit page', () => {
    it('reloads the variants when the page receives a fresh product after collapsing to a simple product', async () => {
        vi.mocked(http.get).mockResolvedValueOnce({ variants: [summary(1), summary(2), summary(3)] });

        const card = mountCard({ id: 7 });
        await flushPromises();

        expect(card.findAll('li')).toHaveLength(3);

        // Collapsing posts to the generate endpoint and redirects back: Inertia
        // keeps the page mounted and swaps in fresh props.
        vi.mocked(http.get).mockResolvedValueOnce({ variants: [summary(1)] });
        await card.setProps({ product: { id: 7 } });
        await flushPromises();

        expect(http.get).toHaveBeenCalledTimes(2);
        expect(http.get).toHaveBeenLastCalledWith('/panel/bundles/products/7');
        expect(card.findAll('li')).toHaveLength(0);
        expect(card.get('[data-test="editor"]').text()).toBe('1');
    });

    it('does not refetch when a partial reload keeps the same product', async () => {
        const product = { id: 7 };
        vi.mocked(http.get).mockResolvedValueOnce({ variants: [summary(1), summary(2)] });

        const card = mountCard(product);
        await flushPromises();
        await card.setProps({ product });
        await flushPromises();

        expect(http.get).toHaveBeenCalledTimes(1);
        expect(card.findAll('li')).toHaveLength(2);
    });

    it('keeps the newest response when an older request resolves last', async () => {
        let resolveFirst: (value: unknown) => void = () => {};
        vi.mocked(http.get)
            .mockImplementationOnce(() => new Promise((resolve) => { resolveFirst = resolve; }))
            .mockResolvedValueOnce({ variants: [summary(1)] });

        const card = mountCard({ id: 7 });
        await card.setProps({ product: { id: 7 } });
        await flushPromises();
        resolveFirst({ variants: [summary(1), summary(2)] });
        await flushPromises();

        expect(card.findAll('li')).toHaveLength(0);
        expect(card.get('[data-test="editor"]').text()).toBe('1');
    });
});
