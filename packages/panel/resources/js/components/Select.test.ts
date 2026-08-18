import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';
import Select from './Select.vue';

const options = () => [
    h('option', { value: null }, 'No group'),
    h('option', { value: 5 }, 'Details'),
];

const selectOption = async (wrapper: ReturnType<typeof mount>, index: number) => {
    const select = wrapper.find('select');
    (select.element as HTMLSelectElement).options[index].selected = true;
    await select.trigger('change');
};

describe('Select', () => {
    it('emits the null bound value, not the option text, when the null option is chosen', async () => {
        const wrapper = mount(Select, {
            props: { modelValue: 5 },
            slots: { default: options },
        });

        await selectOption(wrapper, 0);

        // Regression: previously emitted the option text ("No group") via target.value.
        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual([null]);
    });

    it('emits a numeric bound value, not its string form, when a value option is chosen', async () => {
        const wrapper = mount(Select, {
            props: { modelValue: null },
            slots: { default: options },
        });

        await selectOption(wrapper, 1);

        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual([5]);
    });

    it('selects the option matching the model value', () => {
        const wrapper = mount(Select, {
            props: { modelValue: 5 },
            slots: { default: options },
        });

        expect((wrapper.find('select').element as HTMLSelectElement).value).toBe('5');
    });
});
