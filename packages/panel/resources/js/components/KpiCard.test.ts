import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import KpiCard from './KpiCard.vue';

describe('KpiCard', () => {
    it('renders a static div without hover affordances by default', () => {
        const wrapper = mount(KpiCard, { props: { label: 'Total', value: 40 } });

        expect(wrapper.find('button').exists()).toBe(false);
        expect(wrapper.find('div').classes().join(' ')).not.toContain('hover:');
    });

    it('renders a clickable button when a click handler is bound', async () => {
        const onClick = vi.fn();
        const wrapper = mount(KpiCard, { props: { label: 'Total', value: 40 }, attrs: { onClick } });

        const button = wrapper.find('button');
        expect(button.exists()).toBe(true);

        await button.trigger('click');
        expect(onClick).toHaveBeenCalled();
    });
});
