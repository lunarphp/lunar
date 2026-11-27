import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { nextTick, type Component } from 'vue';

vi.mock('@inertiajs/vue3', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/vue3')>()),
    usePage: () => ({ props: { panel: { name: 'Lunar' }, auth: { user: null } } }),
}));

import PanelLayout from './PanelLayout.vue';
import SettingsShell from './SettingsShell.vue';
import { useNavState } from '../composables/useNavState';

let wrapper: VueWrapper | null = null;

function mountLayout(layout: Component): VueWrapper {
    wrapper = mount(layout, {
        global: {
            stubs: {
                NavBody: true,
                SettingsNavBody: true,
                CommandPalette: true,
                Toaster: true,
                Breadcrumbs: true,
                PageActions: true,
                PageHeader: true,
                PageZone: true,
            },
        },
    });

    return wrapper;
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    useNavState().state.collapsed = false;
});

// The test i18n instance has no messages, so t() returns the raw key: a
// hardcoded English label would show up here instead of the key.
describe.each([
    ['PanelLayout', PanelLayout],
    ['SettingsShell', SettingsShell],
])('%s sidebar collapse toggle', (_name, layout) => {
    it('labels the toggle through translation keys', async () => {
        const toggle = mountLayout(layout).get('button[title]');

        expect(toggle.attributes('aria-label')).toBe('nav.collapse_sidebar');
        expect(toggle.attributes('title')).toContain('nav.collapse_sidebar');

        await toggle.trigger('click');
        await nextTick();

        expect(toggle.attributes('aria-label')).toBe('nav.expand_sidebar');
        expect(toggle.attributes('title')).toContain('nav.expand_sidebar');
    });
});
