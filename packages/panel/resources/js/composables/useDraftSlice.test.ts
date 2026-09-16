import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { ValidationError } from '../lib/http';
import { useEditDraft, type EditDraftForm } from './useEditDraft';
import { bindDraftSlice, useAddonDraftSlice, useDraftSlice, type DraftSlice } from './useDraftSlice';

const { httpMock, pageProps } = vi.hoisted(() => ({
    httpMock: {
        patch: vi.fn(),
        post: vi.fn(),
        delete: vi.fn(),
    },
    pageProps: {} as Record<string, unknown>,
}));

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: httpMock,
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: vi.fn(), on: vi.fn(() => () => {}) },
    usePage: () => ({ props: pageProps }),
}));

const urls = { draft: '/customers/1/draft', commit: '/customers/1/draft/commit' };

function buildForm(): EditDraftForm<Record<string, unknown>> {
    return useEditDraft({
        initial: { first_name: 'Ada', 'addon:loyalty:tier': 'bronze', 'addon:loyalty:note': '', 'association:up-sell': [1, 2] },
        draft: null,
        urls,
    });
}

describe('bindDraftSlice', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        httpMock.patch.mockResolvedValue({ data: {}, updated_at: null });
        httpMock.post.mockResolvedValue({ committed: true });
    });

    afterEach(() => {
        vi.clearAllMocks();
        vi.useRealTimers();
    });

    it('scopes reads, writes and enumeration to the namespace', () => {
        const form = buildForm();
        const slice = bindDraftSlice<{ tier: string; note: string }>(form, 'addon:loyalty');

        expect(slice.values.tier).toBe('bronze');
        expect(Object.keys(slice.values)).toEqual(['tier', 'note']);
        expect('tier' in slice.values).toBe(true);
        expect((slice.values as Record<string, unknown>).first_name).toBeUndefined();

        slice.values.tier = 'gold';

        expect(form.values['addon:loyalty:tier']).toBe('gold');
        expect(form.dirtyKeys.value).toEqual(['addon:loyalty:tier']);
        expect(slice.isDirty.value).toBe(true);
    });

    it('stays clean while only other namespaces change', () => {
        const form = buildForm();
        const slice = bindDraftSlice(form, 'addon:loyalty');

        form.values.first_name = 'Grace';
        form.values['association:up-sell'] = [2];

        expect(form.isDirty.value).toBe(true);
        expect(slice.isDirty.value).toBe(false);
    });

    it('refuses a field the namespace does not declare', () => {
        const slice = bindDraftSlice(buildForm(), 'addon:loyalty');

        expect(() => {
            (slice.values as Record<string, unknown>).points = 10;
        }).toThrow('Draft slice [addon:loyalty] has no field [points].');
    });

    it('exposes a writable ref per field', async () => {
        const form = buildForm();
        const tier = bindDraftSlice<{ tier: string }>(form, 'addon:loyalty').field('tier');

        expect(tier.value).toBe('bronze');

        tier.value = 'silver';
        await nextTick();

        expect(tier.value).toBe('silver');
        expect(form.values['addon:loyalty:tier']).toBe('silver');
    });

    it('maps commit errors back to bare field names', async () => {
        const form = buildForm();
        const slice = bindDraftSlice(form, 'addon:loyalty');

        httpMock.post.mockRejectedValueOnce(
            new ValidationError({ 'addon:loyalty:tier': ['The tier is invalid.'], first_name: ['Required.'] }),
        );

        await expect(form.commit()).resolves.toBe(false);

        expect(slice.errors.value).toEqual({ tier: 'The tier is invalid.' });
    });
});

describe('useDraftSlice', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });

    function mountWith(child: ReturnType<typeof defineComponent>) {
        const Page = defineComponent({
            setup() {
                useEditDraft({ initial: { 'addon:loyalty:tier': 'bronze', 'association:up-sell': [] }, draft: null, urls });

                return () => h('div', [h(child)]);
            },
        });

        return mount(Page);
    }

    it('injects the enclosing page form and prefixes an add-on key', () => {
        let slice: DraftSlice<{ tier: string }> | undefined;

        const Child = defineComponent({
            setup() {
                slice = useAddonDraftSlice<{ tier: string }>('loyalty');

                return () => h('span', slice?.values.tier);
            },
        });

        const wrapper = mountWith(Child);

        expect(wrapper.text()).toBe('bronze');
        expect(slice?.values.tier).toBe('bronze');
    });

    it('binds a first-party namespace as given', () => {
        let keys: string[] = [];

        const Child = defineComponent({
            setup() {
                keys = Object.keys(useDraftSlice('association').values);

                return () => null;
            },
        });

        mountWith(Child);

        expect(keys).toEqual(['up-sell']);
    });

    it('throws a descriptive error outside a draft-backed page', () => {
        const Orphan = defineComponent({
            setup() {
                useAddonDraftSlice('loyalty');

                return () => null;
            },
        });

        expect(() => mount(Orphan)).toThrow("useDraftSlice('addon:loyalty') needs a page driven by useEditDraft");
    });
});
