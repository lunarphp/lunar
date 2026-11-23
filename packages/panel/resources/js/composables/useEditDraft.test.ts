import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick, reactive } from 'vue';
import { DraftConflictError, HttpError, StaleDraftError, ValidationError } from '../lib/http';
import { useEditDraft } from './useEditDraft';

const { httpMock, reloadMock, routerOnMock, pageProps, pageState, toastErrorMock } = vi.hoisted(() => ({
    httpMock: {
        patch: vi.fn(),
        post: vi.fn(),
        delete: vi.fn(),
    },
    reloadMock: vi.fn(),
    routerOnMock: vi.fn((_event: string, _handler: unknown) => () => {}),
    pageProps: {} as Record<string, unknown>,
    // Overrides the plain pageProps page with a reactive one for tests that
    // change props after the form is created.
    pageState: { page: null as { props: Record<string, unknown> } | null },
    toastErrorMock: vi.fn(),
}));

vi.mock('./useToasts', () => ({
    useToasts: () => ({ error: toastErrorMock }),
}));

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: httpMock,
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: reloadMock, on: routerOnMock },
    usePage: () => pageState.page ?? { props: pageProps },
}));

const urls = { draft: '/customers/1/draft', commit: '/customers/1/draft/commit' };

async function flushAutosave(): Promise<void> {
    await nextTick();
    await vi.advanceTimersByTimeAsync(750);
    await vi.runOnlyPendingTimersAsync();
}

