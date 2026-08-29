import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import ShipFulfilmentDialog from './ShipFulfilmentDialog.vue';
import { makeFulfilment } from './fixtures';
import type { CarrierData } from './types';

const carriers: CarrierData[] = [
    { key: 'royal-mail', name: 'Royal Mail', services: { 'tracked-24': 'Tracked 24' } },
    { key: 'dpd', name: 'DPD', services: {} },
];

let wrapper: VueWrapper | null = null;

// Reka's DialogPortal teleports content to document.body.
const body = (): HTMLElement => document.body;

async function mountDialog(showNotify = true): Promise<VueWrapper> {
    wrapper = mount(ShipFulfilmentDialog, {
        props: { open: true, fulfilment: makeFulfilment(), carriers, showNotify },
        attachTo: document.body,
    });
    await nextTick();

    return wrapper;
}

const carrierSelects = (): HTMLSelectElement[] => [...body().querySelectorAll('select')];

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('ShipFulfilmentDialog', () => {
    it('starts with one custom tracking row: no services select, URL input shown', async () => {
        await mountDialog();

        expect(carrierSelects()).toHaveLength(1);
        expect(body().textContent).toContain('orders.ship_tracking_url');
        expect(body().textContent).not.toContain('orders.ship_shipping_method');
        expect(body().textContent).toContain('orders.ship_notify');
    });

    it('shows the carrier services and hides the URL input once a carrier is picked', async () => {
        await mountDialog();

        const select = carrierSelects()[0];
        select.value = 'royal-mail';
        select.dispatchEvent(new Event('change', { bubbles: true }));
        select.dispatchEvent(new Event('input', { bubbles: true }));
        await nextTick();

        expect(body().textContent).toContain('orders.ship_shipping_method');
        expect(body().textContent).toContain('Tracked 24');
        expect(body().textContent).not.toContain('orders.ship_tracking_url_help');
    });

    it('adds and removes tracking rows', async () => {
        await mountDialog();

        const addRow = [...body().querySelectorAll('button')].find((button) =>
            button.textContent?.includes('orders.tracking_add_row'),
        );
        addRow!.click();
        await nextTick();
        expect(carrierSelects()).toHaveLength(2);

        const removeRow = body().querySelector<HTMLButtonElement>('[aria-label="orders.tracking_remove_row"]');
        removeRow!.click();
        await nextTick();
        expect(carrierSelects()).toHaveLength(1);
    });

    it('hides the notify toggle when no notification is configured', async () => {
        await mountDialog(false);

        expect(body().textContent).not.toContain('orders.ship_notify');
    });
});
