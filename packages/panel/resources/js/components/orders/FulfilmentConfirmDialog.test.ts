import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
    router: { get: vi.fn(), post: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn() },
}));

import { router } from '@inertiajs/vue3';
import FulfilmentConfirmDialog, { type PendingFulfilmentAction } from './FulfilmentConfirmDialog.vue';

let wrapper: VueWrapper | null = null;

const body = (): HTMLElement => document.body;

async function mountDialog(action: PendingFulfilmentAction): Promise<VueWrapper> {
    wrapper = mount(FulfilmentConfirmDialog, { props: { action }, attachTo: document.body });
    await nextTick();

    return wrapper;
}

const confirmButton = (label: string): HTMLButtonElement =>
    [...body().querySelectorAll('button')].filter((button) => button.textContent?.includes(label)).at(-1)!;

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('FulfilmentConfirmDialog', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('posts the action data with the notify intent when the toggle is shown', async () => {
        await mountDialog({
            title: 'Mark fulfilment as In progress?',
            confirmLabel: 'In progress',
            url: '/f/5/transition',
            data: { state: 'in-progress' },
            showNotify: true,
        });

        expect(body().textContent).toContain('orders.ship_notify');

        confirmButton('In progress').click();
        await nextTick();

        expect(router.post).toHaveBeenCalledWith(
            '/f/5/transition',
            { state: 'in-progress', notify: true },
            expect.objectContaining({ preserveScroll: true }),
        );
    });

    it('omits the notify field when no notification is configured', async () => {
        await mountDialog({ title: 'Release hold', url: '/f/5/release' });

        expect(body().textContent).not.toContain('orders.ship_notify');

        confirmButton('Release hold').click();
        await nextTick();

        expect(router.post).toHaveBeenCalledWith('/f/5/release', {}, expect.anything());
    });

    it('deletes when the action is a removal', async () => {
        await mountDialog({
            title: 'Remove tracking?',
            confirmLabel: 'Remove',
            tone: 'danger',
            url: '/f/5/trackings/9',
            method: 'delete',
        });

        confirmButton('Remove').click();
        await nextTick();

        expect(router.delete).toHaveBeenCalledWith('/f/5/trackings/9', expect.objectContaining({ preserveScroll: true }));
    });
});
