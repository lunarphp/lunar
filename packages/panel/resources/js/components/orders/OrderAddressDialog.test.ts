import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';
import OrderAddressDialog, { type OrderAddressData } from './OrderAddressDialog.vue';

const address: OrderAddressData = {
    id: 3,
    type: 'shipping',
    title: null,
    first_name: 'Ada',
    last_name: 'Lovelace',
    company_name: null,
    tax_identifier: null,
    line_one: '1 Wrong Street',
    line_two: null,
    line_three: null,
    city: 'London',
    state: null,
    postcode: 'E1 6AN',
    country_id: 44,
    contact_email: null,
    contact_phone: null,
    delivery_instructions: null,
    update_url: '/panel/orders/1/addresses/3',
};

let wrapper: VueWrapper | null = null;

// Reka's DialogPortal teleports content to document.body.
const body = (): HTMLElement => document.body;

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('OrderAddressDialog', () => {
    it('fills the form from the address and hides the customer-default checkboxes', async () => {
        wrapper = mount(OrderAddressDialog, {
            props: {
                open: true,
                address,
                title: 'orders.edit_shipping_address',
                countries: [{ id: 44, name: 'United Kingdom' }],
            },
            attachTo: document.body,
        });
        await nextTick();

        const firstName = body().querySelector<HTMLInputElement>('#order-address-first-name');
        expect(firstName?.value).toBe('Ada');

        const lineOne = body().querySelector<HTMLInputElement>('#order-address-line-one');
        expect(lineOne?.value).toBe('1 Wrong Street');

        // Order addresses have no customer defaults.
        expect(body().textContent).not.toContain('customers.default_shipping');
        expect(body().textContent).not.toContain('customers.default_billing');

        // The save action is present.
        const save = [...body().querySelectorAll('button')].find((button) =>
            button.textContent?.includes('orders.save_address'),
        );
        expect(save).toBeDefined();
    });
});
