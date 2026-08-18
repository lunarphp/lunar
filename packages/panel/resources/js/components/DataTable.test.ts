import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import DataTable from './DataTable.vue';

const columns = [{ key: 'name', label: 'Name' }];
const rows = [
    { id: 1, name: 'Ann' },
    { id: 2, name: 'Bob' },
    { id: 3, name: 'Cal' },
];

describe('DataTable selection', () => {
    it('shows no selection checkboxes unless selectable', () => {
        const wrapper = mount(DataTable, { props: { columns, rows } });

        expect(wrapper.findAllComponents({ name: 'Checkbox' })).toHaveLength(0);
    });

    it('renders a header checkbox and one per row when selectable', () => {
        const wrapper = mount(DataTable, { props: { columns, rows, selectable: true } });

        // header + 3 rows
        expect(wrapper.findAllComponents({ name: 'Checkbox' })).toHaveLength(4);
    });

    it('emits every row key when the header checkbox selects all', async () => {
        const wrapper = mount(DataTable, { props: { columns, rows, selectable: true } });

        await wrapper.findComponent({ name: 'Checkbox' }).vm.$emit('update:modelValue', true);

        expect(wrapper.emitted('update:selected')?.[0]?.[0]).toEqual([1, 2, 3]);
    });

    it('toggles a single row into and out of the selection', async () => {
        const wrapper = mount(DataTable, {
            props: { columns, rows, selectable: true, selected: [2] },
        });

        const rowBoxes = wrapper.findAllComponents({ name: 'Checkbox' });

        // First row checkbox (index 1; index 0 is the header) selects row 1 alongside 2.
        await rowBoxes[1].vm.$emit('update:modelValue', true);
        expect(wrapper.emitted('update:selected')?.at(-1)?.[0]).toEqual([2, 1]);
    });
});
