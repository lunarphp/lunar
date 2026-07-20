import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        Link: defineComponent({
            name: 'Link',
            props: ['href'],
            setup(props, { slots }) {
                return () => h('a', { href: props.href }, slots.default?.());
            },
        }),
        router: { get: vi.fn(), post: vi.fn(), put: vi.fn(), patch: vi.fn(), delete: vi.fn() },
    };
});

import CollectionTreeRow, { type CollectionTreeNode } from './CollectionTreeRow.vue';

const node = (overrides: Partial<CollectionTreeNode> = {}): CollectionTreeNode => ({
    id: 1,
    parent_id: null,
    group_id: 1,
    name: 'Outerwear',
    handle: 'outerwear',
    thumbnail: null,
    short_description: 'Coats and jackets',
    status: 'published',
    status_label: 'Published',
    products_count: 4,
    descendants_count: 1,
    matched: true,
    edit_url: '/panel/collections/1/edit',
    children: [],
    _actions: {},
    ...overrides,
});

const child = node({ id: 2, parent_id: 1, name: 'Raincoats', handle: 'raincoats', descendants_count: 0 });

const mountRow = (collection: CollectionTreeNode, options: { expanded?: number[]; forceExpanded?: boolean } = {}) =>
    mount(CollectionTreeRow, {
        props: {
            collection,
            depth: 0,
            expandedIds: new Set(options.expanded ?? []),
            forceExpanded: options.forceExpanded ?? false,
            actions: [],
        },
    });

describe('CollectionTreeRow', () => {
    it('renders name, handle, counts and status', () => {
        const wrapper = mountRow(node());

        expect(wrapper.text()).toContain('Outerwear');
        expect(wrapper.text()).toContain('outerwear');
        expect(wrapper.text()).toContain('Published');
        expect(wrapper.find('a').attributes('href')).toBe('/panel/collections/1/edit');
    });

    it('hides children while collapsed and shows them when expanded', () => {
        const parent = node({ children: [child] });

        expect(mountRow(parent).text()).not.toContain('Raincoats');
        expect(mountRow(parent, { expanded: [1] }).text()).toContain('Raincoats');
    });

    it('emits toggle from the chevron', async () => {
        const wrapper = mountRow(node({ children: [child] }));

        await wrapper.find('button').trigger('click');

        expect(wrapper.emitted('toggle')).toEqual([[1]]);
    });

    it('force-expands and disables the chevron while filtering', () => {
        const wrapper = mountRow(node({ children: [child] }), { forceExpanded: true });

        expect(wrapper.text()).toContain('Raincoats');
        expect(wrapper.find('button').attributes('disabled')).toBeDefined();
    });

    it('renders no chevron for leaves', () => {
        const wrapper = mountRow(node());

        expect(wrapper.find('button[aria-expanded]').exists()).toBe(false);
    });
});
