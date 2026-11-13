import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import ChangeMethodDialog from './ChangeMethodDialog.vue';
import { makeFulfilment } from './fixtures';
import type { FulfilmentMethodTarget } from './types';

let wrapper: VueWrapper | null = null;

const body = (): HTMLElement => document.body;

const pickup: FulfilmentMethodTarget = { key: 'pickup', label: 'Pickup', requires_delivery: false, location_id: 2 };
const shipping: FulfilmentMethodTarget = { key: 'shipping', label: 'Shipping', requires_delivery: true, location_id: null };

async function mountDialog(method: FulfilmentMethodTarget, hasShippingAddress: boolean): Promise<void> {
    wrapper = mount(ChangeMethodDialog, {
        props: {
            open: true,
            fulfilment: makeFulfilment(),
            method,
            locations: [
                { id: 1, name: 'Warehouse' },
                { id: 2, name: 'Dartford' },
            ],
            hasShippingAddress,
            countries: [{ id: 44, name: 'United Kingdom' }],
        },
        attachTo: document.body,
    });
    await nextTick();
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('ChangeMethodDialog', () => {
    it('asks where a pickup is collected, starting at the suggested location', async () => {
        await mountDialog(pickup, true);

        expect(body().textContent).toContain('orders.action_change_method');
        expect(body().querySelector<HTMLSelectElement>('select')?.value).toBe('2');
        expect(body().querySelector('#change-method-address-first-name')).toBeNull();
    });

    it('takes a shipping address when a delivery method has none to send to', async () => {
        await mountDialog(shipping, false);

        expect(body().textContent).toContain('orders.change_method_address_hint');
        expect(body().querySelector('#change-method-address-first-name')).not.toBeNull();
    });

    it('sends a delivery to the existing shipping address', async () => {
        await mountDialog(shipping, true);

        expect(body().textContent).toContain('orders.change_method_ships_to_address');
        expect(body().querySelector('#change-method-address-first-name')).toBeNull();
    });
});
