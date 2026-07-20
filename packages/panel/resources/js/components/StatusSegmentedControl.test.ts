import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import StatusSegmentedControl from './StatusSegmentedControl.vue';

const options = [
    { value: 'active', label: 'Active', tone: 'sage' as const },
    { value: 'draft', label: 'Draft', tone: 'warn' as const },
];

describe('StatusSegmentedControl', () => {
    it('marks the current value as checked', () => {
        const wrapper = mount(StatusSegmentedControl, { props: { modelValue: 'draft', options } });

        const segments = wrapper.findAll('button');

        expect(segments[0].attributes('aria-checked')).toBe('false');
        expect(segments[1].attributes('aria-checked')).toBe('true');
    });

    it('tints the selected segment with its tone', () => {
        const wrapper = mount(StatusSegmentedControl, { props: { modelValue: 'active', options } });

        const segments = wrapper.findAll('button');

        expect(segments[0].classes().join(' ')).toContain('bg-sage-soft');
        expect(segments[1].classes().join(' ')).not.toContain('bg-warn-soft');
    });

    it('emits the clicked option value', async () => {
        const wrapper = mount(StatusSegmentedControl, { props: { modelValue: 'active', options } });

        await wrapper.findAll('button')[1].trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([['draft']]);
    });
});
