import { afterEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount, type VueWrapper } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({
    Link: { template: '<a><slot /></a>' },
    router: { on: vi.fn(() => () => {}), delete: vi.fn(), post: vi.fn(), reload: vi.fn() },
    usePage: () => ({ props: {} }),
}));

vi.mock('../../lib/http', async (importOriginal) => {
    const actual = await importOriginal<typeof import('../../lib/http')>();

    return {
        ...actual,
        http: {
            get: vi.fn().mockResolvedValue({ data: [] }),
            patch: vi.fn(),
            post: vi.fn().mockRejectedValue(new actual.ValidationError({ status: ['The selected status is invalid.'] }, 2)),
            delete: vi.fn(),
        },
    };
});

import Edit from './Edit.vue';

let wrapper: VueWrapper | null = null;

function mountEdit(): VueWrapper {
    wrapper = mount(Edit, {
        props: {
            product: {
                id: 1,
                name: { en: 'Mug' },
                display_name: 'Mug',
                status: 'draft',
                status_label: 'Draft',
                product_type_id: 1,
                product_type_name: 'Stock',
                brand_id: null,
                short_description: {},
                description: {},
                thumbnail: null,
                sku: null,
                variants_count: 1,
                has_order_history: false,
                tags: [],
                created_at: '2026-01-01',
                updated_at: '2026-01-01',
            },
            shape: 'multi',
            attachedOptions: [],
            variants: [],
            variant: null,
            variantAttributeGroups: [],
            currencies: [],
            customerGroups: [],
            taxClasses: [],
            measurements: { length: [], weight: [] },
            draft: { data: { status: 'live' }, updated_at: '2026-01-01', version: 1 },
            languages: [],
            mediaGroups: [],
            attributeGroups: [],
            availability: { channels: [], customer_groups: [] },
            brandOptions: [],
            typeOptions: [{ value: 1, label: 'Stock' }],
            collections: [],
            associationTypes: [],
            associationProducts: {},
            storefrontUrl: null,
            activities: [],
            urls: {
                index: '/panel/products',
                activityLog: '/panel/products/1/activity',
                destroy: '/panel/products/1',
                draft: '/panel/products/1/draft',
                draftCommit: '/panel/products/1/draft/commit',
                collectionsSearch: '',
                productsSearch: '',
                productOptionsSearch: '',
                optionsGenerate: '',
                variantsBulk: '',
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
                AttributeFields: true,
                MediaGroups: true,
                AssociationsCard: true,
                UrlSlugs: true,
                VariantsTable: true,
                ProductOptionsBuilder: true,
                CollectionPicker: true,
                TranslatedInput: true,
                StickySidebar: { template: '<div><slot /></div>' },
            },
        },
    });

    return wrapper;
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
});

describe('products/Edit', () => {
    it('shows a refused status so a failed save is never silent', async () => {
        const page = mountEdit();

        await page.find('form').trigger('submit');
        await flushPromises();

        expect(page.text()).toContain('The selected status is invalid.');
    });
});
