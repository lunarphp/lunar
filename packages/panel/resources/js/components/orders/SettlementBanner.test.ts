import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import SettlementBanner from './SettlementBanner.vue';
import type { SettlementData } from './types';

const settlement = (overrides: Partial<SettlementData> = {}): SettlementData => ({
    status: 'balanced',
    captured: '£60.00',
    refunded: null,
    total: '£100.00',
    variance: null,
    varianceMajor: 0,
    ...overrides,
});

describe('SettlementBanner', () => {
    it('renders nothing when balanced', () => {
        const wrapper = mount(SettlementBanner, {
            props: { settlement: settlement(), canCapture: true, canRefund: true },
        });

        expect(wrapper.find('[data-testid="settlement-banner"]').exists()).toBe(false);
    });

    it('shows the outstanding copy with a take-payment action', async () => {
        const wrapper = mount(SettlementBanner, {
            props: {
                settlement: settlement({ status: 'outstanding', variance: '£40.00' }),
                canCapture: true,
                canRefund: false,
            },
        });

        expect(wrapper.find('[data-testid="settlement-banner"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('orders.settlement_outstanding');
        expect(wrapper.text()).toContain('orders.settlement_take_payment');

        await wrapper.find('button').trigger('click');
        expect(wrapper.emitted('capture')).toHaveLength(1);
        expect(wrapper.emitted('refund')).toBeUndefined();
    });

    it('shows the refund-due copy with a refund action', async () => {
        const wrapper = mount(SettlementBanner, {
            props: {
                settlement: settlement({ status: 'refund_due', variance: '£20.00' }),
                canCapture: false,
                canRefund: true,
            },
        });

        expect(wrapper.text()).toContain('orders.settlement_refund_due');
        expect(wrapper.text()).toContain('orders.action_refund');

        await wrapper.find('button').trigger('click');
        expect(wrapper.emitted('refund')).toHaveLength(1);
    });

    it('hides the action button when the corresponding permission is absent', () => {
        const wrapper = mount(SettlementBanner, {
            props: {
                settlement: settlement({ status: 'outstanding', variance: '£40.00' }),
                canCapture: false,
                canRefund: false,
            },
        });

        expect(wrapper.find('button').exists()).toBe(false);
    });
});
