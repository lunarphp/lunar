import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: { template: '<a><slot /></a>' },
    router: { on: vi.fn(() => () => {}), delete: vi.fn(), reload: vi.fn() },
    usePage: () => ({ props: {} }),
}));

vi.mock('../../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../../lib/http')>()),
    http: { get: vi.fn().mockResolvedValue({ data: [] }), patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

import Edit from './Edit.vue';
import TargetChipList from '../../components/TargetChipList.vue';
import TargetPickerDialog from '../../components/TargetPickerDialog.vue';

let wrapper: VueWrapper | null = null;

const emptyLimitation = { products: [], variants: [], collections: [], brands: [], customers: [] };

function mountEdit(): VueWrapper {
    wrapper = mount(Edit, {
        props: {
            discount: {
                id: 1,
                name: 'Spring',
                handle: 'spring',
                coupon: null,
                type: 'percentage',
                status: 'active',
                status_label: 'Active',
                starts_at: null,
                ends_at: null,
                priority: 1,
                stop: false,
                uses: 0,
                max_uses: null,
                max_uses_per_user: null,
                data: {},
                created_at: '2026-01-01',
                updated_at: '2026-01-01',
            },
            type: { class: 'PercentageOff', label: 'Percentage off', component: 'PercentageOffForm', buckets: ['limitation', 'exclusion'] },
            typeRegistered: true,
            draft: null,
            currencies: [],
            targets: {
                'target:limitation': { ...emptyLimitation },
                'target:exclusion': { products: [], variants: [], collections: [], brands: [] },
            },
            targetChips: {
                'target:limitation': { ...emptyLimitation },
                'target:exclusion': { products: [], variants: [], collections: [], brands: [] },
            },
            availability: { channels: [], customer_groups: [] },
            availabilityValues: {},
            activities: [],
            urls: {
                index: '/panel/discounts',
                activityLog: '/panel/discounts/1/activity',
                update: '/panel/discounts/1',
                destroy: '/panel/discounts/1',
                draft: '/panel/discounts/1/draft',
                draftCommit: '/panel/discounts/1/draft/commit',
                targetSearch: '/panel/discounts/1/targets/search',
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
                AvailabilityCard: true,
                DiscountConditions: true,
                PercentageOffForm: true,
                TargetPickerDialog: true,
            },
        },
    });

    return wrapper;
}

const customerList = (page: VueWrapper) =>
    page.findAllComponents(TargetChipList).find((list) => list.props('label') === 'discounts.bucket_customers')!;

const pickerFor = (page: VueWrapper, kinds: string[]) =>
    page.findAllComponents(TargetPickerDialog).find((picker) => JSON.stringify(picker.props('kinds')) === JSON.stringify(kinds))!;

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
});

describe('discounts/Edit', () => {
    it('adds customers picked in the eligible customers card', async () => {
        const page = mountEdit();

        customerList(page).vm.$emit('add');
        await nextTick();

        const picker = pickerFor(page, ['customers']);
        expect(picker.props('open')).toBe(true);

        picker.vm.$emit('add', [{ kind: 'customers', id: 42, label: 'Ada Kshlerin', hint: null }]);
        await nextTick();

        expect(customerList(page).props('chips')).toEqual({
            customers: [{ id: 42, label: 'Ada Kshlerin', hint: null }],
        });
    });
});
