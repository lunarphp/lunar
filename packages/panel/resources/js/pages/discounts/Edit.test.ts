import { afterAll, afterEach, beforeAll, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

vi.mock('@inertiajs/vue3', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/vue3')>()),
    Link: { template: '<a><slot /></a>' },
    router: { on: vi.fn(() => () => {}), delete: vi.fn(), reload: vi.fn() },
    usePage: () => ({ props: {} }),
}));

vi.mock('../../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../../lib/http')>()),
    http: { get: vi.fn().mockResolvedValue({ data: [] }), patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

import Edit from './Edit.vue';
import { http } from '../../lib/http';
import TargetChipList from '../../components/TargetChipList.vue';
import TargetPickerDialog from '../../components/TargetPickerDialog.vue';

let wrapper: VueWrapper | null = null;

const emptyLimitation = { products: [], variants: [], collections: [], brands: [], customers: [] };

function mountEdit(discount: Record<string, unknown> = {}): VueWrapper {
    wrapper = mount(Edit, {
        props: {
            discount: {
                id: 1,
                name: 'Spring',
                handle: 'spring',
                redemption: 'automatic',
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
                ...discount,
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
            coupons: { data: [], current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null, from: null, to: null, total: 0 },
            couponSearch: '',
            couponGenerations: [],
            couponQueueThreshold: 5000,
            urls: {
                index: '/panel/discounts',
                activityLog: '/panel/discounts/1/activity',
                update: '/panel/discounts/1',
                destroy: '/panel/discounts/1',
                draft: '/panel/discounts/1/draft',
                draftCommit: '/panel/discounts/1/draft/commit',
                targetSearch: '/panel/discounts/1/targets/search',
                couponStore: '/panel/discounts/1/coupons',
                couponGenerate: '/panel/discounts/1/coupons/generate',
                couponExport: '/panel/discounts/1/coupons/export',
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

    describe('in a timezone ahead of UTC', () => {
        // London is on BST (UTC+1) in June to August 2027.
        beforeAll(() => {
            vi.stubEnv('TZ', 'Europe/London');
        });

        afterAll(() => {
            vi.unstubAllEnvs();
            vi.useRealTimers();
        });

        it('shows the stored schedule in the staff member\'s local time', () => {
            const page = mountEdit({ starts_at: '2027-06-15T08:00:00.000000Z', ends_at: '2027-08-31T22:59:00.000000Z' });

            expect((page.find('#discount-starts-at').element as HTMLInputElement).value).toBe('2027-06-15T09:00');
            expect((page.find('#discount-ends-at').element as HTMLInputElement).value).toBe('2027-08-31T23:59');
        });

        it('drafts the local time the staff member enters as the matching instant', async () => {
            vi.useFakeTimers();
            vi.mocked(http.patch).mockResolvedValue({ data: {}, updated_at: null, version: 1 });

            const page = mountEdit({ starts_at: '2027-06-15T08:00:00.000000Z' });

            await page.find('#discount-ends-at').setValue('2027-08-31T23:59');
            await vi.advanceTimersByTimeAsync(1000);

            expect(http.patch).toHaveBeenCalledWith(
                '/panel/discounts/1/draft',
                expect.objectContaining({ data: { ends_at: '2027-08-31T22:59:00.000Z' } }),
            );

            vi.useRealTimers();
        });
    });
});
