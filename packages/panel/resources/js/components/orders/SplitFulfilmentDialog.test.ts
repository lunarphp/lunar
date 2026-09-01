import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import SplitFulfilmentDialog from './SplitFulfilmentDialog.vue';
import { makeFulfilment, makeLine } from './fixtures';

let wrapper: VueWrapper | null = null;

const body = (): HTMLElement => document.body;

// One line, three allocated units.
async function mountDialog(): Promise<VueWrapper> {
    wrapper = mount(SplitFulfilmentDialog, {
        props: { open: true, fulfilment: makeFulfilment({ lines: [makeLine({ quantity: 3 })] }) },
        attachTo: document.body,
    });
    await nextTick();

    return wrapper;
}

const quantityInput = (): HTMLInputElement => body().querySelector<HTMLInputElement>('input[type="number"]')!;

const confirmButton = (): HTMLButtonElement =>
    [...body().querySelectorAll('button')].find((button) => button.textContent?.includes('orders.split_confirm'))!;

async function setQuantity(value: string): Promise<void> {
    const input = quantityInput();
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
    await nextTick();
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('SplitFulfilmentDialog', () => {
    it('starts with nothing selected and the confirm disabled', async () => {
        await mountDialog();

        expect(quantityInput().value).toBe('0');
        expect(confirmButton().hasAttribute('disabled')).toBe(true);
    });

    it('enables confirm for a partial move only', async () => {
        await mountDialog();

        await setQuantity('2');
        expect(confirmButton().hasAttribute('disabled')).toBe(false);

        // Moving everything is a merge, not a split.
        await setQuantity('3');
        expect(confirmButton().hasAttribute('disabled')).toBe(true);
    });

    it('clamps an over-allocation back to the line quantity', async () => {
        await mountDialog();

        await setQuantity('99');
        expect(quantityInput().value).toBe('3');
    });
});
