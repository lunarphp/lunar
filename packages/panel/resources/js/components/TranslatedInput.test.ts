import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import TranslatedInput from './TranslatedInput.vue';

const languages = [
    { id: 1, code: 'en', name: 'English', default: true },
    { id: 2, code: 'fr', name: 'French', default: false },
];

describe('TranslatedInput', () => {
    it('shows the default language value and switches locales via the picker', async () => {
        const wrapper = mount(TranslatedInput, {
            props: { modelValue: { en: 'Hello', fr: 'Bonjour' }, languages },
        });

        expect(wrapper.find('input').element.value).toBe('Hello');

        await wrapper.find('select').setValue('fr');

        expect(wrapper.find('input').element.value).toBe('Bonjour');
    });

    it('lists each language as an uppercase code option', () => {
        const wrapper = mount(TranslatedInput, {
            props: { modelValue: { en: 'Hello' }, languages },
        });

        expect(wrapper.findAll('option').map((option) => option.text())).toEqual(['EN', 'FR']);
    });

    it('updates only the active locale key', async () => {
        const wrapper = mount(TranslatedInput, {
            props: { modelValue: { en: 'Hello', fr: 'Bonjour' }, languages },
        });

        await wrapper.find('input').setValue('Hi');

        expect(wrapper.emitted('update:modelValue')).toEqual([[{ en: 'Hi', fr: 'Bonjour' }]]);
    });

    it('drops a locale key when its value is cleared', async () => {
        const wrapper = mount(TranslatedInput, {
            props: { modelValue: { en: 'Hello', fr: 'Bonjour' }, languages },
        });

        await wrapper.find('input').setValue('');

        expect(wrapper.emitted('update:modelValue')).toEqual([[{ fr: 'Bonjour' }]]);
    });

    it('hides the language picker with a single language', () => {
        const wrapper = mount(TranslatedInput, {
            props: { modelValue: {}, languages: [languages[0]] },
        });

        expect(wrapper.find('select').exists()).toBe(false);
    });
});
