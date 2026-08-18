import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import TagsInput from './TagsInput.vue';

const mountInput = (tags: string[] = []) =>
    mount(TagsInput, {
        props: { modelValue: tags },
    });

describe('TagsInput', () => {
    it('renders existing tags as chips', () => {
        const wrapper = mountInput(['SALE', 'FESTIVE']);

        expect(wrapper.text()).toContain('SALE');
        expect(wrapper.text()).toContain('FESTIVE');
    });

    it('adds an uppercased tag on Enter and sorts the set', async () => {
        const wrapper = mountInput(['SALE']);

        await wrapper.find('input').setValue('festive');
        await wrapper.find('input').trigger('keydown', { key: 'Enter' });

        expect(wrapper.emitted('update:modelValue')?.[0]?.[0]).toEqual(['FESTIVE', 'SALE']);
    });

    it('ignores duplicates and blank input', async () => {
        const wrapper = mountInput(['SALE']);

        await wrapper.find('input').setValue('sale');
        await wrapper.find('input').trigger('keydown', { key: 'Enter' });

        await wrapper.find('input').setValue('   ');
        await wrapper.find('input').trigger('keydown', { key: 'Enter' });

        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });

    it('removes a tag from its chip button', async () => {
        const wrapper = mountInput(['SALE', 'FESTIVE']);

        await wrapper.find('button[aria-label="products.remove_tag"]').trigger('click');

        expect(wrapper.emitted('update:modelValue')?.[0]?.[0]).toEqual(['FESTIVE']);
    });

    it('removes the last tag on Backspace in an empty input', async () => {
        const wrapper = mountInput(['SALE', 'FESTIVE']);

        await wrapper.find('input').trigger('keydown', { key: 'Backspace' });

        expect(wrapper.emitted('update:modelValue')?.[0]?.[0]).toEqual(['SALE']);
    });
});
