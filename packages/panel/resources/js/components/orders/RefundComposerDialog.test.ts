import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import { createI18n } from 'vue-i18n';
import RefundComposerDialog from './RefundComposerDialog.vue';
import type { RefundableLineData, ShippingLineData } from './types';

let wrapper: VueWrapper | null = null;

// The messages the money and limit assertions read; every other key falls
// back to the raw key, as in the shared test setup.
const i18n = createI18n({
    legacy: false,
    locale: 'en',
    missingWarn: false,
    fallbackWarn: false,
    messages: {
        en: {
            orders: {
                refund_total: 'Refunding {amount}',
                refund_exceeds_available: 'This is more than the {amount} available to refund.',
                refund_quantity_exceeded: 'Only {quantity} left to refund.',
            },
        },
    },
});

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
            currency: { code: 'GBP', decimal_places: 2 },
            ...props,
        },
        global: { plugins: [i18n] },
        attachTo: document.body,
    });
    await nextTick();

    return wrapper;
}

const quantityInput = (): HTMLInputElement => body().querySelector<HTMLInputElement>('input[type="number"]')!;
const shippingCheckbox = () => body().querySelector('[role="checkbox"]') as HTMLElement;
const confirmButton = (): HTMLButtonElement =>
    [...body().querySelectorAll('button')].find((button) => button.textContent?.includes('orders.action_refund'))!;

async function typeQuantity(value: string): Promise<void> {
    const input = quantityInput();
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    await nextTick();
}

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
        expect(body().textContent).toContain('Refunding £5.00');
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

    it('formats the refund total in the order currency', async () => {
        await mountDialog();

        await setQuantity('1');
        expect(body().textContent).toContain('Refunding £10.00');
    });

    it('says why confirm is disabled when the total exceeds what is available', async () => {
        await mountDialog({ availableToRefund: 15, availableToRefundFormatted: '£15.00' });

        await setQuantity('2');
        expect(confirmButton().hasAttribute('disabled')).toBe(true);
        expect(body().textContent).toContain('This is more than the £15.00 available to refund.');
    });

    it('flags a quantity above the line refundable quantity before it is clamped', async () => {
        await mountDialog({ lines: [line({ refundable_quantity: 1 })] });

        await typeQuantity('2');
        expect(quantityInput().getAttribute('max')).toBe('1');
        expect(body().textContent).toContain('Only 1 left to refund.');
        expect(confirmButton().hasAttribute('disabled')).toBe(true);
    });

    it('shows server errors reported against a line', async () => {
        const dialog = await mountDialog();

        (dialog.vm as unknown as { form: { setError: (errors: Record<string, string>) => void } }).form.setError({
            'lines.0.quantity': 'The quantity field must be an integer.',
        });
        await nextTick();
        expect(body().textContent).toContain('The quantity field must be an integer.');
    });
});
