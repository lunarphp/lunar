import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import DiscountTypeCard from './DiscountTypeCard.vue';

describe('DiscountTypeCard', () => {
    it('reports its selected state to assistive technology', () => {
        const unselected = mount(DiscountTypeCard, { props: { label: 'Percentage off', selected: false } });
        const selected = mount(DiscountTypeCard, { props: { label: 'Percentage off', selected: true } });

        expect(unselected.attributes('aria-pressed')).toBe('false');
        expect(selected.attributes('aria-pressed')).toBe('true');
    });

    it('emits select when clicked', async () => {
        const wrapper = mount(DiscountTypeCard, { props: { label: 'Buy X get Y', selected: false } });

        await wrapper.trigger('click');

        expect(wrapper.emitted('select')).toHaveLength(1);
    });

    it('renders an optional description', () => {
        const wrapper = mount(DiscountTypeCard, {
            props: { label: 'Fixed amount off', description: 'Takes a set amount off the line.', selected: false },
        });

        expect(wrapper.text()).toContain('Takes a set amount off the line.');
    });
});
