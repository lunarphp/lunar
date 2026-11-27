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

const mountCard = (values: Record<string, unknown>, fieldPrefix = '', incomingUrl: string | null = '/incoming') =>
    mount(InventoryCard, {
        props: {
            values: reactive(values),
            fieldPrefix,
            stock: { aggregate, levels },
            adjustUrl: '/stock',
            incomingUrl,
        },
    });

const baseValues = (prefix = ''): Record<string, unknown> => ({
    [`${prefix}continue_selling`]: true,
    [`${prefix}sell_against_incoming`]: false,
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

    it('shows the incoming toggle only once continue-selling is off', async () => {
        const values = baseValues('variant:');
        const wrapper = mountCard(values, 'variant:');

        const toggle = (label: string) => wrapper.find(`[aria-label="${label}"]`);

        expect(toggle('products.field_sell_against_incoming').exists()).toBe(false);

        await toggle('products.field_continue_selling').trigger('click');

        expect(values['variant:continue_selling']).toBe(false);

        await toggle('products.field_sell_against_incoming').trigger('click');

        expect(values['variant:sell_against_incoming']).toBe(true);
    });

    it('commits an inline on-hand edit as an adjustment post', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => undefined as never);

        const wrapper = mountCard(baseValues());

        const onHand = wrapper.find('input[aria-label="products.stock_on_hand — Warehouse"]');
        await onHand.setValue('25');
        await onHand.trigger('blur');

        expect(post).toHaveBeenCalledWith('/stock', { location_id: 1, on_hand: 25 }, expect.anything());
    });

    it.each([
        ['products.stock_on_hand — Warehouse', '10'],
        ['products.stock_incoming — Warehouse', '0'],
    ])('keeps showing the entered %s figure until its save finishes', async (label, saved) => {
        let finish: () => void = () => {};
        const post = vi.spyOn(router, 'post').mockImplementation((_url, _data, options) => {
            finish = () => options?.onFinish?.(undefined as never);
        });

        const wrapper = mountCard(baseValues());

        const input = wrapper.find<HTMLInputElement>(`input[aria-label="${label}"]`);
        await input.setValue('25');
        await input.trigger('keydown', { key: 'Enter' });
        await input.trigger('blur');

        expect(post).toHaveBeenCalledTimes(1);
        expect(input.element.value).toBe('25');

        // These props never change, so once the save ends the cell falls back to them.
        finish();
        await wrapper.vm.$nextTick();

        expect(input.element.value).toBe(saved);
    });

    it('does not post when the on-hand figure is unchanged', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => undefined as never);

        const wrapper = mountCard(baseValues());

        const onHand = wrapper.find('input[aria-label="products.stock_on_hand — Warehouse"]');
        await onHand.setValue('10');
        await onHand.trigger('blur');

        expect(post).not.toHaveBeenCalled();
    });

    it('commits an inline incoming edit to the incoming url', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => undefined as never);

        const wrapper = mountCard(baseValues());

        const incoming = wrapper.find('input[aria-label="products.stock_incoming — Warehouse"]');
        await incoming.setValue('12');
        await incoming.trigger('blur');

        expect(post).toHaveBeenCalledWith('/incoming', { location_id: 1, incoming: 12 }, expect.anything());
    });

    it('shows incoming read-only when entry is turned off', () => {
        const wrapper = mountCard(baseValues(), '', null);

        expect(wrapper.find('input[aria-label="products.stock_incoming — Warehouse"]').exists()).toBe(false);
    });

    it('writes ordering quantities to prefixed keys', async () => {
        const values = baseValues('variant:');
        const wrapper = mountCard(values, 'variant:');

        // The three quantity fields render without an aria-label, in order:
        // unit_quantity, min_quantity, quantity_increment.
        const quantityInputs = wrapper.findAll('input[type="number"]').filter((input) => !input.attributes('aria-label'));
        expect(quantityInputs).toHaveLength(3);
        await quantityInputs[1].setValue('6');

        expect(values['variant:min_quantity']).toBe(6);
    });
});
