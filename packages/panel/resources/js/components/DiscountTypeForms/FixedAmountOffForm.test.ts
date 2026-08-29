import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import FixedAmountOffForm from './FixedAmountOffForm.vue';

const currencies = [
    { id: 1, code: 'GBP', decimal_places: 2, default: true },
    { id: 2, code: 'JPY', decimal_places: 0, default: false },
];

describe('FixedAmountOffForm', () => {
    it('renders an input per currency', () => {
        const wrapper = mount(FixedAmountOffForm, { props: { modelValue: {}, currencies } });

        expect(wrapper.findAll('input')).toHaveLength(2);
        expect(wrapper.text()).toContain('GBP');
        expect(wrapper.text()).toContain('JPY');
    });

    it('takes its step from the currency decimal places', () => {
        const wrapper = mount(FixedAmountOffForm, { props: { modelValue: {}, currencies } });
        const inputs = wrapper.findAll('input');

        expect(inputs[0].attributes('step')).toBe('0.01');
        // A zero-decimal currency takes whole units, not pennies.
        expect(inputs[1].attributes('step')).toBe('1');
    });

    it('edits one currency without disturbing the others or the rest of the payload', async () => {
        const wrapper = mount(FixedAmountOffForm, {
            props: { modelValue: { amounts: { GBP: 10, JPY: 500 }, min_prices: { GBP: 20 } }, currencies },
        });

        await wrapper.findAll('input')[1].setValue('750');

        expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual({
            amounts: { GBP: 10, JPY: '750' },
            min_prices: { GBP: 20 },
        });
    });
});
