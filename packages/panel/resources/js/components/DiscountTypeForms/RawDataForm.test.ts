import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import RawDataForm from './RawDataForm.vue';

const currencies = [{ id: 1, code: 'GBP', decimal_places: 2, default: true }];

describe('RawDataForm', () => {
    it('seeds the editor from the stored value', () => {
        const wrapper = mount(RawDataForm, { props: { modelValue: { percentage: 15 }, currencies } });

        expect((wrapper.find('textarea').element as HTMLTextAreaElement).value).toContain('"percentage": 15');
    });

    it('emits the parsed payload as it is edited', async () => {
        const wrapper = mount(RawDataForm, { props: { modelValue: {}, currencies } });

        await wrapper.find('textarea').setValue('{"tier": 3}');

        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual([{ tier: 3 }]);
    });

    it('flags unparsable json without emitting it', async () => {
        const wrapper = mount(RawDataForm, { props: { modelValue: {}, currencies } });

        await wrapper.find('textarea').setValue('{"tier":');

        expect(wrapper.find('textarea').attributes('aria-invalid')).toBe('true');
        expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });
});
