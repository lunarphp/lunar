import { computed, inject, type ComputedRef, type Ref, type WritableComputedRef } from 'vue';
import { sliceFormKey, type SliceForm } from './sliceForm';

/**
 * A namespaced view onto the page's form. Reads and writes go to the form's
 * `{namespace}:{field}` keys, so the slice's fields validate and commit with
 * the form's own (and autosave, restore and conflict-check on a drafted
 * page); nothing here reaches another namespace or the underlying form.
 */
export interface FormSlice<T extends Record<string, unknown> = Record<string, unknown>> {
    /** Reactive values scoped to the namespace; `v-model="slice.values.tier"` works. */
    values: T;
    /** Commit-time validation errors for this namespace, keyed by bare field. */
    errors: ComputedRef<Record<string, string>>;
    /** A writable ref for one field. */
    field: <K extends keyof T & string>(name: K) => WritableComputedRef<T[K]>;
    /** Whether any of the namespace's fields differ from their pristine value. */
    isDirty: ComputedRef<boolean>;
    saving: Ref<boolean>;
    committing: Ref<boolean>;
}

/**
 * Scope a form to one namespace. Exported for the composable's tests; page
 * code goes through useFormSlice() / useAddonFormSlice().
 */
export function bindFormSlice<T extends Record<string, unknown>>(form: SliceForm, namespace: string): FormSlice<T> {
    const prefix = `${namespace}:`;
    const target = form.values;

    const known = (prop: string | symbol): prop is string => typeof prop === 'string' && `${prefix}${prop}` in target;

    // A Proxy rather than a copy: reads track the form's reactive values and
    // writes land on them, so autosave and dirty state see the change.
    const values = new Proxy({} as T, {
        get: (_, prop) => (known(prop) ? target[`${prefix}${prop}`] : undefined),
        set: (_, prop, value) => {
            if (!known(prop)) {
                throw new Error(`Form slice [${namespace}] has no field [${String(prop)}].`);
            }

            target[`${prefix}${prop}`] = value;

            return true;
        },
        has: (_, prop) => known(prop),
        ownKeys: () =>
            Object.keys(target)
                .filter((key) => key.startsWith(prefix))
                .map((key) => key.slice(prefix.length)),
        getOwnPropertyDescriptor: (_, prop) =>
            known(prop)
                ? { enumerable: true, configurable: true, writable: true, value: target[`${prefix}${prop}`] }
                : undefined,
    });

    const errors = computed<Record<string, string>>(() =>
        Object.fromEntries(
            Object.entries(form.errors.value)
                .filter(([key]) => key.startsWith(prefix))
                .map(([key, message]) => [key.slice(prefix.length), message]),
        ),
    );

    const isDirty = computed(() => form.dirtyKeys.value.some((key) => key.startsWith(prefix)));

    const field = <K extends keyof T & string>(name: K): WritableComputedRef<T[K]> =>
        computed({
            get: () => values[name],
            set: (value: T[K]) => {
                values[name] = value;
            },
        });

    return { values, errors, field, isDirty, saving: form.saving, committing: form.committing };
}

function injectForm(namespace: string): SliceForm {
    const form = inject(sliceFormKey, null);

    if (!form) {
        throw new Error(`useFormSlice('${namespace}') needs a page form (useEditDraft) above it in the component tree.`);
    }

    return form;
}

/**
 * Bind to a first-party slice by its bare namespace, e.g. `association`.
 */
export function useFormSlice<T extends Record<string, unknown> = Record<string, unknown>>(namespace: string): FormSlice<T> {
    return bindFormSlice<T>(injectForm(namespace), namespace);
}

/**
 * Bind to an add-on slice by its key. Slices registered through
 * Section::formExtensions() live under `addon:{key}`, and this is the form
 * `@lunarphp/panel` exports as useFormSlice, so an add-on component only
 * ever names its own key.
 */
export function useAddonFormSlice<T extends Record<string, unknown> = Record<string, unknown>>(key: string): FormSlice<T> {
    return useFormSlice<T>(`addon:${key}`);
}
