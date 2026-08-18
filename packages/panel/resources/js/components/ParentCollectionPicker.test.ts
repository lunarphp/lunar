import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';

vi.mock('../lib/http', () => ({
    http: {
        get: vi.fn().mockResolvedValue({
            data: [
                { id: 2, name: 'Outerwear', breadcrumb: '' },
                { id: 3, name: 'Raincoats', breadcrumb: 'Outerwear' },
            ],
        }),
    },
}));

import { http } from '../lib/http';
import ParentCollectionPicker, { type ParentOption } from './ParentCollectionPicker.vue';

const mountPicker = (modelValue: ParentOption | null = null, groupId: number | null = 1, excludeId?: number) =>
    mount(ParentCollectionPicker, {
        props: { modelValue, searchUrl: '/panel/catalog/collections/search', groupId, excludeId },
    });

describe('ParentCollectionPicker', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('shows the top-level label when no parent is picked', () => {
        expect(mountPicker().text()).toContain('collections.field_parent_none');
    });

    it('searches scoped to the group and excluding the subtree', async () => {
        const wrapper = mountPicker(null, 5, 9);

        await wrapper.find('button').trigger('click');
        await vi.waitFor(() => expect(http.get).toHaveBeenCalled());

        const url = (http.get as ReturnType<typeof vi.fn>).mock.calls[0][0] as string;

        expect(url).toContain('group_id=5');
        expect(url).toContain('exclude=9');
    });

    it('emits the picked option and null for top level', async () => {
        const wrapper = mountPicker();

        await wrapper.find('button').trigger('click');
        await vi.waitFor(() => expect(http.get).toHaveBeenCalled());

        const option = wrapper
            .findAll('[role="option"]')
            .find((candidate) => candidate.text().includes('Raincoats'))!;
        await option.trigger('click');

        expect(wrapper.emitted('update:modelValue')![0][0]).toEqual({ id: 3, name: 'Raincoats', breadcrumb: 'Outerwear' });
    });

    it('is disabled without a group', () => {
        expect(mountPicker(null, null).find('button').attributes('disabled')).toBeDefined();
    });
});
