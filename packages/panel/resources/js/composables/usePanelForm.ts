import { computed, getCurrentInstance, provide, ref, type Ref } from 'vue';
import { useForm, usePage, type InertiaForm } from '@inertiajs/vue3';
import type { FormDataType } from '@inertiajs/core';
import { clone, encode } from './formValues';
import { sliceFormKey, type SliceForm } from './sliceForm';

// The shared prop is absent outside an Inertia page (unit tests, tooling).
function sharedSliceValues(): Record<string, unknown> {
    try {
        return (usePage().props.formSliceValues as Record<string, unknown> | undefined) ?? {};
    } catch {
        return {};
    }
}

/**
 * Inertia's useForm for a plain (non-drafted) panel page, with the page
 * form offered to form slices. A slot component that binds a slice with
 * useFormSlice() claims its namespace: the namespace's keys are seeded from
 * the shared formSliceValues prop into the form's data and defaults, so they
 * post with the form and nothing else changes for a page nobody extends.
 * Returns the Inertia form unchanged, so page code reads as before.
 */
export function usePanelForm<T extends FormDataType<T>>(initial: T): InertiaForm<T> {
    // useForm's precognition constraint is a private type; the data shape is
    // the same one every page already passed to useForm.
    const form = useForm(initial as never) as unknown as InertiaForm<T>;
    const seeds = sharedSliceValues();
    const pristine: Record<string, unknown> = {};
    const claimed = new Set<string>();
    const data = form as unknown as Record<string, unknown>;

    const claim = (namespace: string): void => {
        if (claimed.has(namespace)) {
            return;
        }

        claimed.add(namespace);

        const prefix = `${namespace}:`;

        for (const [key, value] of Object.entries(seeds)) {
            if (!key.startsWith(prefix) || key in data) {
                continue;
            }

            // defaults() is what data() enumerates on submit; the property
            // itself is what the slice proxy reads and writes.
            form.defaults(key as never, clone(value) as never);
            data[key] = clone(value);
            pristine[key] = clone(value);
        }
    };

    const host: SliceForm = {
        values: data,
        errors: computed(() => ({ ...(form.errors as Record<string, string>) })) as unknown as Ref<Record<string, string>>,
        dirtyKeys: computed(() => Object.keys(pristine).filter((key) => encode(data[key]) !== encode(pristine[key]))),
        saving: ref(false),
        committing: computed(() => form.processing) as unknown as Ref<boolean>,
        claim,
    };

    if (getCurrentInstance()) {
        provide(sliceFormKey, host);
    }

    return form;
}
