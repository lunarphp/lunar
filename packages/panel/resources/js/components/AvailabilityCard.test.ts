import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import AvailabilityCard, { type AvailabilityValue } from './AvailabilityCard.vue';

const future = new Date(Date.now() + 86_400_000).toISOString();

const mountCard = (values: Record<string, AvailabilityValue>) =>
    mount(AvailabilityCard, {
        props: {
            channels: [
                { id: 1, name: 'Webstore', field: 'channel:1' },
                { id: 2, name: 'POS', field: 'channel:2' },
            ],
            customerGroups: [{ id: 5, name: 'Retail', field: 'customer_group:5' }],
            values: reactive(values),
        },
        attachTo: document.body,
        global: {
            // Reka tooltips need the layout's TooltipProvider; render through.
            stubs: { Tooltip: { template: '<span><slot /></span>' } },
        },
    });

const defaults = (): Record<string, AvailabilityValue> => ({
    'channel:1': { enabled: true, starts_at: null, ends_at: null },
    'channel:2': { enabled: false, starts_at: null, ends_at: null },
    'customer_group:5': { enabled: true, visible: true, starts_at: null, ends_at: null },
});

describe('AvailabilityCard', () => {
    it('summarises collapsed sections', () => {
        const wrapper = mountCard(defaults());

        expect(wrapper.text()).toContain('availability.summary_some');
        expect(wrapper.text()).toContain('availability.summary_all');
    });

    it('toggles a channel power pill onto the draft values', async () => {
        const values = defaults();
        const wrapper = mountCard(values);

        await wrapper.find('button[aria-expanded]').trigger('click');

        const powerButtons = wrapper.findAll('button[aria-label="availability.disabled"], button[aria-label="availability.enabled"]');
        await powerButtons[1].trigger('click');

        expect(values['channel:2'].enabled).toBe(true);
    });

    it('locks the power pill while a row is scheduled', async () => {
        const values = defaults();
        values['channel:1'] = { enabled: true, starts_at: future, ends_at: null };
        const wrapper = mountCard(values);

        await wrapper.find('button[aria-expanded]').trigger('click');

        const locked = wrapper.find('button[aria-label="availability.scheduled_locked"]');

        expect(locked.attributes('disabled')).toBeDefined();
        expect(wrapper.text()).toContain('availability.turns_on');
    });

    it('clears a schedule from the calendar pill', async () => {
        const values = defaults();
        values['channel:1'] = { enabled: true, starts_at: future, ends_at: null };
        const wrapper = mountCard(values);

        await wrapper.find('button[aria-expanded]').trigger('click');

        const calendars = wrapper.findAll('button').filter((button) =>
            button.attributes('aria-label')?.includes('availability.scheduled_tip'));
        await calendars[0].trigger('click');

        expect(values['channel:1']).toEqual({ enabled: false, starts_at: null, ends_at: null });
    });

    it('toggles the customer-group visible flag through the eye control', async () => {
        const values = defaults();
        const wrapper = mountCard(values);

        const groupHeaders = wrapper.findAll('button[aria-expanded]');
        await groupHeaders[1].trigger('click');

        await wrapper.find('button[aria-label="availability.make_hidden"]').trigger('click');

        expect(values['customer_group:5'].visible).toBe(false);
        expect(wrapper.text()).toContain('availability.hidden');
    });

    it('hides the purchasable controls unless the card opts in', async () => {
        const wrapper = mountCard(defaults());

        const groupHeaders = wrapper.findAll('button[aria-expanded]');
        await groupHeaders[1].trigger('click');

        expect(wrapper.find('button[aria-label="availability.make_view_only"]').exists()).toBe(false);
    });

    it('toggles the customer-group purchasable flag when opted in', async () => {
        const values = defaults();
        values['customer_group:5'].purchasable = true;

        const wrapper = mount(AvailabilityCard, {
            props: {
                channels: [],
                customerGroups: [{ id: 5, name: 'Retail', field: 'customer_group:5' }],
                values: reactive(values),
                withPurchasable: true,
            },
            attachTo: document.body,
            global: {
                stubs: { Tooltip: { template: '<span><slot /></span>' } },
            },
        });

        const groupHeaders = wrapper.findAll('button[aria-expanded]');
        await groupHeaders[1].trigger('click');

        await wrapper.find('button[aria-label="availability.make_view_only"]').trigger('click');

        expect(values['customer_group:5'].purchasable).toBe(false);
        expect(wrapper.text()).toContain('availability.view_only');
    });
});
