import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import FlashMessage from './FlashMessage.vue';

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: { flash: {} } }),
}));

const mountFlash = (props = {}) => mount(FlashMessage, { props: { message: 'Saved.', ...props } });

describe('FlashMessage', () => {
    beforeEach(() => vi.useFakeTimers());
    afterEach(() => vi.useRealTimers());

    it('shows the message with a status role', () => {
        const wrapper = mountFlash();

        const status = wrapper.find('[role="status"]');
        expect(status.exists()).toBe(true);
        expect(status.text()).toContain('Saved.');
    });

    it('renders nothing without a message', () => {
        const wrapper = mountFlash({ message: null });

        expect(wrapper.find('[role="status"]').exists()).toBe(false);
    });

    it('dismisses via the close button', async () => {
        const wrapper = mountFlash();

        await wrapper.find('button').trigger('click');

        expect(wrapper.find('[role="status"]').exists()).toBe(false);
    });

    it('auto-dismisses after the timeout', async () => {
        const wrapper = mountFlash({ timeout: 5000 });

        expect(wrapper.find('[role="status"]').exists()).toBe(true);

        vi.advanceTimersByTime(5001);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('[role="status"]').exists()).toBe(false);
    });

    it('persists when the timeout is zero', async () => {
        const wrapper = mountFlash({ timeout: 0 });

        vi.advanceTimersByTime(60_000);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('[role="status"]').exists()).toBe(true);
    });

    it('pauses the auto-dismiss while hovered', async () => {
        const wrapper = mountFlash({ timeout: 5000 });

        await wrapper.find('[role="status"]').trigger('mouseenter');
        vi.advanceTimersByTime(10_000);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('[role="status"]').exists()).toBe(true);

        await wrapper.find('[role="status"]').trigger('mouseleave');
        vi.advanceTimersByTime(5001);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('[role="status"]').exists()).toBe(false);
    });
});
