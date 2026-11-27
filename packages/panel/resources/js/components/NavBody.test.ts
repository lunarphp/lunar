import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import type { NavItemShape, NavTreeShape } from '../types/navigation';

const { pageProps } = vi.hoisted(() => ({
    pageProps: {
        panel: { name: 'Lunar', path: 'panel', storefront_url: null, support_url: null },
        auth: { user: { name: 'Ada Lovelace', email: 'ada@example.com', avatar: null } },
        locale: 'en',
        availableLocales: ['en'],
        navigation: { groups: [], items: [] } as NavTreeShape,
        settingsNavigation: { groups: [], items: [] } as NavTreeShape,
    },
}));

vi.mock('@inertiajs/vue3', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        Link: defineComponent({
            name: 'Link',
            props: ['href'],
            setup(props, { slots }) {
                return () => h('a', { href: props.href }, slots.default?.());
            },
        }),
        router: { visit: vi.fn(), get: vi.fn(), post: vi.fn(), put: vi.fn() },
        usePage: () => ({ props: pageProps, url: '/panel' }),
    };
});

import NavBody from './NavBody.vue';

const navItem = (key: string, url: string): NavItemShape => ({
    key,
    label: key,
    icon: null,
    url,
    priority: 10,
    badge: null,
    exact: false,
    children: [],
});

let wrapper: VueWrapper | null = null;

function settingsLink(): HTMLAnchorElement | undefined {
    return wrapper!.findAll('a').map((link) => link.element as HTMLAnchorElement).find((link) => link.textContent?.includes('nav.settings'));
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    pageProps.settingsNavigation = { groups: [], items: [] };
});

describe('NavBody', () => {
    it('links Settings to the first settings page the staff member can see', () => {
        pageProps.settingsNavigation = {
            groups: [{ key: 'store', label: 'Store', priority: 20, section: null, items: [navItem('tags', '/panel/settings/tags')] }],
            items: [],
        };

        wrapper = mount(NavBody);

        expect(settingsLink()?.getAttribute('href')).toBe('/panel/settings/tags');
    });

    it('hides Settings when the staff member can see no settings page', () => {
        wrapper = mount(NavBody);

        expect(settingsLink()).toBeUndefined();
    });
});
