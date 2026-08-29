import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import FulfilmentLineRow from './FulfilmentLineRow.vue';
import { makeLine } from './fixtures';

describe('FulfilmentLineRow', () => {
    it('renders the line identity and allocated quantity', () => {
        const wrapper = mount(FulfilmentLineRow, { props: { line: makeLine() } });

        expect(wrapper.text()).toContain('Blue Widget');
        expect(wrapper.text()).toContain('Blue');
        expect(wrapper.text()).toContain('SKU-1');
        expect(wrapper.text()).toContain('£50.00');
        expect(wrapper.find('[data-testid="line-detail"]').exists()).toBe(false);
    });

    it('expands to the price detail panel, including tax and total', async () => {
        const wrapper = mount(FulfilmentLineRow, {
            props: { line: makeLine({ discount_total: '£5.00', notes: 'Engrave it' }) },
        });

        await wrapper.find('button').trigger('click');

        const detail = wrapper.find('[data-testid="line-detail"]');
        expect(detail.exists()).toBe(true);
        expect(detail.text()).toContain('orders.line_unit_price');
        expect(detail.text()).toContain('orders.line_discount');
        expect(detail.text()).toContain('VAT (20%)');
        expect(detail.text()).toContain('£120.00');
        expect(detail.text()).toContain('Engrave it');

        await wrapper.find('button').trigger('click');
        expect(wrapper.find('[data-testid="line-detail"]').exists()).toBe(false);
    });
});
