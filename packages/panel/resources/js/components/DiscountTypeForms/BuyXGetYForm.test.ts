import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import BuyXGetYForm from './BuyXGetYForm.vue';

const currencies = [{ id: 1, code: 'GBP', decimal_places: 2, default: true }];

describe('BuyXGetYForm', () => {
    it('edits a quantity without disturbing the rest of the payload', async () => {
        const wrapper = mount(BuyXGetYForm, {
            props: {
                modelValue: { min_qty: 2, reward_qty: 1, max_reward_qty: null, min_prices: { GBP: 20 } },
                currencies,
            },
        });

        await wrapper.findAll('input[type="number"]')[0].setValue('3');

        expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual({
            min_qty: '3',
            reward_qty: 1,
            max_reward_qty: null,
            min_prices: { GBP: 20 },
        });
    });

    it('flips the automatic-rewards toggle onto the payload', async () => {
        // Toggle takes :on and emits toggle; bound with v-model it renders
        // permanently off and swallows the click.
        const wrapper = mount(BuyXGetYForm, {
            props: {
                modelValue: { min_qty: 2, reward_qty: 1, automatically_add_rewards: false },
                currencies,
            },
        });

        await wrapper.findComponent({ name: 'Toggle' }).vm.$emit('toggle');

        expect(wrapper.emitted('update:modelValue')?.at(-1)?.[0]).toEqual({
            min_qty: 2,
            reward_qty: 1,
            automatically_add_rewards: true,
        });
    });

    it('shows the toggle as on when the payload says so', () => {
        const wrapper = mount(BuyXGetYForm, {
            props: {
                modelValue: { min_qty: 2, reward_qty: 1, automatically_add_rewards: true },
                currencies,
            },
        });

        expect(wrapper.findComponent({ name: 'Toggle' }).props('on')).toBe(true);
    });

    it('renders the three quantity fields', () => {
        const wrapper = mount(BuyXGetYForm, {
            props: { modelValue: { min_qty: 2, reward_qty: 1, max_reward_qty: null }, currencies },
        });

        expect(wrapper.findAll('input[type="number"]')).toHaveLength(3);
    });
});
