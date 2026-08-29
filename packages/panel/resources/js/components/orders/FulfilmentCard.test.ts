import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import FulfilmentCard from './FulfilmentCard.vue';
import { makeFulfilment } from './fixtures';
import type { FulfilmentData } from './types';

let wrapper: VueWrapper | null = null;

const mountCard = (fulfilment: FulfilmentData): VueWrapper => {
    wrapper = mount(FulfilmentCard, { props: { fulfilment } });

    return wrapper;
};

const buttonByText = (text: string) =>
    wrapper!.findAll('button').find((button) => button.text().includes(text));

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
});

describe('FulfilmentCard', () => {
    it('shows the chrome: reference, state, method, and item count', () => {
        mountCard(makeFulfilment());

        expect(wrapper!.text()).toContain('FUL-5');
        expect(wrapper!.text()).toContain('Pending');
        expect(wrapper!.text()).toContain('Shipping');
        expect(wrapper!.text()).toContain('Warehouse');
        // 2 units allocated, not yet handed over.
        expect(wrapper!.text()).toContain('orders.item_count');
    });

    it('offers "Mark shipped" as the primary verb on a tracking method', async () => {
        mountCard(makeFulfilment());

        const primary = buttonByText('orders.mark_shipped');
        expect(primary).toBeDefined();

        await primary!.trigger('click');
        expect(wrapper!.emitted('action')![0]).toEqual([{ type: 'ship' }]);
    });

    it('offers the per-method fulfil label on a non-tracking method', async () => {
        mountCard(
            makeFulfilment({
                method: 'collection',
                method_label: 'Collection',
                fulfil_label: 'Mark collected',
                transitions: [
                    { state: 'ready-for-collection', label: 'Ready for collection', via: 'transition', notify: true },
                    { state: 'collected', label: 'Collected', via: 'fulfil', notify: false },
                ],
            }),
        );

        const primary = buttonByText('Mark collected');
        expect(primary).toBeDefined();

        await primary!.trigger('click');
        expect(wrapper!.emitted('action')![0]).toEqual([{ type: 'fulfil' }]);
    });

    it('shows the handed-over line and no primary verb once handed over', () => {
        mountCard(
            makeFulfilment({
                state: 'shipped',
                state_label: 'Shipped',
                state_category: 'fulfilled',
                shipped_at: '2026-08-01T10:00:00Z',
                transitions: [{ state: 'returned', label: 'Returned', via: 'return', notify: true }],
            }),
        );

        // Locale-agnostic: the handed-over label plus the formatted year.
        expect(wrapper!.text()).toMatch(/Shipped .*2026/);
        expect(buttonByText('orders.mark_shipped')).toBeUndefined();
        // The return step still lives in the update-status menu.
        expect(buttonByText('orders.update_status')).toBeDefined();
    });

    it('leads the subline with the checkout delivery method when present', () => {
        mountCard(makeFulfilment({ delivery_method: 'Royal Mail Tracked 48' }));

        expect(wrapper!.text()).toContain('Royal Mail Tracked 48');
    });

    it('flags a hold with its reason', () => {
        mountCard(makeFulfilment({ on_hold: true, hold_reason_label: 'Out of stock', hold_note: 'Restock Friday' }));

        expect(wrapper!.text()).toContain('orders.on_hold');
        expect(wrapper!.text()).toContain('Out of stock');
    });

    it('hides the more-actions menu when nothing is allowed', () => {
        mountCard(makeFulfilment());

        expect(wrapper!.find('[aria-label="common.more_actions"]').exists()).toBe(false);
    });

    it('shows the more-actions menu when a gate opens', () => {
        mountCard(makeFulfilment({ can: { ...makeFulfilment().can, split: true } }));

        expect(wrapper!.find('[aria-label="common.more_actions"]').exists()).toBe(true);
    });

    it('lists tracking rows and emits their removal', async () => {
        mountCard(
            makeFulfilment({
                trackings: [
                    {
                        id: 9,
                        carrier: 'royal-mail',
                        carrier_name: 'Royal Mail',
                        shipping_method: 'Tracked 24',
                        tracking_number: 'RM123',
                        url: 'https://track.example/RM123',
                        destroy_url: '/f/5/trackings/9',
                    },
                ],
            }),
        );

        expect(wrapper!.text()).toContain('Royal Mail');
        expect(wrapper!.find('a[href="https://track.example/RM123"]').exists()).toBe(true);

        await wrapper!.find('[aria-label="orders.tracking_remove_row"]').trigger('click');
        const emitted = wrapper!.emitted('action')![0][0] as { type: string; tracking: { id: number } };
        expect(emitted.type).toBe('remove-tracking');
        expect(emitted.tracking.id).toBe(9);
    });
});
