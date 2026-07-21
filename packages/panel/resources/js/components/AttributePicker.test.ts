import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import AttributePicker from './AttributePicker.vue';

const groups = [
    {
        handle: 'details',
        name: 'Details',
        attributes: [
            { id: 1, name: 'Material', handle: 'material', type: 'text', required: false },
            { id: 2, name: 'Colour', handle: 'colour', type: 'dropdown', required: true },
        ],
    },
    {
        handle: 'specs',
        name: 'Specs',
        attributes: [
            { id: 3, name: 'Storage', handle: 'storage', type: 'number', required: false },
        ],
    },
];

const mountPicker = (modelValue: number[] = [1]) =>
    mount(AttributePicker, {
        props: { modelValue, groups, title: 'Product attributes' },
    });

describe('AttributePicker', () => {
    it('renders a row per attribute with type and required pills', () => {
        const wrapper = mountPicker();

        expect(wrapper.text()).toContain('Material');
        expect(wrapper.text()).toContain('colour');
        expect(wrapper.text()).toContain('dropdown');
        expect(wrapper.text()).toContain('attributes.picker_required');
    });

    it('toggling a row emits the sorted id list', async () => {
        const wrapper = mountPicker([3]);

        const material = wrapper.findAll('label').find((row) => row.text().includes('Material'))!;
        await material.find('button[role="checkbox"]').trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([[[1, 3]]]);
    });

    it('unchecking a selected row removes its id', async () => {
        const wrapper = mountPicker([1, 3]);

        const material = wrapper.findAll('label').find((row) => row.text().includes('Material'))!;
        await material.find('button[role="checkbox"]').trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([[[3]]]);
    });

    it('the group All toggle selects every attribute in the group only', async () => {
        const wrapper = mountPicker([]);

        const all = wrapper
            .findAll('button')
            .find((button) => button.text() === 'attributes.picker_all')!;
        await all.trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([[[1, 2]]]);
    });

    it('the group None toggle clears the group but keeps other selections', async () => {
        const wrapper = mountPicker([1, 2, 3]);

        const none = wrapper
            .findAll('button')
            .find((button) => button.text() === 'attributes.picker_none')!;
        await none.trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([[[3]]]);
    });

    it('search filters rows by name and handle and hides empty groups', async () => {
        const wrapper = mountPicker();

        await wrapper.find('input').setValue('storage');

        expect(wrapper.text()).toContain('Storage');
        expect(wrapper.text()).not.toContain('Material');
        expect(wrapper.text()).not.toContain('Details');
    });

    it('shows the empty state when no groups exist', () => {
        const wrapper = mount(AttributePicker, {
            props: { modelValue: [], groups: [], title: 'Product attributes' },
        });

        expect(wrapper.text()).toContain('attributes.picker_empty');
    });
});
