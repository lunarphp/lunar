import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

vi.mock('@inertiajs/vue3', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/vue3')>()),
    router: { on: vi.fn(() => () => {}), delete: vi.fn(), reload: vi.fn() },
    usePage: () => ({ props: {} }),
}));

import Edit from './Edit.vue';

let wrapper: VueWrapper | null = null;

function mountEdit(zoneType = 'country'): VueWrapper {
    wrapper = mount(Edit, {
        props: {
            taxZone: { id: 1, name: 'UK', zone_type: zoneType, active: true, default: false },
            coverage: { countries: [], states: [], postcodes: [{ country_id: 7, postcode: 'SW1A *' }], customerGroups: [] },
            rates: [{ id: null, name: '', priority: 1, amounts: { 3: -5 } }],
            taxClasses: [{ id: 3, name: 'Standard' }],
            countries: [{ id: 7, name: 'United Kingdom', iso2: 'GB' }],
            states: [],
            customerGroups: [],
            urls: { update: '/panel/settings/tax-zones/1', destroy: '/panel/settings/tax-zones/1', index: '/panel/settings/tax-zones' },
        },
        global: {
            stubs: {
                SettingsShell: { template: '<div><slot name="actions" /><slot /></div>' },
                ConfirmDialog: true,
                Tooltip: { template: '<div><slot /></div>' },
            },
        },
    });

    return wrapper;
}

// The page's Inertia form, reached the same way the template binds it.
const formOf = (page: VueWrapper) => (page.vm as unknown as { form: { setError: (errors: Record<string, string>) => void } }).form;

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
});

describe('settings/tax-zones/Edit', () => {
    it('shows each rate error next to its field', async () => {
        const page = mountEdit();

        formOf(page).setError({
            'rates.0.name': 'The rate name field is required.',
            'rates.0.priority': 'The rate priority field must be between 1 and 255.',
            'rates.0.amounts.3': 'The percentage field must be between 0 and 100.',
        });
        await nextTick();

        const text = page.text();
        expect(text).toContain('The rate name field is required.');
        expect(text).toContain('The rate priority field must be between 1 and 255.');
        expect(text).toContain('The percentage field must be between 0 and 100.');
    });

    it('shows coverage list errors', async () => {
        const page = mountEdit();

        formOf(page).setError({ 'countries.0': 'The selected country is invalid.' });
        await nextTick();

        expect(page.text()).toContain('The selected country is invalid.');
    });

    it('shows postcode errors on their row', async () => {
        const page = mountEdit('postcode');

        formOf(page).setError({ 'postcodes.0.postcode': 'The postcode field must not be greater than 20 characters.' });
        await nextTick();

        expect(page.text()).toContain('The postcode field must not be greater than 20 characters.');
    });
});
