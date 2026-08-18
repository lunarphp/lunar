import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ProductOptionsBuilder, { type AttachedOption, type VariantRow } from './ProductOptionsBuilder.vue';

const sizeOption: AttachedOption = {
    id: 1,
    name: 'Size',
    shared: true,
    values: [
        { id: 11, name: 'Small' },
        { id: 12, name: 'Medium' },
        { id: 13, name: 'Large' },
    ],
    selected_value_ids: [11, 12],
};

const variantRow = (id: number, valueIds: number[], locked = false): VariantRow => ({
    id,
    label: `V${id}`,
    value_ids: valueIds,
    sku: null,
    price: null,
    stock: 0,
    enabled: true,
    locked,
    thumbnail: null,
    edit_url: '#',
});

const mountBuilder = (options: AttachedOption[], variants: VariantRow[]) =>
    mount(ProductOptionsBuilder, {
        props: {
            attachedOptions: options,
            variants,
            searchUrl: '/search',
            generateUrl: '/generate',
        },
        global: {
            stubs: { Tooltip: { template: '<span><slot /></span>' } },
        },
    });

describe('ProductOptionsBuilder', () => {
    it('shows the empty state without options', () => {
        const wrapper = mountBuilder([], [variantRow(1, [])]);

        expect(wrapper.text()).toContain('products.options_empty_title');
    });

    it('counts pending combinations from the selection', () => {
        const wrapper = mountBuilder([sizeOption], [variantRow(1, [11]), variantRow(2, [12])]);

        expect(wrapper.text()).toContain('products.options_combo_count');
    });

    it('reports no drift while variants match the selection', () => {
        const wrapper = mountBuilder([sizeOption], [variantRow(1, [11]), variantRow(2, [12])]);

        expect(wrapper.text()).not.toContain('products.options_drift');
    });

    it('detects drift once a selected value has no variant', async () => {
        const wrapper = mountBuilder([sizeOption], [variantRow(1, [11]), variantRow(2, [12])]);

        // Select "Large" — a third combination with no variant behind it.
        const chips = wrapper.findAll('button[aria-pressed]');
        await chips[2].trigger('click');

        expect(wrapper.text()).toContain('products.options_drift');
        expect(wrapper.emitted('update:staleIds')).toBeTruthy();
    });

    it('blocks regeneration while a removal is locked', async () => {
        const wrapper = mountBuilder([sizeOption], [
            variantRow(1, [11]),
            variantRow(2, [12], true),
        ]);

        // Deselect "Medium" — the locked variant becomes a removal.
        const chips = wrapper.findAll('button[aria-pressed]');
        await chips[1].trigger('click');

        expect(wrapper.text()).toContain('products.options_locked');
    });

    it('deselects and reselects shared values', async () => {
        const wrapper = mountBuilder([sizeOption], []);

        const chips = wrapper.findAll('button[aria-pressed]');

        expect(chips[0].attributes('aria-pressed')).toBe('true');

        await chips[0].trigger('click');

        expect(wrapper.findAll('button[aria-pressed]')[0].attributes('aria-pressed')).toBe('false');
    });
});
