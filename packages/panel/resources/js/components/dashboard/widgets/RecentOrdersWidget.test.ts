import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({
    Link: { template: '<a><slot /></a>' },
}));

import RecentOrdersWidget from './RecentOrdersWidget.vue';

const order = {
    id: 1,
    reference: 'DEMO-PAID-SHIPPED',
    customer: 'Ann Smith',
    status: 'open',
    status_label: 'Open',
    placed_at: '2026-01-01T00:00:00Z',
    placed_at_human: '1 day ago',
    total: '£10.00',
};

describe('RecentOrdersWidget', () => {
    it('links a row to its order page', () => {
        const wrapper = mount(RecentOrdersWidget, {
            props: { data: { orders: [{ ...order, show_url: '/panel/orders/1' }] } },
        });

        const link = wrapper.find('a[href="/panel/orders/1"]');

        expect(link.exists()).toBe(true);
        expect(link.text()).toContain('DEMO-PAID-SHIPPED');
    });

    it('renders a plain row without a URL', () => {
        const wrapper = mount(RecentOrdersWidget, {
            props: { data: { orders: [{ ...order, show_url: null }] } },
        });

        expect(wrapper.find('a').exists()).toBe(false);
        expect(wrapper.text()).toContain('DEMO-PAID-SHIPPED');
    });
});
