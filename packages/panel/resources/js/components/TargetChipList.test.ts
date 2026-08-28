import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import TargetChipList from './TargetChipList.vue';

const chips = {
    products: [{ id: 1, label: 'Widget', hint: 'WID-1' }],
    brands: [{ id: 7, label: 'Stark', hint: null }],
};

describe('TargetChipList', () => {
    it('groups the chips by kind', () => {
        const wrapper = mount(TargetChipList, {
            props: { chips, kinds: ['products', 'brands'], label: 'Applies to' },
        });

        expect(wrapper.text()).toContain('Widget');
        expect(wrapper.text()).toContain('WID-1');
        expect(wrapper.text()).toContain('Stark');
        expect(wrapper.text()).toContain('discounts.kind_products');
        expect(wrapper.text()).toContain('discounts.kind_brands');
    });

    it('says nothing is selected rather than rendering an empty list', () => {
        const wrapper = mount(TargetChipList, {
            props: { chips: { products: [] }, kinds: ['products'], label: 'Applies to' },
        });

        expect(wrapper.text()).toContain('discounts.target_empty');
    });

    it('emits the kind alongside the id when a chip is removed', async () => {
        const wrapper = mount(TargetChipList, {
            props: { chips, kinds: ['products', 'brands'], label: 'Applies to' },
        });

        // The remove buttons follow the chips; the second belongs to the brand.
        const buttons = wrapper.findAll('button');
        await buttons[buttons.length - 1].trigger('click');

        expect(wrapper.emitted('remove')?.at(-1)).toEqual(['brands', 7]);
    });

    it('emits add when the add button is used', async () => {
        const wrapper = mount(TargetChipList, {
            props: { chips: { products: [] }, kinds: ['products'], label: 'Applies to' },
        });

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('add')).toHaveLength(1);
    });

    it('only renders the kinds it was given', () => {
        const wrapper = mount(TargetChipList, {
            props: { chips, kinds: ['products'], label: 'Applies to' },
        });

        expect(wrapper.text()).toContain('Widget');
        expect(wrapper.text()).not.toContain('Stark');
    });
});
