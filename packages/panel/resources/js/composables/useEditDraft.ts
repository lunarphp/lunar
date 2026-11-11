import {
    computed,
    getCurrentInstance,
    getCurrentScope,
    onScopeDispose,
    provide,
    reactive,
    ref,
    watch,
    type ComputedRef,
    type Ref,
} from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { clone, encode } from './formValues';
import { sliceFormKey } from './sliceForm';
import { useI18n } from 'vue-i18n';
import { DraftConflictError, ValidationError, http, type DraftConflict } from '../lib/http';

export interface DraftState {
    data: Record<string, unknown>;
    updated_at: string | null;
}

export interface EditDraftOptions<T extends Record<string, unknown>> {
    initial: T;
    draft: DraftState | null;
    urls: {
        draft: string;
        commit: string;
    };
    debounceMs?: number;
    /**
     * Seed the form with the page's shared `formSliceValues` prop (the
     * prefixed current values of every form slice on the route's deepest
     * record). Off for a page that drafts some other record.
     */
    slices?: boolean;
}

export interface EditDraftForm<T extends Record<string, unknown>> {
    values: T;
    errors: Ref<Record<string, string>>;
    conflicts: Ref<DraftConflict[]>;
    isDirty: ComputedRef<boolean>;
    /** The keys whose value differs from pristine. */
    dirtyKeys: ComputedRef<string[]>;
    saving: Ref<boolean>;
    committing: Ref<boolean>;
    savedAt: Ref<string | null>;
    hasDraft: Ref<boolean>;
    restoredFrom: Ref<string | null>;
    commit: () => Promise<boolean>;
    resolve: (resolutions: Record<string, unknown>, rebase: Record<string, unknown>) => Promise<boolean>;
    discard: () => Promise<void>;
}

// The shared prop is absent outside an Inertia page (unit tests, tooling).
function sharedSliceValues(): Record<string, unknown> {
    try {
        return (usePage().props.formSliceValues as Record<string, unknown> | undefined) ?? {};
    } catch {
        return {};
    }
}

// The server stores empty nullable text fields as null; string-bound inputs
// need '' back, or overlaying a draft would fake dirtiness against a
// ''-shaped pristine value.
function coerceToShape(value: unknown, reference: unknown): unknown {
    return value === null && typeof reference === 'string' ? '' : value;
}

/**
 * Drives a draft-backed edit form: values overlay the staff member's stored
 * draft, dirty fields autosave (debounced, serialised so a stale response
 * never lands after a newer one), and commit() surfaces per-field conflicts
 * for the resolution dialog instead of failing the whole save.
 */
