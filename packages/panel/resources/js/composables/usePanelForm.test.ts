import { afterEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import { usePanelForm } from './usePanelForm';
import { useAddonFormSlice, type FormSlice } from './useFormSlice';

const { pageProps } = vi.hoisted(() => ({ pageProps: {} as Record<string, unknown> }));

vi.mock('@inertiajs/vue3', async (importOriginal) => ({
    ...(await importOriginal<typeof import('@inertiajs/vue3')>()),
    usePage: () => ({ props: pageProps }),
}));

describe('usePanelForm', () => {
    afterEach(() => {
        delete pageProps.formSliceValues;
    });

    it('posts only the page fields until a slice is bound', () => {
        pageProps.formSliceValues = { 'addon:loyalty:tier': 'bronze', 'channel:1': { enabled: true } };

        const form = usePanelForm({ first_name: 'Ada' });

        expect(form.data()).toEqual({ first_name: 'Ada' });
    });

    it('seeds a claimed namespace into the data and defaults, and tracks its dirt', async () => {
        pageProps.formSliceValues = { 'addon:loyalty:tier': 'bronze', 'channel:1': { enabled: true } };

        let slice: FormSlice<{ tier: string }> | undefined;
        let form: ReturnType<typeof usePanelForm<{ first_name: string }>> | undefined;

        const Child = defineComponent({
            setup() {
                slice = useAddonFormSlice<{ tier: string }>('loyalty');

                return () => null;
            },
        });

        const Page = defineComponent({
            setup() {
                form = usePanelForm({ first_name: 'Ada' });

                return () => h('div', [h(Child)]);
            },
        });

        mount(Page);

        expect(form?.data()).toEqual({ first_name: 'Ada', 'addon:loyalty:tier': 'bronze' });
        expect(slice?.values.tier).toBe('bronze');
        expect(slice?.isDirty.value).toBe(false);

        slice!.values.tier = 'gold';

        expect(form?.data()).toEqual({ first_name: 'Ada', 'addon:loyalty:tier': 'gold' });
        expect(slice?.isDirty.value).toBe(true);

        // Inertia recomputes its own isDirty in a watcher.
        await nextTick();
        expect(form?.isDirty).toBe(true);
    });
});
