import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
    router: { get: vi.fn(), post: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn() },
}));

import { router } from '@inertiajs/vue3';
import CollectionGroupDialog, { type CollectionGroupOption } from './CollectionGroupDialog.vue';

// Reka's DialogPortal teleports content to document.body, out of the
// wrapper's reach; interactions query the document instead.
const body = (): HTMLElement => document.body;

const group: CollectionGroupOption = {
    id: 7,
    name: 'Navigation',
    handle: 'navigation',
    collections_count: 0,
    urls: { update: '/panel/collections/groups/7', destroy: '/panel/collections/groups/7' },
};

let wrapper: VueWrapper | null = null;

async function mountDialog(props: Partial<{ group: CollectionGroupOption | null }> = {}): Promise<VueWrapper> {
    wrapper = mount(CollectionGroupDialog, {
        props: { open: true, group: null, storeUrl: '/panel/collections/groups', ...props },
        attachTo: document.body,
    });

    // The portal teleports content to document.body on the next tick.
    await nextTick();

    return wrapper;
}

function input(selector: string): HTMLInputElement {
    const element = body().querySelector<HTMLInputElement>(selector);

    expect(element, `input "${selector}"`).not.toBeNull();

    return element!;
}

async function setInput(selector: string, value: string): Promise<void> {
    const element = input(selector);
    element.value = value;
    element.dispatchEvent(new Event('input', { bubbles: true }));

    await nextTick();
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
});

describe('CollectionGroupDialog', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('mirrors the name into the handle until the handle is edited', async () => {
        await mountDialog();

        await setInput('#group-name', 'Gift Guides');
        expect(input('#group-handle').value).toBe('gift-guides');

        await setInput('#group-handle', 'custom');
        await setInput('#group-name', 'Renamed');
        expect(input('#group-handle').value).toBe('custom');
    });

    it('posts to the store endpoint for a new group', async () => {
        await mountDialog();

        await setInput('#group-name', 'Gift Guides');
        body().querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        await nextTick();

        expect(router.post).toHaveBeenCalledWith(
            '/panel/collections/groups',
            expect.objectContaining({ name: 'Gift Guides', handle: 'gift-guides' }),
            expect.anything(),
        );
    });

    it('puts to the update endpoint for an existing group', async () => {
        await mountDialog({ group });

        await setInput('#group-name', 'Renamed');
        body().querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        await nextTick();

        expect(router.put).toHaveBeenCalledWith(
            '/panel/collections/groups/7',
            expect.objectContaining({ name: 'Renamed' }),
            expect.anything(),
        );
    });

    it('disables delete while the group has collections', async () => {
        await mountDialog({ group: { ...group, collections_count: 3 } });

        const deleteButton = [...body().querySelectorAll('button')]
            .find((button) => button.textContent?.includes('collections.group_delete'));

        expect(deleteButton).toBeDefined();
        expect(deleteButton!.hasAttribute('disabled')).toBe(true);
    });
});
