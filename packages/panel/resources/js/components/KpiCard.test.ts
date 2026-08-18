import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import KpiCard from './KpiCard.vue';

describe('KpiCard', () => {
    it('renders a static div without hover affordances by default', () => {
        const wrapper = mount(KpiCard, { props: { label: 'Total', value: 40 } });

        expect(wrapper.find('button').exists()).toBe(false);
        expect(wrapper.find('div').classes().join(' ')).not.toContain('hover:');
    });

    it('exposes the exact value as a tooltip when abbreviated', () => {
        const wrapper = mount(KpiCard, { props: { label: 'Revenue', value: '£1.4M', valueTitle: '£1,398,635.13' } });

        const value = wrapper.find('[title]');
        expect(value.text()).toBe('£1.4M');
        expect(value.attributes('title')).toBe('£1,398,635.13');
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
