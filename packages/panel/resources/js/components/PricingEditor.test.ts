import { afterEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { useEditDraft, type EditDraftForm } from '../composables/useEditDraft';
import PricingEditor, { type CurrencyOption, type PriceValue } from './PricingEditor.vue';

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: { patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: vi.fn(), on: vi.fn(() => () => {}) },
    usePage: () => ({ props: {} }),
}));

const currencies: CurrencyOption[] = [
    { id: 1, code: 'GBP', decimal_places: 2, default: true },
    { id: 2, code: 'JPY', decimal_places: 0, default: false },
];

const base: PriceValue = { customer_group_id: null, min_quantity: 1, price: 1099, list_price: 1299 };

function mountEditor(initial: Record<string, unknown>, props: Record<string, unknown> = {}) {
    let form: EditDraftForm<Record<string, unknown>> | undefined;

    const Page = defineComponent({
        setup() {
            form = useEditDraft({ initial, draft: null, urls: { draft: '/v/1/draft', commit: '/v/1/draft/commit' } });

            return () => h(PricingEditor, { currencies, customerGroups: [{ id: 5, name: 'Wholesale' }], ...props });
        },
    });

    return {
        wrapper: mount(Page, {
            attachTo: document.body,
            global: { stubs: { Select: { template: '<select><slot /></select>' } } },
        }),
        form: form!,
    };
}

const button = (wrapper: ReturnType<typeof mount>, label: string) =>
    wrapper.findAll('button').find((candidate) => candidate.text().includes(label))!;

describe('PricingEditor', () => {
    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('renders one base row per currency in major units', () => {
        const { wrapper, form } = mountEditor({ 'price:GBP': [base], 'price:JPY': [] });

        const inputs = wrapper.findAll('input[type="number"]');

        expect((inputs[0].element as HTMLInputElement).value).toBe('10.99');
        expect((inputs[1].element as HTMLInputElement).value).toBe('12.99');
        expect(wrapper.text()).toContain('JPY');
        expect(form.dirtyKeys.value).toEqual([]);
    });

    it('drafts an amount as minor units by the currency decimal places', async () => {
        const { wrapper, form } = mountEditor({ 'price:GBP': [base], 'price:JPY': [] });

        await wrapper.find('input[aria-label="pricing.amount GBP"]').setValue('12.5');
        await wrapper.find('input[aria-label="pricing.amount JPY"]').setValue('1500');

        expect(form.values['price:GBP']).toEqual([{ ...base, price: 1250 }]);
        expect(form.values['price:JPY']).toEqual([{ customer_group_id: null, min_quantity: 1, price: 1500, list_price: null }]);
        expect(form.dirtyKeys.value.sort()).toEqual(['price:GBP', 'price:JPY']);
    });

    it('is clean again once an edit is typed back', async () => {
        const { wrapper, form } = mountEditor({ 'price:GBP': [base], 'price:JPY': [] });

        const amount = wrapper.find('input[aria-label="pricing.amount GBP"]');
        await amount.setValue('20');
        await amount.setValue('10.99');

        expect(form.dirtyKeys.value).toEqual([]);
    });

    it('adds tiers and group rows in the order the server lists them', async () => {
        const { wrapper, form } = mountEditor({ 'price:GBP': [base], 'price:JPY': [] });

        await button(wrapper, 'pricing.group_add').trigger('click');
        await button(wrapper, 'pricing.tier_add').trigger('click');

        const amounts = wrapper.findAll('input[placeholder="pricing.amount"]');
        await amounts[0].setValue('9');
        await amounts[1].setValue('8');

        // Base, then the any-customer tier, then the group price.
        expect(form.values['price:GBP']).toEqual([
            base,
            { customer_group_id: null, min_quantity: 2, price: 800, list_price: null },
            { customer_group_id: 5, min_quantity: 1, price: 900, list_price: null },
        ]);
    });

    it('removes a row from the draft', async () => {
        const tier = { customer_group_id: null, min_quantity: 10, price: 900, list_price: null };
        const { wrapper, form } = mountEditor({ 'price:GBP': [base, tier], 'price:JPY': [] });

        await wrapper.find('button[aria-label="pricing.remove_row"]').trigger('click');

        expect(form.values['price:GBP']).toEqual([base]);
    });

    it('shows a commit error on the row that caused it', async () => {
        const tier = { customer_group_id: null, min_quantity: 10, price: 900, list_price: null };
        const { wrapper, form } = mountEditor({ 'price:GBP': [base, tier], 'price:JPY': [] });

        // Positions are known once the editor has written its lists.
        await wrapper.find('input[aria-label="pricing.amount GBP"]').setValue('11');
        form.errors.value = { 'price:GBP.1.price': 'The price must be at least 0.' };
        await nextTick();

        const tierRow = wrapper.find('input[aria-label="pricing.tier_min_qty"]').element.closest('.grid')!;
        const baseRow = wrapper.find('input[aria-label="pricing.amount GBP"]').element.closest('.grid')!;

        expect(tierRow.textContent).toContain('The price must be at least 0.');
        expect(baseRow.textContent).not.toContain('The price must be at least 0.');
    });

    it('rebuilds from values replaced outside the editor', async () => {
        const { wrapper, form } = mountEditor({ 'price:GBP': [base], 'price:JPY': [] });

        form.values['price:GBP'] = [{ ...base, price: 4200 }];
        await nextTick();

        expect((wrapper.find('input[aria-label="pricing.amount GBP"]').element as HTMLInputElement).value).toBe('42.00');
    });

    it('binds the product page variant slice with a price: prefix', async () => {
        const { wrapper, form } = mountEditor(
            { 'variant:price:GBP': [base], 'variant:price:JPY': [] },
            { namespace: 'variant', fieldPrefix: 'price:' },
        );

        await wrapper.find('input[aria-label="pricing.amount GBP"]').setValue('15');

        expect(form.values['variant:price:GBP']).toEqual([{ ...base, price: 1500 }]);
    });
});
