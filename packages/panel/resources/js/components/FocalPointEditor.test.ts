import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import FocalPointEditor from './FocalPointEditor.vue';

const mountEditor = () => {
    const wrapper = mount(FocalPointEditor, {
        props: { modelValue: { x: 50, y: 50 }, src: 'image.jpg' },
        attachTo: document.body,
    });

    // happy-dom reports zero dimensions; stub the surface rect so pointer
    // coordinates translate to percentages.
    vi.spyOn(wrapper.element as HTMLElement, 'getBoundingClientRect').mockReturnValue({
        left: 0,
        top: 0,
        width: 200,
        height: 100,
        right: 200,
        bottom: 100,
        x: 0,
        y: 0,
        toJSON: () => ({}),
    } as DOMRect);

    return wrapper;
};

describe('FocalPointEditor', () => {
    it('emits clamped integer percentages on pointer down', async () => {
        const wrapper = mountEditor();

        await wrapper.trigger('pointerdown', { clientX: 50, clientY: 75 });

        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual([{ x: 25, y: 75 }]);
    });

    it('clamps coordinates outside the surface', async () => {
        const wrapper = mountEditor();

        await wrapper.trigger('pointerdown', { clientX: -30, clientY: 500 });

        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual([{ x: 0, y: 100 }]);
    });

    it('positions the crosshair from the model value', () => {
        const wrapper = mount(FocalPointEditor, {
            props: { modelValue: { x: 20, y: 80 }, src: 'image.jpg' },
        });

        const marker = wrapper.find('[class*="rounded-full border-2"]');

        expect(marker.attributes('style')).toContain('left: 20%');
        expect(marker.attributes('style')).toContain('top: 80%');
    });
});
