import { afterEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { useEditDraft, type EditDraftForm } from '../composables/useEditDraft';
import UrlSlugs from './UrlSlugs.vue';

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: { patch: vi.fn(), post: vi.fn(), delete: vi.fn() },
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: vi.fn(), on: vi.fn(() => () => {}) },
    usePage: () => ({ props: {} }),
}));

const languages = [
    { id: 1, code: 'en', name: 'English', default: true },
    { id: 2, code: 'de', name: 'German', default: false },
];

function mountSlugs(initial: Record<string, unknown>) {
    let form: EditDraftForm<Record<string, unknown>> | undefined;

    const Page = defineComponent({
        setup() {
            form = useEditDraft({ initial, draft: null, urls: { draft: '/b/1/draft', commit: '/b/1/draft/commit' } });

            return () => h(UrlSlugs, { languages, pathPrefix: '/brands/', storefrontUrl: 'https://shop.test' });
        },
    });

    return { wrapper: mount(Page, { attachTo: document.body }), form: form! };
}

const stark = { id: 10, slug: 'stark', default: true };
const alias = { id: 11, slug: 'stark-industries', default: false };

describe('UrlSlugs', () => {
    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('renders a row per drafted slug with a storefront preview link', () => {
        const { wrapper } = mountSlugs({ 'url:en': [stark], 'url:de': [] });

        expect(wrapper.findAll('tbody tr')).toHaveLength(1);
        expect(wrapper.find('a[target="_blank"]').attributes('href')).toBe('https://shop.test/brands/stark');
    });

    it('edits a slug into the draft', async () => {
        const { wrapper, form } = mountSlugs({ 'url:en': [stark], 'url:de': [] });

        await wrapper.find('input[aria-label="en slug"]').setValue('stark-hq');

        expect(form.values['url:en']).toEqual([{ ...stark, slug: 'stark-hq' }]);
        expect(form.dirtyKeys.value).toEqual(['url:en']);
    });

    it('moves the default within the language only', async () => {
        const german = { id: 12, slug: 'stark-de', default: true };
        const { wrapper, form } = mountSlugs({ 'url:en': [stark, alias], 'url:de': [german] });

        await wrapper.findAll('button[aria-pressed]')[1].trigger('click');

        expect(form.values['url:en']).toEqual([{ ...stark, default: false }, { ...alias, default: true }]);
        expect(form.values['url:de']).toEqual([german]);
    });

    it('hands the default to the next slug when the default is removed', async () => {
        const { wrapper, form } = mountSlugs({ 'url:en': [stark, alias], 'url:de': [] });

        await wrapper.findAll('button[aria-label="urls.remove"]')[0].trigger('click');

        expect(form.values['url:en']).toEqual([{ ...alias, default: true }]);
    });

    it('adds a new slug without an id, as the default when asked', async () => {
        const { wrapper, form } = mountSlugs({ 'url:en': [stark], 'url:de': [] });

        await wrapper.findAll('button').find((button) => button.text() === 'urls.add_url')!.trigger('click');
        await nextTick();

        const input = document.body.querySelector('#url-slug') as HTMLInputElement;
        input.value = 'iron';
        input.dispatchEvent(new Event('input'));
        (document.body.querySelector('[role="checkbox"], button[aria-label="urls.make_default"]') as HTMLElement).click();
        await nextTick();
        [...document.body.querySelectorAll('button')].find((button) => button.textContent?.trim() === 'urls.add')!.click();
        await nextTick();

        expect(form.values['url:en']).toEqual([{ ...stark, default: false }, { id: null, slug: 'iron', default: true }]);
    });

    it('shows a commit error under the row it belongs to', async () => {
        const { wrapper, form } = mountSlugs({ 'url:en': [stark, alias], 'url:de': [] });

        form.errors.value = { 'url:en.1.slug': 'The slug has already been taken.' };
        await nextTick();

        expect(wrapper.findAll('tbody tr')[1].text()).toContain('The slug has already been taken.');
    });
});
