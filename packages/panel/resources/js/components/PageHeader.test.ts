import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import PageHeader from './PageHeader.vue';

vi.mock('@inertiajs/vue3', async () => {
    const { defineComponent, h } = await import('vue');

    return {
        usePage: () => ({ props: { pageActions: [] } }),
        // Renders the received title as a queryable DOM attribute.
        Head: defineComponent({
            name: 'Head',
            props: ['title'],
            render() {
                return h('span', { 'data-head-title': this.title });
            },
        }),
        router: { visit: () => {} },
    };
});

describe('PageHeader', () => {
    it('provides the page title to the browser tab via Head', () => {
        const wrapper = mount(PageHeader, { props: { title: 'Customers' } });

        expect(wrapper.find('[data-head-title]').attributes('data-head-title')).toBe('Customers');
        expect(wrapper.find('h1').text()).toBe('Customers');
    });

    it('renders the standard icon tile from the icon prop', () => {
        const wrapper = mount(PageHeader, { props: { title: 'Customers', icon: 'users' } });

        expect(wrapper.find('.w-11.h-11 svg').exists()).toBe(true);
    });

    it('renders no tile without an icon prop', () => {
        const wrapper = mount(PageHeader, { props: { title: 'Customers' } });

        expect(wrapper.find('.w-11').exists()).toBe(false);
    });

    it('lets the #icon slot override the prop tile', () => {
        const wrapper = mount(PageHeader, {
            props: { title: 'Customers', icon: 'users' },
            slots: { icon: '<div data-custom-avatar>GB</div>' },
        });

        expect(wrapper.find('[data-custom-avatar]').exists()).toBe(true);
        expect(wrapper.find('.w-11.h-11 svg').exists()).toBe(false);
    });
});
