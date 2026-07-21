import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import ShippingCard from './ShippingCard.vue';

const measurements = { length: ['mm', 'cm', 'm'], weight: ['kg', 'g'] };

const mountCard = (values: Record<string, unknown>, fieldPrefix = '') =>
    mount(ShippingCard, {
        props: {
            values: reactive(values),
            fieldPrefix,
            measurements,
        },
        global: {
            stubs: {
                Toggle: { props: ['on'], emits: ['toggle'], template: '<button :aria-pressed="on" @click="$emit(\'toggle\')" />' },
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
        expect(wrapper.text()).toContain('products.field_shippable_off_hint');
    });

    it('shows dimension fields when shippable', () => {
        const wrapper = mountCard({ shippable: true });

        // weight, length, width, height.
        expect(wrapper.findAll('input[type="number"]')).toHaveLength(4);
    });

    it('toggles shippable on the prefixed key', async () => {
        const values = { 'variant:shippable': true };
        const wrapper = mountCard(values, 'variant:');

        await wrapper.find('button[aria-pressed]').trigger('click');

        expect(values['variant:shippable']).toBe(false);
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
});
