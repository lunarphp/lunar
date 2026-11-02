import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import OrderAddressDetails, { type OrderAddressDetailsData } from './OrderAddressDetails.vue';

const address = (overrides: Partial<OrderAddressDetailsData> = {}): OrderAddressDetailsData => ({
    first_name: 'Sam',
    last_name: 'Fitter',
    company_name: 'Fitter & Sons',
    line_one: '1 Boiler Lane',
    line_two: null,
    line_three: null,
    city: 'Manchester',
    state: null,
    postcode: 'M1 2AB',
    country: 'United Kingdom',
    contact_phone: '07700 900123',
    delivery_instructions: 'Leave with reception <b>please</b>',
    ...overrides,
});

describe('OrderAddressDetails', () => {
    it('renders the address lines and phone', () => {
        const wrapper = mount(OrderAddressDetails, { props: { address: address() } });

        expect(wrapper.text()).toContain('Sam Fitter');
        expect(wrapper.text()).toContain('Manchester M1 2AB');
        expect(wrapper.find('a[href="tel:07700 900123"]').exists()).toBe(true);
    });

    it('shows delivery instructions on the shipping address, as text', () => {
        const wrapper = mount(OrderAddressDetails, { props: { address: address(), showInstructions: true } });

        const instructions = wrapper.get('[data-testid="delivery-instructions"]');

        expect(instructions.text()).toBe('Leave with reception <b>please</b>');
        expect(instructions.find('b').exists()).toBe(false);
    });

    it('leaves instructions off unless asked, and when there are none', () => {
        expect(mount(OrderAddressDetails, { props: { address: address() } }).find('[data-testid="delivery-instructions"]').exists()).toBe(false);

        expect(
            mount(OrderAddressDetails, { props: { address: address({ delivery_instructions: null }), showInstructions: true } })
                .find('[data-testid="delivery-instructions"]')
                .exists(),
        ).toBe(false);
    });
});
