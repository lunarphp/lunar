import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import PercentageOffForm from './PercentageOffForm.vue';

const currencies = [{ id: 1, code: 'GBP', decimal_places: 2, default: true }];

describe('PercentageOffForm', () => {
    it('merges its key into the payload rather than replacing it', async () => {
        const wrapper = mount(PercentageOffForm, {
            props: { modelValue: { percentage: 10, min_prices: { GBP: 50 } }, currencies },
        });

        await wrapper.find('input').setValue('25');

        // min_prices belongs to the shared conditions block; the type form must
        // not drop it on the way out.
        expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual({
            percentage: '25',
            min_prices: { GBP: 50 },
        });
    });

    it('surfaces the server error for its own field', () => {
        const wrapper = mount(PercentageOffForm, {
            props: { modelValue: {}, currencies, errors: { 'data.percentage': 'Too high.' } },
        });

        expect(wrapper.text()).toContain('Too high.');
    });
});