export function useEditDraft<T extends Record<string, unknown>>(options: EditDraftOptions<T>): EditDraftForm<T> {
    const debounceMs = options.debounceMs ?? 750;

    // Slice values seed alongside the page's own initial values so their
    // keys autosave, restore, diff and guard like any other; the page's
    // explicit initial wins where both name a key.
    const initial = { ...(options.slices === false ? {} : sharedSliceValues()), ...options.initial } as T;

    // Reactive so isDirty recomputes when a successful commit re-baselines
    // pristine to the committed values — a plain object would leave the stale
    // dirty state cached until the next keystroke.
    const pristine = reactive<Record<string, unknown>>(clone(initial));
    const values = reactive(clone(initial)) as T;

    for (const [key, value] of Object.entries(options.draft?.data ?? {})) {
        if (key in values) {
            (values as Record<string, unknown>)[key] = coerceToShape(clone(value), pristine[key]);
        }
    }

    const errors = ref<Record<string, string>>({});
    const conflicts = ref<DraftConflict[]>([]);
    const saving = ref(false);
    const committing = ref(false);
    const savedAt = ref<string | null>(options.draft?.updated_at ?? null);
    const hasDraft = ref(options.draft !== null);
    const restoredFrom = ref<string | null>(options.draft?.updated_at ?? null);

    const diff = (): Record<string, unknown> => {
        const changed: Record<string, unknown> = {};

        for (const key of Object.keys(pristine)) {
            const value = (values as Record<string, unknown>)[key];

            if (encode(value) !== encode(pristine[key])) {
                changed[key] = value;
            }
        }

        return changed;
    };

    const dirtyKeys = computed(() => Object.keys(diff()));
    const isDirty = computed(() => dirtyKeys.value.length > 0);

    // Leaving the page with uncommitted changes prompts first — the edits
    // survive as a draft, but staff shouldn't navigate away believing they
    // went live. Same-page visits (partial reloads, widget posts) pass
    // through untouched. Outside a component (unit tests) t() falls back to
    // the raw key.
    const t = getCurrentInstance() ? useI18n().t : (key: string): string => key;

    const unlistenNavigationGuard = router.on('before', (event) => {
        const { url, method, prefetch } = event.detail.visit;

        // Prefetch visits (sidebar <Link prefetch> on hover) fire the same
        // 'before' event but never leave the page, so they must not prompt.
        if (!isDirty.value || prefetch || method !== 'get' || url.pathname === window.location.pathname) {
            return;
        }

        if (!window.confirm(t('drafts.leave_confirm'))) {
            event.preventDefault();
        }
    });

    // Hard navigation (close tab, external link, refresh) gets the browser's
    // native prompt; it also covers edits still inside the autosave debounce,
    // which a draft would otherwise lose.
    const onBeforeUnload = (event: BeforeUnloadEvent): void => {
        if (isDirty.value) {
            event.preventDefault();
        }
    };

    window.addEventListener('beforeunload', onBeforeUnload);

    if (getCurrentScope()) {
        onScopeDispose(() => {
            unlistenNavigationGuard();
            window.removeEventListener('beforeunload', onBeforeUnload);
        });
    }

    // In-flight requests chain so responses apply in request order.
    let queue: Promise<unknown> = Promise.resolve();
    const enqueue = <R>(task: () => Promise<R>): Promise<R> => {
        const run = queue.then(task, task);
        queue = run.catch(() => undefined);

        return run;
    };

    let timer: ReturnType<typeof setTimeout> | null = null;
    const cancelPendingAutosave = (): void => {
        if (timer !== null) {
            clearTimeout(timer);
            timer = null;
        }
    };

    const autosave = (): Promise<void> =>
        enqueue(async () => {
            const changed = diff();

            if (Object.keys(changed).length === 0) {
                if (!hasDraft.value) {
                    return;
                }

                saving.value = true;

                try {
                    await http.delete(options.urls.draft);
                    hasDraft.value = false;
                    savedAt.value = null;
                } finally {
                    saving.value = false;
                }

                return;
            }

            saving.value = true;

            try {
                const response = await http.patch<{ data: Record<string, unknown>; updated_at: string | null }>(
                    options.urls.draft,
                    { data: changed },
                );
                hasDraft.value = true;
                savedAt.value = response.updated_at;
            } finally {
                saving.value = false;
            }
        });

    watch(
        values,
        () => {
            cancelPendingAutosave();
            timer = setTimeout(() => {
                timer = null;
                autosave().catch(() => undefined);
            }, debounceMs);
        },
        { deep: true },
    );

    const send = async (rebase: Record<string, unknown>): Promise<boolean> => {
        cancelPendingAutosave();
        committing.value = true;
        errors.value = {};

        try {
            await enqueue(() => http.post(options.urls.commit, { data: diff(), rebase }));

            conflicts.value = [];
            hasDraft.value = false;
            savedAt.value = null;
            restoredFrom.value = null;
            Object.assign(pristine, clone(values));

            // reload() preserves scroll and state; it refreshes props and
            // surfaces the session flash the commit endpoint set.
            router.reload();

            return true;
        } catch (error) {
            if (error instanceof DraftConflictError) {
                conflicts.value = error.conflicts;

                return false;
            }

            if (error instanceof ValidationError) {
                errors.value = Object.fromEntries(
                    Object.entries(error.errors).map(([key, messages]) => [key, messages[0]]),
                );

                return false;
            }

            throw error;
        } finally {
            committing.value = false;
        }
    };

    const commit = (): Promise<boolean> => send({});

    const resolve = (resolutions: Record<string, unknown>, rebase: Record<string, unknown>): Promise<boolean> => {
        for (const [key, value] of Object.entries(resolutions)) {
            if (key in values) {
                (values as Record<string, unknown>)[key] = coerceToShape(clone(value), pristine[key]);
            }
        }

        return send(rebase);
    };

    const discard = async (): Promise<void> => {
        cancelPendingAutosave();

        for (const key of Object.keys(pristine)) {
            (values as Record<string, unknown>)[key] = clone(pristine[key]);
        }

        await enqueue(() => http.delete(options.urls.draft));

        hasDraft.value = false;
        savedAt.value = null;
        restoredFrom.value = null;
        errors.value = {};
        conflicts.value = [];
    };

    const form: EditDraftForm<T> = {
        values,
        errors,
        conflicts,
        isDirty,
        dirtyKeys,
        saving,
        committing,
        savedAt,
        hasDraft,
        restoredFrom,
        commit,
        resolve,
        discard,
    };

    // Components further down the tree (first-party cards, add-on slot
    // components) bind form slices to this form through useFormSlice().
    if (getCurrentInstance()) {
        provide(sliceFormKey, form);
    }

    return form;
}
