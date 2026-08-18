import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import ValuePreviewChip from './ValuePreviewChip.vue';

describe('ValuePreviewChip', () => {
    it('renders the value name for text options', () => {
        const wrapper = mount(ValuePreviewChip, { props: { type: 'text', value: { name: 'Large' } } });

        expect(wrapper.text()).toBe('Large');
    });

    it('renders a colour swatch tinted with the hex', () => {
        const wrapper = mount(ValuePreviewChip, { props: { type: 'colour', value: { name: 'Navy', colour: '#1F2A44' } } });

        expect(wrapper.html()).toContain('background-color');
        expect(wrapper.text()).toBe('');
    });

    it('renders a swatch image when present', () => {
        const wrapper = mount(ValuePreviewChip, { props: { type: 'swatch', value: { name: 'Cotton', swatch: 'https://example.test/c.png' } } });

        expect(wrapper.html()).toContain('example.test');
    });

    it('falls back for an unknown type', () => {
        const wrapper = mount(ValuePreviewChip, { props: { type: 'gradient', value: { name: 'Matte' } } });

        expect(wrapper.text()).toBe('Matte');
        expect(wrapper.html()).toContain('gradient');
    });
});
