import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

vi.mock('../lib/http', () => ({
    http: {
        get: vi.fn().mockResolvedValue({
            data: [
                { id: 1, name: 'Rain Jacket', sku: 'RJ-001', thumbnail: null, brand: 'Stark', status: 'published' },
                { id: 2, name: 'Sun Hat', sku: 'SH-001', thumbnail: null, brand: null, status: 'draft' },
            ],
        }),
    },
}));

import { http } from '../lib/http';
import ProductPickerDialog from './ProductPickerDialog.vue';

// Reka's DialogPortal teleports content to document.body, out of the
// wrapper's reach; interactions query the document instead.
const body = (): HTMLElement => document.body;

let wrapper: VueWrapper | null = null;

async function mountDialog(existingIds: number[] = []): Promise<VueWrapper> {
    wrapper = mount(ProductPickerDialog, {
        props: { open: true, searchUrl: '/panel/catalog/products/search', existingIds },
        attachTo: document.body,
    });

    await nextTick();
    await vi.waitFor(() => expect(http.get).toHaveBeenCalled());
    await nextTick();

    return wrapper;
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('ProductPickerDialog', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('searches on open and lists the results', async () => {
        await mountDialog();

        expect((http.get as ReturnType<typeof vi.fn>).mock.calls[0][0]).toContain('/panel/catalog/products/search');
        expect(body().textContent).toContain('Rain Jacket');
        expect(body().textContent).toContain('RJ-001');
    });

    it('disables already-attached products', async () => {
        await mountDialog([1]);

        const checkboxes = [...body().querySelectorAll('[role="checkbox"], input[type="checkbox"], button[aria-label="Rain Jacket"]')];

        expect(body().textContent).toContain('products.already_added');
        expect(checkboxes.length).toBeGreaterThan(0);
    });

    it('emits the picked ids', async () => {
        const dialog = await mountDialog();

        const row = [...body().querySelectorAll('label')].find((el) => el.textContent?.includes('Sun Hat'))!;
        (row.querySelector('[role="checkbox"], input[type="checkbox"], button') as HTMLElement).click();
        await nextTick();

        const addButton = [...body().querySelectorAll('button')].find((el) =>
            el.textContent?.includes('products.add_selected'))!;
        addButton.click();
        await nextTick();

        expect(dialog.emitted('add')).toEqual([[[2]]]);
    });
});
