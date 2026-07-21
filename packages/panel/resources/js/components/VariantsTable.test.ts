import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { router } from '@inertiajs/vue3';
import VariantsTable from './VariantsTable.vue';
import type { VariantRow } from './ProductOptionsBuilder.vue';

const rows: VariantRow[] = [
    { id: 1, label: 'Small', value_ids: [11], sku: 'S-1', price: 1099, stock: 5, enabled: true, locked: false, thumbnail: null, edit_url: '/v/1' },
    { id: 2, label: 'Medium', value_ids: [12], sku: 'S-2', price: null, stock: 0, enabled: false, locked: true, thumbnail: null, edit_url: '/v/2' },
];

const mountTable = () =>
    mount(VariantsTable, {
        props: {
            variants: rows,
            currencies: [{ id: 1, code: 'GBP', decimal_places: 2, default: true }],
            bulkUrl: '/bulk',
        },
        global: {
            stubs: {
                Tooltip: { template: '<span><slot /></span>' },
                Link: { template: '<a><slot /></a>' },
                FilterDropdown: { template: '<span />' },
            },
        },
    });

describe('VariantsTable', () => {
    it('renders variant rows with formatted default-currency prices', () => {
        const wrapper = mountTable();

        expect(wrapper.text()).toContain('Small');
        expect(wrapper.text()).toContain('10.99 GBP');
        expect(wrapper.text()).toContain('—');
    });

    it('marks locked rows', () => {
        const wrapper = mountTable();

        expect(wrapper.text()).toContain('products.variants_state_disabled');
    });

    it('shows the bulk bar once rows are selected and posts operations', async () => {
        const post = vi.spyOn(router, 'post').mockImplementation(() => undefined as never);

        const wrapper = mountTable();

        const checkbox = wrapper.findAll('[aria-label="products.variants_select"]')[0]
            ?? wrapper.findAll('input[type="checkbox"], button[role="checkbox"]')[1];

        await checkbox.trigger('click');

        expect(wrapper.text()).toContain('products.variants_selected');

        const enable = wrapper.findAll('button').find((button) => button.text().includes('products.variants_enable'));
        await enable?.trigger('click');

        expect(post).toHaveBeenCalledWith('/bulk', expect.objectContaining({ op: 'enable', ids: [1] }), expect.anything());

        post.mockRestore();
    });
});
