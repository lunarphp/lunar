import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import TextInput from './TextInput.vue';

describe('TextInput', () => {
    it('shows no clear button unless clearable', () => {
        const wrapper = mount(TextInput, { props: { modelValue: 'hello' } });

        expect(wrapper.find('button').exists()).toBe(false);
    });

    it('shows the clear button only while a clearable input holds a value', async () => {
        const wrapper = mount(TextInput, { props: { modelValue: '', clearable: true } });

        expect(wrapper.find('button').exists()).toBe(false);

        await wrapper.setProps({ modelValue: 'hello' });

        expect(wrapper.find('button').exists()).toBe(true);
    });

    it('emits an empty value when cleared', async () => {
        const wrapper = mount(TextInput, { props: { modelValue: 'hello', clearable: true } });

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([['']]);
    });

    it('hides the clear button while disabled', () => {
        const wrapper = mount(TextInput, { props: { modelValue: 'hello', clearable: true, disabled: true } });

        expect(wrapper.find('button').exists()).toBe(false);
    });
});
