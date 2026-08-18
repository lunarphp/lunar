import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CollectionPicker from './CollectionPicker.vue';

vi.mock('../lib/http', () => ({
    http: {
        get: vi.fn().mockResolvedValue({
            data: [
                { id: 2, name: 'Laptops', breadcrumb: 'Catalog' },
                { id: 3, name: 'Phones', breadcrumb: 'Catalog' },
            ],
        }),
    },
}));

import { http } from '../lib/http';

const known = [{ id: 1, name: 'Flagship', breadcrumb: '' }];

const mountPicker = (modelValue = [1]) =>
    mount(CollectionPicker, {
        props: { modelValue, known, searchUrl: '/panel/catalog/collections/search' },
    });

describe('CollectionPicker', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders a chip per selected collection', () => {
        const wrapper = mountPicker();

        expect(wrapper.text()).toContain('Flagship');
    });

    it('removing a chip emits the filtered id list', async () => {
        const wrapper = mountPicker();

        await wrapper.find('button[aria-label]').trigger('click');

        expect(wrapper.emitted('update:modelValue')).toEqual([[[]]]);
    });

    it('opens the dialog and searches the endpoint', async () => {
        const wrapper = mountPicker();

        await wrapper.findAll('button').find((button) => button.text().includes('collections.add'))!.trigger('click');
        await vi.waitFor(() => expect(http.get).toHaveBeenCalled());

        expect((http.get as ReturnType<typeof vi.fn>).mock.calls[0][0]).toContain('/panel/catalog/collections/search');
    });
});
