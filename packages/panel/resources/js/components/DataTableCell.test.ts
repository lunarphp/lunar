import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { defineComponent, h } from 'vue';
import DataTableCell from './DataTableCell.vue';

const stubRegistry = (components: Record<string, ReturnType<typeof defineComponent>>): void => {
    window.LunarPanel = {
        resolveExtensionComponent: (name: string) => components[name],
    } as unknown as Window['LunarPanel'];
};

describe('DataTableCell', () => {
    it('renders a registered add-on component with row and value props', () => {
        const RatingCell = defineComponent({
            props: ['row', 'value'],
            render() {
                return h('div', `${this.row.id}:${this.value}`);
            },
        });

        stubRegistry({ 'my-addon::RatingCell': RatingCell });

        const wrapper = mount(DataTableCell, {
            props: { component: 'my-addon::RatingCell', row: { id: 7 }, value: 4 },
        });

        expect(wrapper.text()).toBe('7:4');
    });

    it('falls back to the type renderer when the component is not registered', () => {
        stubRegistry({});

        const wrapper = mount(DataTableCell, {
            props: {
                component: 'my-addon::Missing',
                type: { name: 'badge', options: {} },
                row: {},
                value: 'Active',
            },
        });

        expect(wrapper.text()).toBe('Active');
        expect(wrapper.find('span').classes().join(' ')).toContain('rounded-full');
    });

    it('renders a badge for the badge type', () => {
        stubRegistry({});

        const wrapper = mount(DataTableCell, {
            props: { type: { name: 'badge', options: {} }, row: {}, value: 'Shipped' },
        });

        expect(wrapper.text()).toBe('Shipped');
        expect(wrapper.find('span').classes().join(' ')).toContain('rounded-full');
    });

    it('formats a currency value using the code option', () => {
        stubRegistry({});

        const wrapper = mount(DataTableCell, {
            props: { type: { name: 'currency', options: { code: 'GBP' } }, row: {}, value: 12.5 },
        });

        expect(wrapper.text()).toContain('12.50');
        expect(wrapper.text()).toContain('£');
    });

    it('renders a check icon for a truthy boolean and a dash for falsy', () => {
        stubRegistry({});

        const truthy = mount(DataTableCell, {
            props: { type: { name: 'boolean', options: {} }, row: {}, value: true },
        });
        const falsy = mount(DataTableCell, {
            props: { type: { name: 'boolean', options: {} }, row: {}, value: false },
        });

        expect(truthy.find('svg').exists()).toBe(true);
        expect(falsy.text()).toBe('—');
    });

    it('renders an em dash for empty values regardless of type', () => {
        stubRegistry({});

        const wrapper = mount(DataTableCell, {
            props: { type: { name: 'badge', options: {} }, row: {}, value: null },
        });

        expect(wrapper.text()).toBe('—');
    });

    it('renders plain text when no component or type is declared', () => {
        stubRegistry({});

        const wrapper = mount(DataTableCell, { props: { row: {}, value: 'plain' } });

        expect(wrapper.text()).toBe('plain');
    });
});
