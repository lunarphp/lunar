import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import DiscountConditions from './DiscountConditions.vue';

const currencies = [
    { id: 1, code: 'GBP', decimal_places: 2, default: true },
    { id: 2, code: 'JPY', decimal_places: 0, default: false },
];

describe('DiscountConditions', () => {
    it('renders a minimum spend input per currency', () => {
        const wrapper = mount(DiscountConditions, { props: { modelValue: {}, currencies } });

        expect(wrapper.findAll('input')).toHaveLength(2);
    });

    it('edits min_prices without disturbing the type payload', async () => {
        const wrapper = mount(DiscountConditions, {
            props: { modelValue: { percentage: 15, min_prices: { GBP: 20 } }, currencies },
        });

        await wrapper.findAll('input')[1].setValue('3000');

        expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual({
            percentage: 15,
            min_prices: { GBP: 20, JPY: '3000' },
        });
    });
});
