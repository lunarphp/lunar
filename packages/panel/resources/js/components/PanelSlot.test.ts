import { beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { defineComponent, h } from 'vue';
import PanelSlot from './PanelSlot.vue';

const pageProps = vi.hoisted(() => ({ slots: {} as Record<string, unknown> }));

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: pageProps }),
}));

describe('PanelSlot', () => {
    beforeEach(() => {
        pageProps.slots = {};
    });

    it('renders slot entries in priority order', () => {
        const First = defineComponent({
            props: ['label'],
            render() {
                return h('div', this.label);
            },
        });
        const Second = defineComponent({
            props: ['label'],
            render() {
                return h('div', this.label);
            },
        });

        window.LunarPanel = {
            resolveExtensionComponent: (name: string) => (name === 'First' ? First : name === 'Second' ? Second : undefined),
        } as unknown as Window['LunarPanel'];

        pageProps.slots = {
            toolbar: [
                { component: 'Second', props: { label: 'second' }, priority: 2 },
                { component: 'First', props: { label: 'first' }, priority: 1 },
            ],
        };

        const wrapper = mount(PanelSlot, { props: { name: 'toolbar' } });

        expect(wrapper.findAll('div').map((div) => div.text())).toEqual(['first', 'second']);
    });

    it('skips an entry whose component does not resolve, without throwing', () => {
        const Known = defineComponent({
            render() {
                return h('div', 'known');
            },
        });

        window.LunarPanel = {
            resolveExtensionComponent: (name: string) => (name === 'Known' ? Known : undefined),
        } as unknown as Window['LunarPanel'];

        pageProps.slots = {
            toolbar: [
                { component: 'Known', props: {}, priority: 1 },
                { component: 'Missing', props: {}, priority: 2 },
            ],
        };

        expect(() => mount(PanelSlot, { props: { name: 'toolbar' } })).not.toThrow();

        const wrapper = mount(PanelSlot, { props: { name: 'toolbar' } });
        const divs = wrapper.findAll('div');

        expect(divs).toHaveLength(1);
        expect(divs[0].text()).toBe('known');
    });

    it('renders the same component twice in one zone without key collisions', () => {
        const Banner = defineComponent({
            props: ['label'],
            render() {
                return h('div', this.label);
            },
        });

        window.LunarPanel = {
            resolveExtensionComponent: () => Banner,
        } as unknown as Window['LunarPanel'];

        pageProps.slots = {
            toolbar: [
                { component: 'Banner', props: { label: 'one' }, priority: 1 },
                { component: 'Banner', props: { label: 'two' }, priority: 2 },
            ],
        };

        const warn = vi.spyOn(console, 'warn');
        const wrapper = mount(PanelSlot, { props: { name: 'toolbar' } });

        expect(wrapper.findAll('div').map((div) => div.text())).toEqual(['one', 'two']);
        expect(warn).not.toHaveBeenCalled();

        warn.mockRestore();
    });

    it('passes both the slot entry props and extra attrs through to the rendered component', () => {
        const Widget = defineComponent({
            props: ['label', 'extra'],
            render() {
                return h('div', `${this.label}-${this.extra}`);
            },
        });

        window.LunarPanel = {
            resolveExtensionComponent: () => Widget,
        } as unknown as Window['LunarPanel'];

        pageProps.slots = {
            toolbar: [{ component: 'Widget', props: { label: 'from-slot' }, priority: 1 }],
        };

        const wrapper = mount(PanelSlot, {
            props: { name: 'toolbar' },
            attrs: { extra: 'from-attrs' },
        });

        expect(wrapper.text()).toBe('from-slot-from-attrs');
    });
});
