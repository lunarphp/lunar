import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import ShippingCard from './ShippingCard.vue';

const measurements = { length: ['mm', 'cm', 'm'], weight: ['kg', 'g'] };

const fulfilmentMethods = [
    { key: 'shipping', label: 'Shipping', physical: true, available: true },
    { key: 'pickup', label: 'Pickup', physical: true, available: true },
    { key: 'digital', label: 'Digital', physical: false, available: true },
];

// No location accepts pickup.
const withoutPickup = fulfilmentMethods.map((method) => (method.key === 'pickup' ? { ...method, available: false } : method));

const mountCard = (values: Record<string, unknown>, fieldPrefix = '', methods = fulfilmentMethods) =>
    mount(ShippingCard, {
        props: {
            values: reactive(values),
            fieldPrefix,
            measurements,
            fulfilmentMethods: methods,
        },
        global: {
            stubs: {
                Select: {
                    props: ['modelValue'],
                    emits: ['update:modelValue'],
                    template: '<select :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><slot /></select>',
                },
            },
        },
    });

describe('ShippingCard', () => {
    it('hides dimension fields while not shippable', () => {
        const wrapper = mountCard({ shippable: false });

        expect(wrapper.findAll('input[type="number"]')).toHaveLength(0);
        expect(wrapper.text()).toContain('products.field_fulfilment_none_hint');
    });

    it('shows dimension fields when shippable', () => {
        const wrapper = mountCard({ shippable: true });

        // weight, length, width, height.
        expect(wrapper.findAll('input[type="number"]')).toHaveLength(4);
    });

    it('writes a dimension value to the prefixed key as a number', async () => {
        const values: Record<string, unknown> = { 'variant:shippable': true };
        const wrapper = mountCard(values, 'variant:');

        // First number input is the weight value.
        await wrapper.findAll('input[type="number"]')[0].setValue('1.5');

        expect(values['variant:weight_value']).toBe(1.5);
    });

    it('nulls a cleared dimension value', async () => {
        const values: Record<string, unknown> = { 'variant:shippable': true, 'variant:weight_value': 2 };
        const wrapper = mountCard(values, 'variant:');

        await wrapper.findAll('input[type="number"]')[0].setValue('');

        expect(values['variant:weight_value']).toBeNull();
    });

    it('drives length, width and height from the single dimension unit', async () => {
        const values: Record<string, unknown> = { 'variant:shippable': true, 'variant:length_unit': 'mm' };
        const wrapper = mountCard(values, 'variant:');

        // Selects render in order: weight unit, then dimension unit.
        await wrapper.findAll('select')[1].setValue('cm');

        expect(values['variant:length_unit']).toBe('cm');
        expect(values['variant:width_unit']).toBe('cm');
        expect(values['variant:height_unit']).toBe('cm');
    });

    it('auto-calculates volume as length times width times height', () => {
        const wrapper = mountCard({
            shippable: true,
            length_value: 2,
            width_value: 3,
            height_value: 4,
        });

        expect(wrapper.text()).toContain('24');
    });

    const boxes = (wrapper: ReturnType<typeof mountCard>) => wrapper.findAll('[role="checkbox"]');
    const states = (wrapper: ReturnType<typeof mountCard>) => boxes(wrapper).map((box) => box.attributes('data-state'));

    it('shows every physical method ticked for an unrestricted shippable variant', async () => {
        const values: Record<string, unknown> = { 'variant:shippable': true, 'variant:fulfilment_methods': null };
        const wrapper = mountCard(values, 'variant:');

        expect(states(wrapper)).toEqual(['checked', 'checked', 'unchecked']);

        await boxes(wrapper)[0].trigger('click');
        expect(values['variant:fulfilment_methods']).toEqual(['pickup']);
        expect(values['variant:shippable']).toBe(true);

        // Ticking every physical method again stores null, which also allows methods registered later.
        await boxes(wrapper)[0].trigger('click');
        expect(values['variant:fulfilment_methods']).toBeNull();
    });

    it('stops shipping when the last physical method is unticked', async () => {
        const values: Record<string, unknown> = { shippable: true, fulfilment_methods: ['pickup'] };
        const wrapper = mountCard(values);

        await boxes(wrapper)[1].trigger('click');

        expect(values.shippable).toBe(false);
        expect(values.fulfilment_methods).toBeNull();
        expect(wrapper.findAll('input[type="number"]')).toHaveLength(0);
        expect(states(wrapper)).toEqual(['unchecked', 'unchecked', 'unchecked']);
    });

    it('switches to a non-physical method and back without mixing them', async () => {
        const values: Record<string, unknown> = { shippable: true, fulfilment_methods: null };
        const wrapper = mountCard(values);

        await boxes(wrapper)[2].trigger('click');
        expect(values.shippable).toBe(false);
        expect(values.fulfilment_methods).toEqual(['digital']);
        expect(states(wrapper)).toEqual(['unchecked', 'unchecked', 'checked']);

        await boxes(wrapper)[1].trigger('click');
        expect(values.shippable).toBe(true);
        expect(values.fulfilment_methods).toEqual(['pickup']);
        expect(states(wrapper)).toEqual(['unchecked', 'checked', 'unchecked']);
    });

    it('leaves an unavailable method out of an unrestricted variant, keeping it unrestricted', async () => {
        const values: Record<string, unknown> = { shippable: true, fulfilment_methods: null };
        const wrapper = mountCard(values, '', withoutPickup);

        expect(wrapper.text()).not.toContain('Pickup');
        expect(wrapper.text()).not.toContain('products.fulfilment_methods_unavailable');
        expect(states(wrapper)).toEqual(['checked', 'unchecked']);

        await boxes(wrapper)[0].trigger('click');
        await boxes(wrapper)[0].trigger('click');

        // Shipping is every listed physical method, so pickup comes back once a location accepts it.
        expect(values.fulfilment_methods).toBeNull();
    });

    it('warns about an unavailable method the variant still uses', async () => {
        const values: Record<string, unknown> = { shippable: true, fulfilment_methods: ['pickup'] };
        const wrapper = mountCard(values, '', withoutPickup);

        expect(wrapper.text()).toContain('Pickup');
        expect(wrapper.text()).toContain('products.fulfilment_methods_unavailable');
        expect(states(wrapper)).toEqual(['unchecked', 'checked', 'unchecked']);

        // Shipping plus pickup is every physical method, so the variant is unrestricted again.
        await boxes(wrapper)[0].trigger('click');
        expect(values.fulfilment_methods).toBeNull();
        expect(values.shippable).toBe(true);
        expect(wrapper.text()).not.toContain('Pickup');
        expect(wrapper.text()).not.toContain('products.fulfilment_methods_unavailable');
    });
});
