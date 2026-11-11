import type { ComputedRef, InjectionKey, Ref } from 'vue';

/**
 * What a form must expose for form slices to bind to it. useEditDraft
 * provides its form under sliceFormKey; a plain page form can provide the
 * same shape.
 */
export interface SliceForm {
    values: Record<string, unknown>;
    errors: Ref<Record<string, string>>;
    dirtyKeys: ComputedRef<string[]>;
    saving: Ref<boolean>;
    committing: Ref<boolean>;
    /**
     * Offered by plain page forms: seed a namespace's keys into the form on
     * first bind, so a page posts only the slices a component actually uses.
     * Drafted pages seed every slice up front and omit it.
     */
    claim?: (namespace: string) => void;
}

export const sliceFormKey: InjectionKey<SliceForm> = Symbol('lunar-panel:slice-form');
