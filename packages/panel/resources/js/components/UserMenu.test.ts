import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick } from 'vue';

const { visitMock, pageProps } = vi.hoisted(() => ({
    visitMock: vi.fn(),
    pageProps: {
        panel: { path: 'panel' },
        auth: { user: { name: 'Ada Lovelace', email: 'ada@example.com', avatar: null } },
        locale: 'en',
        availableLocales: ['en'],
    },
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { visit: visitMock, post: vi.fn(), put: vi.fn() },
    usePage: () => ({ props: pageProps }),
}));

import UserMenu from './UserMenu.vue';

let wrapper: VueWrapper | null = null;

async function openMenu(): Promise<void> {
    wrapper = mount(UserMenu, { attachTo: document.body });

    await wrapper.find('button').trigger('keydown', { key: 'Enter' });
    await nextTick();
    await nextTick();
}

function menuItem(label: string): HTMLElement | undefined {
    return Array.from(document.body.querySelectorAll<HTMLElement>('[role="menuitem"]')).find((item) =>
        item.textContent?.includes(label),
    );
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    document.body.innerHTML = '';
    visitMock.mockClear();
});

describe('UserMenu', () => {
    it('links to the account security page so staff can enrol a second factor', async () => {
        await openMenu();

        const item = menuItem('auth.security_title');

        expect(item, 'the security menu item').toBeDefined();

        item!.click();
        await nextTick();

        expect(visitMock).toHaveBeenCalledWith('/panel/account/security');
    });

    it('follows the configured panel path', async () => {
        pageProps.panel.path = 'backoffice';

        await openMenu();
        menuItem('auth.security_title')?.click();
        await nextTick();

        pageProps.panel.path = 'panel';

        expect(visitMock).toHaveBeenCalledWith('/backoffice/account/security');
    });

    it('keeps the sign out item last in the menu', async () => {
        await openMenu();

        const items = Array.from(document.body.querySelectorAll('[role="menuitem"]'));

        expect(items.at(-1)?.textContent).toContain('nav.sign_out');
    });
});
