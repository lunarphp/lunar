import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import InventoryCard, { type StockAggregate, type StockLevelRow } from './InventoryCard.vue';

const aggregate: StockAggregate = {
    on_hand: 10,
    incoming: 0,
    committed: 2,
    reserved: 0,
    unavailable: 1,
    available: 7,
};

const levels: StockLevelRow[] = [
    { location_id: 1, location_name: 'Warehouse', default: true, on_hand: 10, incoming: 0, committed: 2, unavailable: 1 },
];

const mountCard = (values: Record<string, unknown>, fieldPrefix = '') =>
    mount(InventoryCard, {
        props: {
            values: reactive(values),
            fieldPrefix,
            stock: { aggregate, levels },
            adjustUrl: '/stock',
        },
        global: {
            stubs: { StatusSegmentedControl: false },
        },
    });

const baseValues = (prefix = ''): Record<string, unknown> => ({
    [`${prefix}selling_policy`]: 'always',
    [`${prefix}backorder`]: 0,
    [`${prefix}unit_quantity`]: 1,
    [`${prefix}min_quantity`]: 1,
    [`${prefix}quantity_increment`]: 1,
});

describe('InventoryCard', () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('computes per-location available as on_hand minus committed and unavailable', () => {
        const wrapper = mountCard(baseValues());

        // 10 - 2 - 1 = 7.
        expect(wrapper.text()).toContain('7');
    });

    it('writes the selling policy to the prefixed draft key', async () => {
        const values = baseValues('variant:');
        const wrapper = mountCard(values, 'variant:');

        // The segmented control renders a button per policy option.
        const inStock = wrapper.findAll('button').find((button) => button.text().includes('products.selling_policy_in_stock'));
        await inStock?.trigger('click');

        expect(values['variant:selling_policy']).toBe('in_stock');
    });

    it('commits an inline on-hand edit as an adjustment post', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => undefined as never);

        const wrapper = mountCard(baseValues());

        const onHand = wrapper.find('input[aria-label="products.stock_on_hand — Warehouse"]');
        await onHand.setValue('25');
        await onHand.trigger('blur');

        expect(post).toHaveBeenCalledWith('/stock', { location_id: 1, on_hand: 25 }, expect.anything());
    });

    it('does not post when the on-hand figure is unchanged', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => undefined as never);

        const wrapper = mountCard(baseValues());

        const onHand = wrapper.find('input[aria-label="products.stock_on_hand — Warehouse"]');
        await onHand.setValue('10');
        await onHand.trigger('blur');

        expect(post).not.toHaveBeenCalled();
    });

    it('writes ordering quantities to prefixed keys', async () => {
        const values = baseValues('variant:');
        const wrapper = mountCard(values, 'variant:');

        // The four quantity fields render without an aria-label, in order:
        // backorder, unit_quantity, min_quantity, quantity_increment.
        const quantityInputs = wrapper.findAll('input[type="number"]').filter((input) => !input.attributes('aria-label'));
        await quantityInputs[2].setValue('6');

        expect(values['variant:min_quantity']).toBe(6);
    });
});
