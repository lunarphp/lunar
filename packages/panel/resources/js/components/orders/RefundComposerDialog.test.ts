import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import RefundComposerDialog from './RefundComposerDialog.vue';
import type { RefundableLineData, ShippingLineData } from './types';

let wrapper: VueWrapper | null = null;

const body = (): HTMLElement => document.body;

const line = (overrides: Partial<RefundableLineData> = {}): RefundableLineData => ({
    id: 1,
    quantity: 2,
    line_quantity: 2,
    description: 'Blue Widget',
    option: null,
    identifier: 'SKU-1',
    thumbnail: null,
    unit_price: '£10.00',
    sub_total: '£20.00',
    discount_total: null,
    tax: [],
    total: '£20.00',
    notes: null,
    refundable_quantity: 2,
    refund_unit_amount: 10,
    ...overrides,
});

const shippingLine = (overrides: Partial<ShippingLineData> = {}): ShippingLineData => ({
    id: 9,
    description: 'Standard Delivery',
    total: '£5.00',
    amount: 5,
    ...overrides,
});

async function mountDialog(props: Record<string, unknown> = {}): Promise<VueWrapper> {
    wrapper = mount(RefundComposerDialog, {
        props: {
            open: true,
            lines: [line()],
            shippingLines: [shippingLine()],
            charges: [{ id: 1, reference: 'ch_1', amount: 100, amount_formatted: '£100.00' }],
            availableToRefund: 100,
            availableToRefundFormatted: '£100.00',
            url: '/refund',
            ...props,
        },
        attachTo: document.body,
    });
    await nextTick();

    return wrapper;
}

const quantityInput = (): HTMLInputElement => body().querySelector<HTMLInputElement>('input[type="number"]')!;
const shippingCheckbox = () => body().querySelector('[role="checkbox"]') as HTMLElement;
const confirmButton = (): HTMLButtonElement =>
    [...body().querySelectorAll('button')].find((button) => button.textContent?.includes('orders.action_refund'))!;

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

describe('RefundComposerDialog', () => {
    it('starts with nothing selected and the confirm disabled', async () => {
        await mountDialog();

        expect(quantityInput().value).toBe('0');
        expect(confirmButton().hasAttribute('disabled')).toBe(true);
    });

    it('enables confirm once a line quantity is selected', async () => {
        await mountDialog();

        await setQuantity('1');
        expect(confirmButton().hasAttribute('disabled')).toBe(false);
    });

    it('clamps an over-selection back to the refundable quantity', async () => {
        await mountDialog();

        await setQuantity('99');
        expect(quantityInput().value).toBe('2');
    });

    it('includes shipping when toggled', async () => {
        await mountDialog();

        await shippingCheckbox().click();
        await nextTick();

        expect(confirmButton().hasAttribute('disabled')).toBe(false);
        expect(body().textContent).toContain('orders.refund_total');
    });

    it('disables confirm when nothing is refundable', async () => {
        await mountDialog({ lines: [], shippingLines: [] });

        expect(body().textContent).toContain('orders.refund_nothing_selectable');
        expect(confirmButton().hasAttribute('disabled')).toBe(true);
    });

    it('defaults notify on and can toggle it off', async () => {
        await mountDialog();

        const toggle = body().querySelector('[role="switch"]') as HTMLElement;
        expect(toggle.getAttribute('aria-checked')).toBe('true');

        toggle.click();
        await nextTick();
        expect(toggle.getAttribute('aria-checked')).toBe('false');
    });

    it('pre-fills the manual adjustment from the settlement banner', async () => {
        await mountDialog({ lines: [], shippingLines: [], prefillAdjustment: 12.5 });

        const adjustmentInput = body().querySelectorAll<HTMLInputElement>('input[type="number"]')[0];
        expect(adjustmentInput.value).toBe('12.5');
        expect(confirmButton().hasAttribute('disabled')).toBe(false);
    });
});
