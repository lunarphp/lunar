import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({
    Deferred: { template: '<div />' },
    Link: { template: '<a><slot /></a>' },
    router: { on: vi.fn(() => () => {}), delete: vi.fn(), post: vi.fn(), reload: vi.fn() },
    useForm: (data: Record<string, unknown>) => ({ ...data, errors: {}, processing: false, reset: vi.fn(), post: vi.fn() }),
    usePage: () => ({ props: {} }),
}));

import Edit from './Edit.vue';

let wrapper: VueWrapper | null = null;

const order = {
    id: 1,
    reference: 'DEMO-PAID-SHIPPED',
    status: 'open',
    status_label: 'Open',
    placed_at: '2026-01-01T00:00:00Z',
    total: '£10.00',
};

function mountEdit(orders: Array<typeof order & { show_url: string | null }>): VueWrapper {
    wrapper = mount(Edit, {
        props: {
            customer: {
                id: 1,
                title: null,
                first_name: 'Ann',
                last_name: 'Smith',
                company_name: null,
                tax_identifier: null,
                account_ref: null,
                admin_notes: null,
                created_at: '2026-01-01',
                customer_groups: [],
            },
            customerGroups: [],
            countries: [],
            addresses: [],
            users: [],
            activities: [],
            orders,
            stats: { orders: orders.length, totalSpend: null, avgOrder: null, latestOrderAt: null },
            draft: null,
            urls: {
                index: '/panel/customers',
                activityLog: '/panel/activity-log',
                update: '/panel/customers/1',
                destroy: '/panel/customers/1',
                addressesStore: '/panel/customers/1/addresses',
                usersStore: '/panel/customers/1/users',
                draft: '/panel/customers/1/draft',
                draftCommit: '/panel/customers/1/draft/commit',
            },
        },
        global: {
            stubs: {
                PanelLayout: { template: '<div><slot /></div>' },
                Breadcrumbs: true,
                DraftActions: true,
                DraftRestoredBanner: true,
                DraftConflictDialog: true,
                ConfirmDialog: true,
                PageZone: true,
                PageHeader: true,
                ActivityTimeline: true,
                StickySidebar: { template: '<div><slot /></div>' },
                Tabs: { template: '<div><slot name="orders" /></div>' },
            },
        },
    });

    return wrapper;
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
});

describe('customers/Edit order history', () => {
    it('links an order row to its order page', () => {
        const link = mountEdit([{ ...order, show_url: '/panel/orders/1' }]).find('a[href="/panel/orders/1"]');

        expect(link.exists()).toBe(true);
        expect(link.text()).toContain('DEMO-PAID-SHIPPED');
    });

    it('renders a plain order row without a URL', () => {
        const page = mountEdit([{ ...order, show_url: null }]);

        expect(page.text()).toContain('DEMO-PAID-SHIPPED');
        expect(page.find('a[href^="/panel/orders"]').exists()).toBe(false);
    });
});
