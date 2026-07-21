import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import IdentifiersCard from './IdentifiersCard.vue';

const mountCard = (values: Record<string, unknown>, fieldPrefix = '') =>
    mount(IdentifiersCard, {
        props: { values: reactive(values), fieldPrefix },
    });

describe('IdentifiersCard', () => {
    it('seeds each identifier input from the (prefixed) draft value', () => {
        const wrapper = mountCard({ 'variant:sku': 'WID-1', 'variant:gtin': '', 'variant:mpn': '', 'variant:ean': '' }, 'variant:');

        expect((wrapper.findAll('input')[0].element as HTMLInputElement).value).toBe('WID-1');
    });

    it('writes an identifier to the prefixed key', async () => {
        const values: Record<string, unknown> = {};
        const wrapper = mountCard(values, 'variant:');

        await wrapper.findAll('input')[0].setValue('NEW-SKU');

        expect(values['variant:sku']).toBe('NEW-SKU');
    });

    it('nulls a cleared identifier rather than writing an empty string', async () => {
        const values: Record<string, unknown> = { sku: 'WID-1' };
        const wrapper = mountCard(values);

        await wrapper.findAll('input')[0].setValue('');

        expect(values.sku).toBeNull();
    });

    it('surfaces a field error under its input', () => {
        const wrapper = mountCard({}, 'variant:');

        const withError = mount(IdentifiersCard, {
            props: {
                values: reactive({}),
                fieldPrefix: 'variant:',
                errors: { 'variant:sku': 'The sku has already been taken.' },
            },
        });

        expect(wrapper.text()).not.toContain('already been taken');
        expect(withError.text()).toContain('The sku has already been taken.');
    });
});
