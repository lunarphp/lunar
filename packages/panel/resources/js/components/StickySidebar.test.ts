import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import StickySidebar from './StickySidebar.vue';

const mountAt = (sidebarHeight: number, viewportHeight: number) => {
    Object.defineProperty(window, 'innerHeight', { value: viewportHeight, configurable: true });
    vi.spyOn(HTMLElement.prototype, 'offsetHeight', 'get').mockReturnValue(sidebarHeight);

    return mount(StickySidebar, { slots: { default: '<p>card</p>' } });
};

describe('StickySidebar', () => {
    beforeEach(() => {
        vi.stubGlobal('ResizeObserver', undefined);
    });

    afterEach(() => {
        vi.restoreAllMocks();
        vi.unstubAllGlobals();
    });

    it('pins below the breadcrumb bar when it fits in the viewport', async () => {
        const wrapper = mountAt(400, 900);
        await nextTick();

        expect(wrapper.attributes('style')).toContain('top: 60px');
        expect(wrapper.classes()).toContain('lg:sticky');
    });

    it('sticks by its bottom edge when taller than the viewport', async () => {
        const wrapper = mountAt(1500, 900);
        await nextTick();

        // 900 - 1500 - 16: the sidebar scrolls with the page until its end is in view.
        expect(wrapper.attributes('style')).toContain('top: -616px');
    });

    it('re-measures when the window resizes', async () => {
        const wrapper = mountAt(1500, 900);
        await nextTick();

        Object.defineProperty(window, 'innerHeight', { value: 2000, configurable: true });
        window.dispatchEvent(new Event('resize'));
        await nextTick();

        expect(wrapper.attributes('style')).toContain('top: 60px');
    });
});
