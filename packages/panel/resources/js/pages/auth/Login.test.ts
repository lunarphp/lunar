import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import { reactive } from 'vue';

const pageProps = reactive<{ flash?: Record<string, string | null> }>({});

vi.mock('@inertiajs/vue3', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/vue3')>()),
    Head: { render: () => null },
    usePage: () => ({ props: pageProps }),
}));

import Login from './Login.vue';

let wrapper: VueWrapper | null = null;

function mountLogin(): VueWrapper {
    wrapper = mount(Login, {
        props: { urls: { store: '/panel/login', forgotPassword: '/panel/forgot-password' } },
        global: {
            stubs: {
                AuthLayout: { template: '<div><slot /></div>' },
                Link: { template: '<a><slot /></a>' },
            },
        },
    });

    return wrapper;
}

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    delete pageProps.flash;
});

describe('auth/Login', () => {
    it('shows the success flash, such as the password reset confirmation', () => {
        pageProps.flash = { success: 'Your password has been reset.', error: null, info: null };

        const status = mountLogin().find('[role="status"]');

        expect(status.exists()).toBe(true);
        expect(status.text()).toBe('Your password has been reset.');
    });

    it('shows no status note without a flash', () => {
        pageProps.flash = { success: null, error: null, info: null };

        expect(mountLogin().find('[role="status"]').exists()).toBe(false);
    });
});
