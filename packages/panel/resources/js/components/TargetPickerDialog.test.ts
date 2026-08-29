import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

vi.mock('../lib/http', () => ({
    http: {
        get: vi.fn().mockResolvedValue({
            data: [
                { kind: 'products', id: 1, label: 'Widget', hint: 'WID-1' },
                { kind: 'brands', id: 7, label: 'Stark', hint: null },
            ],
        }),
    },
}));

import { http } from '../lib/http';
import TargetPickerDialog from './TargetPickerDialog.vue';

// Reka's DialogPortal teleports content to document.body, out of the
// wrapper's reach; interactions query the document instead.
const body = (): HTMLElement => document.body;

const buttonWith = (text: string): HTMLButtonElement | undefined =>
    [...body().querySelectorAll('button')].find((button) => button.textContent?.trim().startsWith(text));

let wrapper: VueWrapper | null = null;

async function mountDialog(kinds: string[] = ['products', 'brands']): Promise<VueWrapper> {
    wrapper = mount(TargetPickerDialog, {
        props: { open: true, searchUrl: '/panel/discounts/1/targets/search', bucket: 'limitation', kinds },
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

describe('TargetPickerDialog', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('scopes the search to the bucket', async () => {
        await mountDialog();

        expect((http.get as ReturnType<typeof vi.fn>).mock.calls[0][0]).toContain('bucket=limitation');
    });

    it('lists every kind in one result set, labelled', async () => {
        await mountDialog();

        expect(body().textContent).toContain('Widget');
        expect(body().textContent).toContain('Stark');
        expect(body().textContent).toContain('discounts.kind_products');
    });

    it('shows where a result lives and repeats it as a tooltip', async () => {
        await mountDialog();

        expect(body().textContent).toContain('WID-1');

        const titled = [...body().querySelectorAll('[title]')]
            .map((element) => element.getAttribute('title'));

        expect(titled).toContain('Widget \u2014 WID-1');
    });

    it('narrows to one kind when a chip is chosen', async () => {
        await mountDialog();

        buttonWith('discounts.kind_brands')?.click();
        await nextTick();
        await vi.waitFor(() => expect((http.get as ReturnType<typeof vi.fn>).mock.calls.length).toBeGreaterThan(1));

        expect((http.get as ReturnType<typeof vi.fn>).mock.calls.at(-1)?.[0]).toContain('kinds%5B%5D=brands');
    });

    it('offers no kind chips when the bucket takes a single kind', async () => {
        await mountDialog(['customers']);

        expect(buttonWith('common.all')).toBeUndefined();
    });

    it('emits the picked rows with their kind', async () => {
        const dialog = await mountDialog();

        const checkboxes = [...body().querySelectorAll('[role="checkbox"], input[type="checkbox"]')];
        (checkboxes[1] as HTMLElement).click();
        await nextTick();

        buttonWith('discounts.target_add_selected')?.click();
        await nextTick();

        expect(dialog.emitted('add')?.at(-1)?.[0]).toEqual([
            { kind: 'brands', id: 7, label: 'Stark', hint: null },
        ]);
        expect(dialog.emitted('update:open')?.at(-1)).toEqual([false]);
    });
});
