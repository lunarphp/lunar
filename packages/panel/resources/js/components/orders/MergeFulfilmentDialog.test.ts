import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import MergeFulfilmentDialog from './MergeFulfilmentDialog.vue';
import { makeFulfilment } from './fixtures';

let wrapper: VueWrapper | null = null;

const body = (): HTMLElement => document.body;

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('MergeFulfilmentDialog', () => {
    it('lists the candidate targets and preselects the first', async () => {
        wrapper = mount(MergeFulfilmentDialog, {
            props: {
                open: true,
                fulfilment: makeFulfilment({
                    merge_targets: [
                        { id: 6, reference: 'FUL-6', quantity: 3 },
                        { id: 7, reference: 'FUL-7', quantity: 1 },
                    ],
                }),
            },
            attachTo: document.body,
        });
        await nextTick();

        expect(body().textContent).toContain('FUL-6');
        expect(body().textContent).toContain('FUL-7');

        const radios = [...body().querySelectorAll<HTMLInputElement>('input[type="radio"]')];
        expect(radios).toHaveLength(2);
        expect(radios[0].checked).toBe(true);

        const confirm = [...body().querySelectorAll('button')].find((button) =>
            button.textContent?.includes('orders.merge_confirm'),
        );
        expect(confirm!.hasAttribute('disabled')).toBe(false);
    });
});
