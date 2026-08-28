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

    it('puts the id and numeric attributes on the input, not the wrapper', () => {
        // A prefixed input renders a grouped wrapper, so these have to be bound
        // explicitly — attribute fallthrough would land them on the wrapper div,
        // breaking label association and dropping the numeric constraints.
        const wrapper = mount(TextInput, {
            props: { id: 'amount', type: 'number', min: 0, max: 100, step: '0.01' },
            slots: { prefix: 'GBP' },
        });

        const input = wrapper.find('input');

        expect(input.attributes('id')).toBe('amount');
        expect(input.attributes('min')).toBe('0');
        expect(input.attributes('max')).toBe('100');
        expect(input.attributes('step')).toBe('0.01');
        expect(wrapper.find('div').attributes('id')).toBeUndefined();
    });

    it('puts them on a standalone input too', () => {
        const wrapper = mount(TextInput, { props: { id: 'plain', type: 'number', step: '1' } });

        expect(wrapper.find('input').attributes('id')).toBe('plain');
        expect(wrapper.find('input').attributes('step')).toBe('1');
    });
});
