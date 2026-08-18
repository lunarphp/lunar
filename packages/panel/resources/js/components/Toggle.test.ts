import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import Toggle from './Toggle.vue';

describe('Toggle', () => {
    it('emits toggle when the switch is clicked', async () => {
        const wrapper = mount(Toggle, { props: { on: false } });

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('toggle')).toHaveLength(1);
    });

    it('emits toggle when clicked while on', async () => {
        const wrapper = mount(Toggle, { props: { on: true } });

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('toggle')).toHaveLength(1);
    });

    it('does not emit toggle when disabled', async () => {
        const wrapper = mount(Toggle, { props: { on: true, disabled: true } });

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('toggle')).toBeUndefined();
    });

    it('reflects the on prop in the switch state', () => {
        expect(mount(Toggle, { props: { on: true } }).find('button').attributes('data-state')).toBe('checked');
        expect(mount(Toggle, { props: { on: false } }).find('button').attributes('data-state')).toBe('unchecked');
    });
});
