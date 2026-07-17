import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { computed, nextTick, reactive, ref } from 'vue';
import DraftActions from './DraftActions.vue';
import type { EditDraftForm } from '../composables/useEditDraft';

function fakeForm(overrides: Partial<Record<string, unknown>> = {}): EditDraftForm<Record<string, unknown>> {
    return {
        values: reactive({}),
        errors: ref({}),
        conflicts: ref([]),
        isDirty: computed(() => false),
        saving: ref(false),
        committing: ref(false),
        savedAt: ref<string | null>(null),
        hasDraft: ref(false),
        restoredFrom: ref<string | null>(null),
        commit: vi.fn().mockResolvedValue(true),
        resolve: vi.fn().mockResolvedValue(true),
        discard: vi.fn().mockResolvedValue(undefined),
        ...overrides,
    } as EditDraftForm<Record<string, unknown>>;
}

const buttons = (wrapper: ReturnType<typeof mount>) => wrapper.findAll('button');

describe('DraftActions', () => {
    it('renders nothing while the form is clean', () => {
        const wrapper = mount(DraftActions, { props: { form: fakeForm() } });

        expect(wrapper.find('button').exists()).toBe(false);
    });

    it('shows discard and save once the form is dirty', () => {
        const wrapper = mount(DraftActions, {
            props: { form: fakeForm({ isDirty: computed(() => true) }) },
        });

        const labels = buttons(wrapper).map((button) => button.text());

        expect(labels).toEqual(['drafts.discard', 'common.save_changes']);
    });

    it('stays visible for a stored draft even when local values are clean', () => {
        const wrapper = mount(DraftActions, {
            props: { form: fakeForm({ hasDraft: ref(true) }) },
        });

        expect(buttons(wrapper)).toHaveLength(2);
    });

    it('shows the saving status text while an autosave is in flight', () => {
        const wrapper = mount(DraftActions, {
            props: { form: fakeForm({ isDirty: computed(() => true), saving: ref(true) }) },
        });

        expect(wrapper.find('[role="status"]').text()).toBe('drafts.saving');
    });

    it('shows the saved note after a save lands and clears it after 10 seconds', async () => {
        vi.useFakeTimers();

        try {
            const saving = ref(true);
            const savedAt = ref<string | null>(null);
            const wrapper = mount(DraftActions, {
                props: { form: fakeForm({ isDirty: computed(() => true), saving, savedAt }) },
            });

            saving.value = false;
            savedAt.value = '2026-07-17T00:00:00Z';
            await nextTick();

            expect(wrapper.find('[role="status"]').text()).toBe('drafts.saved');

            await vi.advanceTimersByTimeAsync(10_000);

            expect(wrapper.find('[role="status"]').exists()).toBe(false);
        } finally {
            vi.useRealTimers();
        }
    });

    it('does not show a stale saved note for a restored draft on load', () => {
        const wrapper = mount(DraftActions, {
            props: { form: fakeForm({ hasDraft: ref(true), savedAt: ref('2026-07-16T00:00:00Z') }) },
        });

        expect(wrapper.find('[role="status"]').exists()).toBe(false);
        expect(buttons(wrapper)).toHaveLength(2);
    });

    it('commits on save and discards on discard', async () => {
        const form = fakeForm({ isDirty: computed(() => true) });
        const wrapper = mount(DraftActions, { props: { form } });

        await buttons(wrapper)[1].trigger('click');
        expect(form.commit).toHaveBeenCalledTimes(1);

        await buttons(wrapper)[0].trigger('click');
        expect(form.discard).toHaveBeenCalledTimes(1);
    });

    it('disables both buttons while committing', () => {
        const wrapper = mount(DraftActions, {
            props: { form: fakeForm({ isDirty: computed(() => true), committing: ref(true) }) },
        });

        for (const button of buttons(wrapper)) {
            expect(button.attributes('disabled')).toBeDefined();
        }
    });
});