describe('useEditDraft', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        httpMock.patch.mockResolvedValue({ data: {}, updated_at: '2026-07-17T00:00:00Z', version: 1 });
        httpMock.post.mockResolvedValue({ committed: true, version: 0 });
        httpMock.delete.mockResolvedValue(null);
    });

    afterEach(() => {
        vi.clearAllMocks();
        vi.useRealTimers();
        delete pageProps.formSliceValues;
        pageState.page = null;
        vi.unstubAllGlobals();
    });

    it('seeds draft slice values from the shared page prop', async () => {
        pageProps.formSliceValues = { 'addon:loyalty:tier': 'bronze' };

        const form = useEditDraft({
            initial: { first_name: 'Original' },
            draft: { data: { 'addon:loyalty:tier': 'gold' }, updated_at: null },
            urls,
        });

        const values = form.values as Record<string, unknown>;

        // Restored from the draft because the key was seeded, and diffed
        // against the seeded pristine value.
        expect(values['addon:loyalty:tier']).toBe('gold');
        expect(form.dirtyKeys.value).toEqual(['addon:loyalty:tier']);

        values['addon:loyalty:tier'] = 'bronze';
        await flushAutosave();

        expect(httpMock.delete).toHaveBeenCalledWith(urls.draft, { version: 0 });
    });

    it('skips slice seeding when the page opts out', () => {
        pageProps.formSliceValues = { 'addon:loyalty:tier': 'bronze' };

        const form = useEditDraft({
            initial: { first_name: 'Original' },
            draft: { data: { 'addon:loyalty:tier': 'gold' }, updated_at: null },
            urls,
            slices: false,
        });

        expect('addon:loyalty:tier' in form.values).toBe(false);
        expect(form.isDirty.value).toBe(false);
    });

    it('overlays a stored draft onto the initial values', () => {
        const form = useEditDraft({
            initial: { first_name: 'Original', company_name: 'Acme' },
            draft: { data: { first_name: 'Drafted' }, updated_at: '2026-07-16T00:00:00Z' },
            urls,
        });

        expect(form.values.first_name).toBe('Drafted');
        expect(form.values.company_name).toBe('Acme');
        expect(form.isDirty.value).toBe(true);
        expect(form.hasDraft.value).toBe(true);
        expect(form.restoredFrom.value).toBe('2026-07-16T00:00:00Z');
    });

    it('autosaves only the dirty diff after the debounce', async () => {
        const form = useEditDraft({
            initial: { first_name: 'Original', company_name: 'Acme' },
            draft: null,
            urls,
        });

        form.values.first_name = 'Changed';
        await flushAutosave();

        expect(httpMock.patch).toHaveBeenCalledTimes(1);
        // The base is what the field showed before the edit.
        expect(httpMock.patch).toHaveBeenCalledWith(urls.draft, {
            data: { first_name: 'Changed' },
            base: { first_name: 'Original' },
            version: 0,
        });
        expect(form.hasDraft.value).toBe(true);
        expect(form.savedAt.value).toBe('2026-07-17T00:00:00Z');
    });

    it('collapses rapid edits into one autosave', async () => {
        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });

        form.values.first_name = 'C';
        await nextTick();
        await vi.advanceTimersByTimeAsync(300);
        form.values.first_name = 'Ch';
        await flushAutosave();

        expect(httpMock.patch).toHaveBeenCalledTimes(1);
        expect(httpMock.patch).toHaveBeenCalledWith(urls.draft, {
            data: { first_name: 'Ch' },
            base: { first_name: 'Original' },
            version: 0,
        });
    });

    it('deletes the draft when the form returns to clean', async () => {
        const form = useEditDraft({
            initial: { first_name: 'Original' },
            draft: { data: { first_name: 'Drafted' }, updated_at: null, version: 3 },
            urls,
        });

        form.values.first_name = 'Original';
        await flushAutosave();

        expect(httpMock.delete).toHaveBeenCalledWith(urls.draft, { version: 3 });
        expect(httpMock.patch).not.toHaveBeenCalled();
        expect(form.hasDraft.value).toBe(false);
    });

    it('treats reordered array values as clean', async () => {
        const form = useEditDraft({
            initial: { customer_group_ids: [1, 2] },
            draft: null,
            urls,
        });

        form.values.customer_group_ids = [1, 2];
        await flushAutosave();

        expect(httpMock.patch).not.toHaveBeenCalled();
        expect(form.isDirty.value).toBe(false);
    });

    it('commits the current diff and reloads on success', async () => {
        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });

        form.values.first_name = 'Changed';

        // Read (and cache) the computed before committing: the commit must
        // invalidate it when it re-baselines pristine, not leave it stale.
        expect(form.isDirty.value).toBe(true);

        await expect(form.commit()).resolves.toBe(true);

        expect(httpMock.post).toHaveBeenCalledWith(urls.commit, {
            data: { first_name: 'Changed' },
            base: { first_name: 'Original' },
            rebase: {},
            version: 0,
        });
        expect(reloadMock).toHaveBeenCalled();
        expect(form.hasDraft.value).toBe(false);
        expect(form.isDirty.value).toBe(false);
    });

    it('cancels a pending autosave when committing', async () => {
        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });

        form.values.first_name = 'Changed';
        await nextTick();

        await form.commit();
        await vi.runOnlyPendingTimersAsync();

        expect(httpMock.patch).not.toHaveBeenCalled();
    });

    it('captures conflicts from a 409 without reloading', async () => {
        const conflicts = [{ key: 'first_name', label: 'First name', mine: 'Mine', base: 'Base', theirs: 'Theirs' }];
        httpMock.post.mockRejectedValue(new DraftConflictError(conflicts));

        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
        form.values.first_name = 'Mine';

        await expect(form.commit()).resolves.toBe(false);

        expect(form.conflicts.value).toEqual(conflicts);
        expect(reloadMock).not.toHaveBeenCalled();
    });

    it('captures field errors from a 422', async () => {
        httpMock.post.mockRejectedValue(new ValidationError({ first_name: ['Required.', 'Second message.'] }));

        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
        form.values.first_name = '';

        await expect(form.commit()).resolves.toBe(false);

        expect(form.errors.value).toEqual({ first_name: 'Required.' });
    });

    it('applies resolutions and re-commits with the rebase payload', async () => {
        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
        form.values.first_name = 'Mine';

        await expect(form.resolve({ first_name: 'Theirs' }, { first_name: 'Theirs' })).resolves.toBe(true);

        expect(form.values.first_name).toBe('Theirs');
        expect(httpMock.post).toHaveBeenCalledWith(urls.commit, {
            data: { first_name: 'Theirs' },
            base: { first_name: 'Original' },
            rebase: { first_name: 'Theirs' },
            version: 0,
        });
    });

    it('discard resets values and deletes the server draft', async () => {
        const form = useEditDraft({
            initial: { first_name: 'Original' },
            draft: { data: { first_name: 'Drafted' }, updated_at: '2026-07-16T00:00:00Z', version: 2 },
            urls,
        });

        await form.discard();

        expect(form.values.first_name).toBe('Original');
        expect(httpMock.delete).toHaveBeenCalledWith(urls.draft, { version: 2 });
        expect(form.hasDraft.value).toBe(false);
        expect(form.restoredFrom.value).toBeNull();
        expect(form.isDirty.value).toBe(false);
    });

    it('serialises in-flight requests so responses apply in order', async () => {
        const order: string[] = [];
        let releaseFirst!: () => void;

        httpMock.patch
            .mockImplementationOnce(async () => {
                await new Promise<void>((resolvePromise) => {
                    releaseFirst = resolvePromise;
                });
                order.push('first');

                return { data: {}, updated_at: 'first' };
            })
            .mockImplementationOnce(async () => {
                order.push('second');

                return { data: {}, updated_at: 'second' };
            });

        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });

        form.values.first_name = 'One';
        await flushAutosave();

        form.values.first_name = 'Two';
        await flushAutosave();

        releaseFirst();
        await vi.runAllTimersAsync();

        expect(order).toEqual(['first', 'second']);
        expect(form.savedAt.value).toBe('second');
    });

    it('keeps edits typed while a commit is in flight dirty, and autosaves them', async () => {
        let releaseCommit!: () => void;
        httpMock.post.mockImplementationOnce(
            () =>
                new Promise((resolvePromise) => {
                    releaseCommit = () => resolvePromise({ committed: true, version: 0 });
                }),
        );

        const form = useEditDraft({ initial: { first_name: 'Original', company_name: 'Acme' }, draft: null, urls });
        form.values.first_name = 'Sent';

        const committed = form.commit();
        await nextTick();

        // Typed after Save was clicked: not part of the commit.
        form.values.company_name = 'Typed during save';
        await nextTick();

        releaseCommit();
        await expect(committed).resolves.toBe(true);

        expect(form.dirtyKeys.value).toEqual(['company_name']);

        await flushAutosave();

        expect(httpMock.patch).toHaveBeenCalledWith(urls.draft, {
            data: { company_name: 'Typed during save' },
            base: { company_name: 'Acme' },
            version: 0,
        });
    });

    it('tracks the server draft a refused commit leaves behind', async () => {
        httpMock.post.mockRejectedValue(new ValidationError({ first_name: ['Required.'] }, 4));

        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
        form.values.first_name = '';

        await form.commit();

        expect(form.hasDraft.value).toBe(true);

        // Reverting by hand must now remove the server draft, at its version.
        form.values.first_name = 'Original';
        await flushAutosave();

        expect(httpMock.delete).toHaveBeenCalledWith(urls.draft, { version: 4 });
    });

    it('records the draft version a conflict response carries', async () => {
        const conflicts = [{ key: 'first_name', label: 'First name', mine: 'Mine', base: 'Base', theirs: 'Theirs' }];
        httpMock.post.mockRejectedValueOnce(new DraftConflictError(conflicts, 5));

        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
        form.values.first_name = 'Mine';

        await form.commit();
        await form.resolve({ first_name: 'Mine' }, { first_name: 'Theirs' });

        expect(form.hasDraft.value).toBe(false);
        expect(httpMock.post).toHaveBeenLastCalledWith(urls.commit, expect.objectContaining({ version: 5 }));
    });

    it('shows an error instead of throwing when a commit fails in transport', async () => {
        httpMock.post.mockRejectedValue(new HttpError(500));

        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
        form.values.first_name = 'Changed';

        await expect(form.commit()).resolves.toBe(false);

        expect(toastErrorMock).toHaveBeenCalledWith('drafts.save_failed');
        expect(form.isDirty.value).toBe(true);
    });

    it('shows an error and stays dirty when an autosave fails', async () => {
        httpMock.patch.mockRejectedValue(new HttpError(419));

        const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
        form.values.first_name = 'Changed';
        await flushAutosave();

        expect(toastErrorMock).toHaveBeenCalledWith('drafts.autosave_failed');
        expect(form.isDirty.value).toBe(true);
    });

    describe('stale draft', () => {
        it('reloads when the staff member accepts', async () => {
            const reloadPage = vi.fn();
            vi.stubGlobal('confirm', vi.fn().mockReturnValue(true));
            vi.stubGlobal('location', { ...window.location, reload: reloadPage });
            httpMock.patch.mockRejectedValue(new StaleDraftError(7));

            const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
            form.values.first_name = 'Changed';
            await flushAutosave();

            expect(reloadPage).toHaveBeenCalled();
        });

        it('continues against the newer version when the staff member declines', async () => {
            vi.stubGlobal('confirm', vi.fn().mockReturnValue(false));
            httpMock.patch.mockRejectedValueOnce(new StaleDraftError(7));

            const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
            form.values.first_name = 'Changed';
            await flushAutosave();

            form.values.first_name = 'Changed again';
            await flushAutosave();

            expect(httpMock.patch).toHaveBeenLastCalledWith(urls.draft, expect.objectContaining({ version: 7 }));
            expect(toastErrorMock).not.toHaveBeenCalled();
        });
    });

    describe('slice key set', () => {
        it('adopts slice keys a structural operation adds, with their server values as pristine', async () => {
            pageState.page = reactive({ props: { formSliceValues: {} as Record<string, unknown> } });

            const form = useEditDraft({ initial: { name: 'Mug' }, draft: null, urls });
            const values = form.values as Record<string, unknown>;

            pageState.page.props.formSliceValues = { 'variant:sku': 'MUG-1' };
            await nextTick();

            expect(values['variant:sku']).toBe('MUG-1');
            expect(form.isDirty.value).toBe(false);

            values['variant:sku'] = 'MUG-2';
            await flushAutosave();

            expect(httpMock.patch).toHaveBeenCalledWith(urls.draft, {
                data: { 'variant:sku': 'MUG-2' },
                base: { 'variant:sku': 'MUG-1' },
                version: 0,
            });
        });

        it('drops removed slice keys from the form and the stored draft', async () => {
            pageState.page = reactive({ props: { formSliceValues: { 'variant:sku': 'MUG-1' } as Record<string, unknown> } });

            const form = useEditDraft({
                initial: { name: 'Mug' },
                draft: { data: { 'variant:sku': 'MUG-2' }, updated_at: null, version: 1 },
                urls,
            });

            pageState.page.props.formSliceValues = {};
            await nextTick();

            expect('variant:sku' in form.values).toBe(false);
            expect(form.isDirty.value).toBe(false);

            await flushAutosave();

            expect(httpMock.delete).toHaveBeenCalledWith(urls.draft, { version: 1 });
        });

        it('moves untouched slice keys to rewritten server values and keeps edited ones', async () => {
            pageState.page = reactive({ props: { formSliceValues: { 'price:GBP': [1000], 'price:JPY': [1500] } as Record<string, unknown> } });

            const form = useEditDraft({ initial: { name: 'Mug' }, draft: null, urls });
            const values = form.values as Record<string, unknown>;

            values['price:JPY'] = [1600];

            pageState.page.props.formSliceValues = { 'price:GBP': [900], 'price:JPY': [1400] };
            await nextTick();

            expect(values['price:GBP']).toEqual([900]);
            expect(values['price:JPY']).toEqual([1600]);
            expect(form.dirtyKeys.value).toEqual(['price:JPY']);
        });

        it('leaves the page\'s own keys alone when the slice prop changes', async () => {
            pageState.page = reactive({ props: { formSliceValues: { name: 'from slice' } as Record<string, unknown> } });

            const form = useEditDraft({ initial: { name: 'Mug' }, draft: null, urls });

            pageState.page.props.formSliceValues = {};
            await nextTick();

            expect(form.values.name).toBe('Mug');
        });
    });

    describe('navigation guard', () => {
        type BeforeHandler = (event: {
            detail: { visit: { url: URL; method: string; prefetch: boolean } };
            preventDefault: () => void;
        }) => void;

        const guardEvent = (path: string, method = 'get', prefetch = false) => ({
            detail: { visit: { url: new URL(path, window.location.origin), method, prefetch } },
            preventDefault: vi.fn(),
        });

        const lastGuard = (): BeforeHandler => routerOnMock.mock.calls.at(-1)![1] as BeforeHandler;

        afterEach(() => {
            vi.unstubAllGlobals();
        });

        it('prompts before navigating away with uncommitted changes and cancels on decline', () => {
            const confirmMock = vi.fn().mockReturnValue(false);
            vi.stubGlobal('confirm', confirmMock);

            const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
            form.values.first_name = 'Changed';

            const event = guardEvent('/somewhere-else');
            lastGuard()(event);

            expect(confirmMock).toHaveBeenCalledWith('drafts.leave_confirm');
            expect(event.preventDefault).toHaveBeenCalled();
        });

        it('allows navigation when the prompt is accepted', () => {
            vi.stubGlobal('confirm', vi.fn().mockReturnValue(true));

            const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
            form.values.first_name = 'Changed';

            const event = guardEvent('/somewhere-else');
            lastGuard()(event);

            expect(event.preventDefault).not.toHaveBeenCalled();
        });

        it('never prompts when clean, for same-page visits, or non-GET actions', () => {
            const confirmMock = vi.fn();
            vi.stubGlobal('confirm', confirmMock);

            const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });

            lastGuard()(guardEvent('/somewhere-else'));

            form.values.first_name = 'Changed';

            lastGuard()(guardEvent(window.location.pathname));
            lastGuard()(guardEvent('/somewhere-else', 'post'));

            expect(confirmMock).not.toHaveBeenCalled();
        });

        it('never prompts for prefetch visits (sidebar link hover)', () => {
            const confirmMock = vi.fn();
            vi.stubGlobal('confirm', confirmMock);

            const form = useEditDraft({ initial: { first_name: 'Original' }, draft: null, urls });
            form.values.first_name = 'Changed';

            lastGuard()(guardEvent('/somewhere-else', 'get', true));

            expect(confirmMock).not.toHaveBeenCalled();
        });
    });
});
