import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import TaxCard from './TaxCard.vue';

const taxClasses = [
    { id: 1, name: 'Standard' },
    { id: 2, name: 'Reduced' },
];

const mountCard = (values: Record<string, unknown>, fieldPrefix = '') =>
    mount(TaxCard, {
        props: { values: reactive(values), fieldPrefix, taxClasses },
    });

describe('TaxCard', () => {
    it('lists the available tax classes', () => {
        const wrapper = mountCard({ 'variant:tax_class_id': 1 }, 'variant:');

        expect(wrapper.text()).toContain('Standard');
        expect(wrapper.text()).toContain('Reduced');
    });

    it('writes the selected tax class to the prefixed key as a number', async () => {
        const values: Record<string, unknown> = { 'variant:tax_class_id': 1 };
        const wrapper = mountCard(values, 'variant:');

        await wrapper.find('select').setValue('2');

        expect(values['variant:tax_class_id']).toBe(2);
    });

    it('writes the tax reference to the prefixed key', async () => {
        const values: Record<string, unknown> = {};
        const wrapper = mountCard(values, 'variant:');

        await wrapper.find('input').setValue('EU-REDUCED');

        expect(values['variant:tax_ref']).toBe('EU-REDUCED');
    });

    it('nulls a cleared tax reference', async () => {
        const values: Record<string, unknown> = { tax_ref: 'X' };
        const wrapper = mountCard(values);

        await wrapper.find('input').setValue('');

        expect(values.tax_ref).toBeNull();
    });
});
