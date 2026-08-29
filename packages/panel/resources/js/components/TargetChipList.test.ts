import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import TargetChipList from './TargetChipList.vue';

const chips = {
    products: [{ id: 1, label: 'Widget', hint: 'WID-1' }],
    brands: [{ id: 7, label: 'Stark', hint: null }],
};

// Reka tooltips need the layout's TooltipProvider; this stub renders through
// and surfaces the text the component passed.
const global = {
    stubs: { Tooltip: { props: ['text'], template: '<span :data-tooltip="text"><slot /></span>' } },
};

const tooltips = (wrapper: ReturnType<typeof mount>): (string | undefined)[] =>
    wrapper.findAll('[data-tooltip]').map((node) => node.attributes('data-tooltip'));

describe('TargetChipList', () => {
    it('groups the chips by kind', () => {
        const wrapper = mount(TargetChipList, {
            props: { chips, kinds: ['products', 'brands'], label: 'Applies to' },
            global,
        });

        expect(wrapper.text()).toContain('Widget');
        expect(wrapper.text()).toContain('Stark');
        expect(wrapper.text()).toContain('discounts.kind_products');
        expect(wrapper.text()).toContain('discounts.kind_brands');
    });

    it('keeps the chip to its label, leaving the detail to hover', () => {
        // A deeply nested collection path inline would make the chip, and the
        // row it sits in, unreadable.
        const wrapper = mount(TargetChipList, {
            props: {
                chips: { collections: [{ id: 3, label: 'Sale', hint: 'Shop / Apparel / Knitwear' }] },
                kinds: ['collections'],
                label: 'Applies to',
            },
            global,
        });

        expect(wrapper.text()).toContain('Sale');
        expect(wrapper.text()).not.toContain('Shop / Apparel / Knitwear');
        expect(tooltips(wrapper)).toContain('Shop / Apparel / Knitwear');
    });

    it('offers no tooltip for a target with no extra context', () => {
        const wrapper = mount(TargetChipList, {
            props: {
                chips: { brands: [{ id: 7, label: 'Stark', hint: null }] },
                kinds: ['brands'],
                label: 'Applies to',
            },
            global,
        });

        expect(tooltips(wrapper)).toEqual(['']);
    });

    it('says nothing is selected rather than rendering an empty list', () => {
        const wrapper = mount(TargetChipList, {
            props: { chips: { products: [] }, kinds: ['products'], label: 'Applies to' },
            global,
        });

        expect(wrapper.text()).toContain('discounts.target_empty');
    });

    it('emits the kind alongside the id when a chip is removed', async () => {
        const wrapper = mount(TargetChipList, {
            props: { chips, kinds: ['products', 'brands'], label: 'Applies to' },
            global,
        });

        // The remove buttons follow the chips; the second belongs to the brand.
        const buttons = wrapper.findAll('button');
        await buttons[buttons.length - 1].trigger('click');

        expect(wrapper.emitted('remove')?.at(-1)).toEqual(['brands', 7]);
    });

    it('emits add when the add button is used', async () => {
        const wrapper = mount(TargetChipList, {
            props: { chips: { products: [] }, kinds: ['products'], label: 'Applies to' },
            global,
        });

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('add')).toHaveLength(1);
    });

    it('only renders the kinds it was given', () => {
        const wrapper = mount(TargetChipList, {
            props: { chips, kinds: ['products'], label: 'Applies to' },
            global,
        });

        expect(wrapper.text()).toContain('Widget');
        expect(wrapper.text()).not.toContain('Stark');
    });
});
