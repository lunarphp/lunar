import { afterEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { useEditDraft, type EditDraftForm } from '../composables/useEditDraft';
import AssociationsCard, { type AssociationProduct } from './AssociationsCard.vue';
import ProductPickerDialog from './ProductPickerDialog.vue';

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: { patch: vi.fn(), post: vi.fn(), delete: vi.fn(), get: vi.fn().mockResolvedValue({ data: [] }) },
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: vi.fn(), on: vi.fn(() => () => {}) },
    usePage: () => ({ props: {} }),
}));

const product = (id: number, name: string): AssociationProduct => ({
    id, name, price: null, variants_count: 1, thumbnail: null, status: 'published',
});

function mountCard(initial: Record<string, number[]>) {
    let form: EditDraftForm<Record<string, unknown>> | undefined;

    const Page = defineComponent({
        setup() {
            form = useEditDraft({ initial, draft: null, urls: { draft: '/p/1/draft', commit: '/p/1/draft/commit' } });

            return () => h(AssociationsCard, {
                types: [{ type: 'cross-sell', label: 'Cross Sell' }, { type: 'up-sell', label: 'Up Sell' }],
                products: { 2: product(2, 'Hat'), 3: product(3, 'Scarf') },
                productId: 1,
                searchUrl: '/search',
            });
        },
    });

    const wrapper = mount(Page, { attachTo: document.body });

    return { wrapper, form: form! };
}

async function expandCrossSell(wrapper: ReturnType<typeof mount>): Promise<void> {
    await wrapper.findAll('button[aria-expanded]')[0].trigger('click');
}

describe('AssociationsCard', () => {
    afterEach(() => {
        document.body.innerHTML = '';
        vi.clearAllMocks();
    });

    it('lists each type\'s drafted ids in order with their display data', async () => {
        const { wrapper } = mountCard({ 'association:cross-sell': [3, 2], 'association:up-sell': [] });

        await expandCrossSell(wrapper);

        const names = wrapper.findAll('[draggable="true"]').map((row) => row.text());
        expect(names[0]).toContain('Scarf');
        expect(names[1]).toContain('Hat');
    });

    it('unlinks into the draft rather than saving', async () => {
        const { wrapper, form } = mountCard({ 'association:cross-sell': [2, 3], 'association:up-sell': [] });

        await expandCrossSell(wrapper);
        await wrapper.find('button[aria-label="products.assoc_remove"]').trigger('click');

        expect(form.values['association:cross-sell']).toEqual([3]);
        expect(form.dirtyKeys.value).toEqual(['association:cross-sell']);
    });

    it('appends picked products to the type being linked and shows them at once', async () => {
        const { wrapper, form } = mountCard({ 'association:cross-sell': [2], 'association:up-sell': [] });

        // Link on the up-sell row opens the picker for that type.
        await wrapper.findAll('button').filter((button) => button.text() === 'products.assoc_add')[1].trigger('click');

        wrapper.findComponent(ProductPickerDialog).vm.$emit('add', [4, 2, 1], [
            { id: 4, name: 'Gloves', sku: null, thumbnail: null, brand: null, status: 'draft' },
        ]);
        await nextTick();

        // The page's own id is never linked; up-sell gains 4 and 2, cross-sell is untouched.
        expect(form.values['association:up-sell']).toEqual([4, 2]);
        expect(form.values['association:cross-sell']).toEqual([2]);
        expect(wrapper.text()).toContain('Gloves');
    });

    it('shows a commit error for one of the type\'s ids under its row', async () => {
        const { wrapper, form } = mountCard({ 'association:cross-sell': [2], 'association:up-sell': [] });

        form.errors.value = { 'association:cross-sell.0': 'The selected product is invalid.' };
        await nextTick();

        expect(wrapper.find('[data-testid="association-error"]').text()).toBe('The selected product is invalid.');
    });
});
