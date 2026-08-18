import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import { DraftConflictError, ValidationError } from '../lib/http';
import { useEditDraft } from './useEditDraft';

const { httpMock, reloadMock, routerOnMock } = vi.hoisted(() => ({
    httpMock: {
        patch: vi.fn(),
        post: vi.fn(),
        delete: vi.fn(),
    },
    reloadMock: vi.fn(),
    routerOnMock: vi.fn((_event: string, _handler: unknown) => () => {}),
}));

vi.mock('../lib/http', async (importOriginal) => ({
    ...(await importOriginal<typeof import('../lib/http')>()),
    http: httpMock,
}));

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: reloadMock, on: routerOnMock },
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
        httpMock.patch.mockResolvedValue({ data: {}, updated_at: '2026-07-17T00:00:00Z' });
        httpMock.post.mockResolvedValue({ committed: true });
        httpMock.delete.mockResolvedValue(null);
    });

    afterEach(() => {
        vi.clearAllMocks();
        vi.useRealTimers();
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
        expect(httpMock.patch).toHaveBeenCalledWith(urls.draft, { data: { first_name: 'Changed' } });
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
        expect(httpMock.patch).toHaveBeenCalledWith(urls.draft, { data: { first_name: 'Ch' } });
    });

    it('deletes the draft when the form returns to clean', async () => {
        const form = useEditDraft({
            initial: { first_name: 'Original' },
            draft: { data: { first_name: 'Drafted' }, updated_at: null },
            urls,
        });

        form.values.first_name = 'Original';
        await flushAutosave();

        expect(httpMock.delete).toHaveBeenCalledWith(urls.draft);
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
            rebase: {},
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
            rebase: { first_name: 'Theirs' },
        });
    });

    it('discard resets values and deletes the server draft', async () => {
        const form = useEditDraft({
            initial: { first_name: 'Original' },
            draft: { data: { first_name: 'Drafted' }, updated_at: '2026-07-16T00:00:00Z' },
            urls,
        });

        await form.discard();

        expect(form.values.first_name).toBe('Original');
        expect(httpMock.delete).toHaveBeenCalledWith(urls.draft);
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
