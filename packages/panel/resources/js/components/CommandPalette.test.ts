import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

const { httpMock, visitMock, pageProps } = vi.hoisted(() => ({
    httpMock: { get: vi.fn() },
    visitMock: vi.fn(),
    pageProps: {
        panel: { path: 'panel' },
        auth: { user: { id: 42 } },
        searchCommands: [
            { key: 'products.create', label: 'Create product', url: '/panel/products/create', icon: 'plus' },
            { key: 'brands.create', label: 'Create brand', url: '/panel/brands/create', icon: 'plus' },
        ],
        searchSources: [
            { key: 'orders', label: 'Orders', icon: 'cart' },
            { key: 'products', label: 'Products', icon: 'box' },
        ],
    },
}));

vi.mock('../lib/http', () => ({ http: httpMock }));

vi.mock('@inertiajs/vue3', () => ({
    router: { visit: visitMock },
    usePage: () => ({ props: pageProps }),
}));

import CommandPalette from './CommandPalette.vue';
import { useCommandPalette } from '../composables/useCommandPalette';

// Reka's DialogPortal teleports content to document.body, out of the
// wrapper's reach; interactions query the document instead.
const body = (): HTMLElement => document.body;

const productRow = (id: number, label: string) => ({
    kind: 'products',
    kind_label: 'Products',
    icon: 'box',
    id,
    label,
    hint: 'SKU-1',
    url: `/panel/products/${id}/edit`,
});

const orderRow = {
    kind: 'orders',
    kind_label: 'Orders',
    icon: 'cart',
    id: 7,
    label: 'ORD-7',
    hint: 'Ada Lovelace',
    url: '/panel/orders/7',
};

let wrapper: VueWrapper | null = null;

async function openPalette(): Promise<void> {
    wrapper = mount(CommandPalette, { attachTo: document.body });
    useCommandPalette().openPalette();

    await nextTick();
    await nextTick();
}

function input(): HTMLInputElement {
    const element = body().querySelector<HTMLInputElement>('input[role="combobox"]');

    expect(element, 'the palette input').not.toBeNull();

    return element!;
}

async function type(term: string): Promise<void> {
    const element = input();
    element.value = term;
    element.dispatchEvent(new Event('input', { bubbles: true }));

    await nextTick();
    await vi.advanceTimersByTimeAsync(250);
    await nextTick();
}

async function press(key: string): Promise<void> {
    body()
        .querySelector('[role="listbox"]')!
        .dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true }));

    await nextTick();
}

function options(): HTMLElement[] {
    return [...body().querySelectorAll<HTMLElement>('[role="option"]')];
}

describe('CommandPalette', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.clearAllMocks();
        window.localStorage.clear();
        httpMock.get.mockResolvedValue({ data: [] });
        useCommandPalette().closePalette();
    });

    afterEach(() => {
        wrapper?.unmount();
        wrapper = null;
        document.body.innerHTML = '';
        vi.useRealTimers();
    });

    it('stays closed until it is opened', async () => {
        wrapper = mount(CommandPalette, { attachTo: document.body });
        await nextTick();

        expect(body().querySelector('input[role="combobox"]')).toBeNull();
    });

    it('opens on cmd+k', async () => {
        wrapper = mount(CommandPalette, { attachTo: document.body });

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', metaKey: true }));
        await nextTick();

        expect(useCommandPalette().open.value).toBe(true);
    });

    it('opens on ctrl+k for non-mac keyboards', async () => {
        wrapper = mount(CommandPalette, { attachTo: document.body });

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', ctrlKey: true }));
        await nextTick();

        expect(useCommandPalette().open.value).toBe(true);
    });

    it('ignores a bare k', async () => {
        wrapper = mount(CommandPalette, { attachTo: document.body });

        window.dispatchEvent(new KeyboardEvent('keydown', { key: 'k' }));
        await nextTick();

        expect(useCommandPalette().open.value).toBe(false);
    });

    it('offers the quick actions before anything is typed', async () => {
        await openPalette();

        expect(body().textContent).toContain('Create product');
        expect(httpMock.get).not.toHaveBeenCalled();
    });

    it('debounces the search and groups results by source', async () => {
        httpMock.get.mockResolvedValue({ data: [orderRow, productRow(1, 'Lamp')] });

        await openPalette();

        const element = input();
        element.value = 'la';
        element.dispatchEvent(new Event('input', { bubbles: true }));
        await nextTick();

        expect(httpMock.get).not.toHaveBeenCalled();

        await vi.advanceTimersByTimeAsync(250);
        await nextTick();

        expect(httpMock.get).toHaveBeenCalledTimes(1);
        expect(httpMock.get).toHaveBeenCalledWith('/panel/search?q=la');
        expect(body().textContent).toContain('ORD-7');
        expect(body().textContent).toContain('Lamp');
    });

    it('narrows the request to the selected kind chip', async () => {
        await openPalette();

        const chips = [...body().querySelectorAll<HTMLElement>('button[aria-pressed]')];
        chips[2].dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await nextTick();

        await type('lamp');

        expect(httpMock.get).toHaveBeenLastCalledWith('/panel/search?q=lamp&kinds%5B%5D=products');
    });

    it('filters the quick actions against the typed term', async () => {
        await openPalette();
        await type('brand');

        expect(body().textContent).toContain('Create brand');
        expect(body().textContent).not.toContain('Create product');
    });

    it('visits the highlighted row on enter and closes', async () => {
        httpMock.get.mockResolvedValue({ data: [productRow(1, 'Lamp'), productRow(2, 'Lantern')] });

        await openPalette();
        await type('la');

        await press('ArrowDown');
        await press('Enter');

        expect(visitMock).toHaveBeenCalledWith('/panel/products/2/edit', expect.anything());
        expect(useCommandPalette().open.value).toBe(false);
    });

    it('wraps the highlight around the ends of the list', async () => {
        httpMock.get.mockResolvedValue({ data: [productRow(1, 'Lamp'), productRow(2, 'Lantern')] });

        await openPalette();
        await type('la');
        await press('ArrowUp');

        const rows = options();
        expect(rows[rows.length - 1].getAttribute('aria-selected')).toBe('true');
    });

    it('visits a clicked row', async () => {
        httpMock.get.mockResolvedValue({ data: [productRow(3, 'Lamp')] });

        await openPalette();
        await type('lamp');

        options()[0].dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await nextTick();

        expect(visitMock).toHaveBeenCalledWith('/panel/products/3/edit', expect.anything());
    });

    it('prunes a recently viewed record that 404s', async () => {
        window.localStorage.setItem(
            'lunar-panel-recent-records:42',
            JSON.stringify([productRow(9, 'Deleted lamp')]),
        );

        await openPalette();

        expect(body().textContent).toContain('Deleted lamp');

        options()[0].dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await nextTick();

        const visitOptions = visitMock.mock.calls[0][1] as { onHttpException: (r: { status: number }) => void };
        visitOptions.onHttpException({ status: 404 });

        expect(window.localStorage.getItem('lunar-panel-recent-records:42')).not.toContain('Deleted lamp');
    });

    it('reports no matches for a term that finds nothing', async () => {
        await openPalette();
        await type('zzzz');

        expect(body().textContent).toContain('search.no_results');
    });

    it('resets the query when reopened', async () => {
        await openPalette();
        await type('lamp');

        useCommandPalette().closePalette();
        await nextTick();
        useCommandPalette().openPalette();
        await nextTick();

        expect(input().value).toBe('');
    });
});
