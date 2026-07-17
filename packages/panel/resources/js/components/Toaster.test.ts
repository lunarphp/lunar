import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick, reactive } from 'vue';
import Toaster from './Toaster.vue';
import { useToasts } from '../composables/useToasts';

const pageProps = reactive<{ flash?: Record<string, string | null> }>({});

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: pageProps }),
}));

let wrapper: VueWrapper | null = null;

// The stack teleports to document.body, out of the wrapper's subtree.
const stack = (): HTMLElement => document.body;

function mountToaster(): VueWrapper {
    wrapper = mount(Toaster, { attachTo: document.body });

    return wrapper;
}

describe('Toaster', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        useToasts().clear();
        delete pageProps.flash;
    });

    afterEach(() => {
        wrapper?.unmount();
        wrapper = null;
        useToasts().clear();
        vi.useRealTimers();
        document.body.innerHTML = '';
    });

    it('renders pushed toasts with their tone roles', async () => {
        mountToaster();

        const toast = useToasts();
        toast.success('Saved.');
        toast.error('Broke.');
        toast.info('FYI.');
        await nextTick();

        const statuses = [...stack().querySelectorAll('[role="status"]')].map((el) => el.textContent?.trim());
        const alerts = [...stack().querySelectorAll('[role="alert"]')].map((el) => el.textContent?.trim());

        expect(statuses.some((text) => text?.includes('Saved.'))).toBe(true);
        expect(statuses.some((text) => text?.includes('FYI.'))).toBe(true);
        expect(alerts.some((text) => text?.includes('Broke.'))).toBe(true);
    });

    it('auto-dismisses success toasts and keeps error toasts until closed', async () => {
        mountToaster();

        const toast = useToasts();
        toast.success('Saved.');
        toast.error('Broke.');
        await nextTick();

        await vi.advanceTimersByTimeAsync(6000);

        expect(stack().textContent).not.toContain('Saved.');
        expect(stack().textContent).toContain('Broke.');
    });

    it('dismisses a toast via its close button', async () => {
        mountToaster();

        useToasts().error('Broke.');
        await nextTick();

        stack().querySelector<HTMLButtonElement>('[role="alert"] button')?.click();
        await nextTick();

        expect(stack().textContent).not.toContain('Broke.');
    });

    it('pauses the auto-dismiss while hovered and re-arms on leave', async () => {
        mountToaster();

        useToasts().success('Saved.');
        await nextTick();

        const el = stack().querySelector('[role="status"]');
        el?.dispatchEvent(new Event('mouseenter'));
        await vi.advanceTimersByTimeAsync(10_000);

        expect(stack().textContent).toContain('Saved.');

        el?.dispatchEvent(new Event('mouseleave'));
        await vi.advanceTimersByTimeAsync(6000);

        expect(stack().textContent).not.toContain('Saved.');
    });

    it('bridges server flash props into toasts, deduping repeat passes over one flash object', async () => {
        mountToaster();

        pageProps.flash = { success: 'Changes saved.', info: 'Heads up.' };
        await nextTick();

        expect(stack().textContent).toContain('Changes saved.');
        expect(stack().textContent).toContain('Heads up.');
        expect(useToasts().toasts.value).toHaveLength(2);

        // A second instance (as during a layout swap) sees the same flash
        // object and must not duplicate it.
        const second = mount(Toaster, { attachTo: document.body });
        await nextTick();

        expect(useToasts().toasts.value).toHaveLength(2);
        second.unmount();

        // A fresh flash object from the next response toasts again, even with
        // identical text (a repeat save).
        pageProps.flash = { ...pageProps.flash };
        await nextTick();

        expect(useToasts().toasts.value).toHaveLength(4);
    });
});
