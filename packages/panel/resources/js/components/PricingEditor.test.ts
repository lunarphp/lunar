import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { router } from '@inertiajs/vue3';
import PricingEditor, { type CurrencyOption, type PriceRow } from './PricingEditor.vue';

const currencies: CurrencyOption[] = [
    { id: 1, code: 'GBP', decimal_places: 2, default: true },
    { id: 2, code: 'USD', decimal_places: 2, default: false },
];

const basePrice: PriceRow = {
    id: 10,
    currency_id: 1,
    customer_group_id: null,
    min_quantity: 1,
    price: 1099,
    list_price: 1299,
    update_url: '/prices/10',
    destroy_url: '/prices/10',
};

const mountEditor = (prices: PriceRow[] = []) =>
    mount(PricingEditor, {
        props: {
            prices,
            currencies,
            customerGroups: [{ id: 5, name: 'Wholesale' }],
            storeUrl: '/prices',
        },
        global: {
            stubs: { Select: { template: '<select><slot /></select>' } },
        },
    });

describe('PricingEditor', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.restoreAllMocks();
        vi.useRealTimers();
    });

    it('renders one base row per currency, seeded in major units', () => {
        const wrapper = mountEditor([basePrice]);

        const inputs = wrapper.findAll('input[type="number"]');

        // First base row (GBP): amount 10.99, compare-at 12.99.
        expect((inputs[0].element as HTMLInputElement).value).toBe('10.99');
        expect((inputs[1].element as HTMLInputElement).value).toBe('12.99');
        expect(wrapper.text()).toContain('GBP');
        expect(wrapper.text()).toContain('USD');
    });

    it('prefixes each currency row input with its narrow currency symbol', () => {
        const wrapper = mountEditor([basePrice]);

        // Base section renders a GBP row and a USD row.
        expect(wrapper.text()).toContain('£');
        expect(wrapper.text()).toContain('$');
    });

    it('debounces and posts a base price as integer minor units', async () => {
        const put = vi.spyOn(router, 'put').mockImplementation(() => undefined as never);

        const wrapper = mountEditor([basePrice]);

        await wrapper.findAll('input[type="number"]')[0].setValue('12.50');
        vi.advanceTimersByTime(600);

        expect(put).toHaveBeenCalledWith(
            '/prices/10',
            expect.objectContaining({ currency_id: 1, min_quantity: 1, price: 1250, list_price: 1299 }),
            expect.anything(),
        );
    });

    it('posts a new customer-group row to the store url', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => undefined as never);

        const wrapper = mountEditor();

        const addGroup = wrapper.findAll('button').find((button) => button.text().includes('pricing.group_add'));
        await addGroup?.trigger('click');

        // The group row's amount input is the first non-base number input.
        const amount = wrapper.findAll('input[type="number"]').find((input) =>
            (input.attributes('placeholder') ?? '').includes('pricing.amount'));
        await amount?.setValue('9.00');
        vi.advanceTimersByTime(600);

        expect(post).toHaveBeenCalledWith(
            '/prices',
            expect.objectContaining({ customer_group_id: 5, min_quantity: 1, price: 900 }),
            expect.anything(),
        );
    });

    it('deletes a saved row through its destroy url', async () => {
        const destroy = vi.spyOn(router, 'delete').mockImplementation(() => undefined as never);

        const group: PriceRow = { ...basePrice, id: 20, customer_group_id: 5, update_url: '/prices/20', destroy_url: '/prices/20' };
        const wrapper = mountEditor([basePrice, group]);

        const remove = wrapper.find('button[aria-label="pricing.remove_row"]');
        await remove.trigger('click');

        expect(destroy).toHaveBeenCalledWith('/prices/20', expect.anything());
    });
});
