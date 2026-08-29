import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import UrlSlugs from './UrlSlugs.vue';

vi.mock('@inertiajs/vue3', () => ({
    router: {
        put: vi.fn(),
        post: vi.fn(),
        delete: vi.fn(),
    },
}));

import { router } from '@inertiajs/vue3';

const languages = [
    { id: 1, code: 'en', name: 'English', default: true },
    { id: 2, code: 'de', name: 'German', default: false },
];

const urls = [
    {
        id: 10,
        slug: 'stark',
        default: true,
        language_id: 1,
        language_code: 'en',
        update_url: '/panel/brands/1/urls/10',
        destroy_url: '/panel/brands/1/urls/10',
    },
];

const mountComponent = () =>
    mount(UrlSlugs, {
        props: {
            urls,
            languages,
            storeUrl: '/panel/brands/1/urls',
            pathPrefix: '/brands/',
            storefrontUrl: 'https://shop.test',
        },
        attachTo: document.body,
    });

describe('UrlSlugs', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders a row per url with a storefront preview link', () => {
        const wrapper = mountComponent();

        expect(wrapper.findAll('tbody tr')).toHaveLength(1);
        expect(wrapper.find('a[target="_blank"]').attributes('href')).toBe('https://shop.test/brands/stark');
    });

    it('debounces slug edits into a PUT', async () => {
        vi.useFakeTimers();

        const wrapper = mountComponent();

        await wrapper.find('tbody input').setValue('stark-industries');

        expect(router.put).not.toHaveBeenCalled();

        vi.advanceTimersByTime(700);

        expect(router.put).toHaveBeenCalledWith(
            '/panel/brands/1/urls/10',
            { language_id: 1, slug: 'stark-industries', default: true },
            expect.anything(),
        );

        vi.useRealTimers();
    });

    it('adds a slug for any language through the modal', async () => {
        const wrapper = mountComponent();

        await wrapper
            .findAll('button')
            .find((button) => button.text().includes('urls.add_url'))!
            .trigger('click');
        // Reka's DialogPortal teleports content to document.body on the next tick.
        await nextTick();

        const dialog = document.body;
        const select = dialog.querySelector('select') as HTMLSelectElement;

        // All languages stay addable: an element may carry several slugs in
        // the same language (alias / redirect slugs).
        expect(select.querySelectorAll('option')).toHaveLength(2);

        select.value = '2';
        select.dispatchEvent(new Event('change'));

        const slug = dialog.querySelector('input#url-slug') as HTMLInputElement;
        slug.value = 'stark-de';
        slug.dispatchEvent(new Event('input'));
        await nextTick();

        const add = [...dialog.querySelectorAll('button')].find((button) => button.textContent?.trim() === 'urls.add');
        add!.click();

        expect(router.post).toHaveBeenCalledWith(
            '/panel/brands/1/urls',
            { language_id: 2, slug: 'stark-de', default: false },
            expect.anything(),
        );
    });
});
