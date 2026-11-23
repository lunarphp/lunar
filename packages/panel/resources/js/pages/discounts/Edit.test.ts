import { afterAll, afterEach, beforeAll, describe, expect, it, vi } from 'vitest';
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
import { http } from '../../lib/http';
import TargetChipList from '../../components/TargetChipList.vue';
import TargetPickerDialog from '../../components/TargetPickerDialog.vue';

let wrapper: VueWrapper | null = null;

const emptyLimitation = { products: [], variants: [], collections: [], brands: [], customers: [] };

const emptyExclusion = { products: [], variants: [], collections: [], brands: [] };
const emptyProductBucket = { products: [], variants: [], collections: [] };

const percentageOff = {
    type: { class: 'PercentageOff', label: 'Percentage off', component: 'PercentageOffForm', buckets: ['limitation', 'exclusion'], required_buckets: [] },
    targets: {
        'target:limitation': { ...emptyLimitation },
        'target:exclusion': { ...emptyExclusion },
    },
};

const buyXGetY = {
    type: { class: 'BuyXGetY', label: 'Buy X get Y', component: 'BuyXGetYForm', buckets: ['condition', 'reward'], required_buckets: ['condition', 'reward'] },
    targets: {
        // Eligible customers live under limitation for every type.
        'target:limitation': { ...emptyLimitation },
        'target:condition': { ...emptyProductBucket },
        'target:reward': { ...emptyProductBucket },
    },
};

function mountEdit(discount: Record<string, unknown> = {}, discountType: typeof percentageOff | typeof buyXGetY = percentageOff): VueWrapper {
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
                ...discount,
            },
            type: discountType.type,
            typeRegistered: true,
            draft: null,
            currencies: [],
            targets: JSON.parse(JSON.stringify(discountType.targets)),
            targetChips: JSON.parse(JSON.stringify(discountType.targets)),
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
                BuyXGetYForm: true,
                TargetPickerDialog: true,
            },
        },
    });

    return wrapper;
}

const customerList = (page: VueWrapper) =>
    page.findAllComponents(TargetChipList).find((list) => list.props('label') === 'discounts.bucket_customers')!;

const productList = (page: VueWrapper, bucket: string) =>
    page.findAllComponents(TargetChipList).find((list) => list.props('label') === `discounts.bucket_${bucket}`)!;

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

    describe('empty target blocks', () => {
        it('say an empty percentage off block applies to everything, with no warning', () => {
            const page = mountEdit({}, percentageOff);

            expect(productList(page, 'limitation').text()).toContain('discounts.target_empty');
            expect(productList(page, 'limitation').text()).not.toContain('discounts.target_empty_required');
            expect(productList(page, 'exclusion').text()).not.toContain('discounts.target_empty_required');
            expect(page.text()).toContain('discounts.section_targets_description');
            expect(page.find('[data-testid="missing-targets-warning"]').exists()).toBe(false);
        });

        it('say an empty buy x get y block leaves the discount doing nothing, and warn', () => {
            const page = mountEdit({}, buyXGetY);

            expect(productList(page, 'condition').text()).toContain('discounts.target_empty_required');
            expect(productList(page, 'reward').text()).toContain('discounts.target_empty_required');
            expect(page.text()).toContain('discounts.section_targets_required_description');
            expect(page.text()).not.toContain('discounts.section_targets_description');
            expect(page.find('[data-testid="missing-targets-warning"]').exists()).toBe(true);
        });

        it('clears the buy x get y warning once both blocks have a product', async () => {
            const page = mountEdit({}, buyXGetY);

            productList(page, 'condition').vm.$emit('add');
            await nextTick();
            pickerFor(page, ['products', 'variants', 'collections']).vm.$emit('add', [{ kind: 'products', id: 1, label: 'Widget', hint: null }]);
            await nextTick();

            // One block filled is still a discount that does nothing.
            expect(page.find('[data-testid="missing-targets-warning"]').exists()).toBe(true);

            productList(page, 'reward').vm.$emit('add');
            await nextTick();
            pickerFor(page, ['products', 'variants', 'collections']).vm.$emit('add', [{ kind: 'products', id: 2, label: 'Gadget', hint: null }]);
            await nextTick();

            expect(page.find('[data-testid="missing-targets-warning"]').exists()).toBe(false);
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
