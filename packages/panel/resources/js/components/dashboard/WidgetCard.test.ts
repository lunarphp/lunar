import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { createI18n } from 'vue-i18n';
import WidgetCard from './WidgetCard.vue';

const i18n = createI18n({
    legacy: false,
    locale: 'en',
    messages: {
        en: {
            dashboard: {
                drag_handle: 'Drag to reorder',
                hide_widget: 'Hide widget',
            },
        },
    },
});

const mountCard = (props = {}) =>
    mount(WidgetCard, {
        props: { widgetKey: 'kpis', title: 'Overview', ...props },
        global: { plugins: [i18n] },
        slots: { default: '<span data-testid="body">body</span>' },
    });

describe('WidgetCard', () => {
    it('renders the header and body without edit affordances', () => {
        const wrapper = mountCard();

        expect(wrapper.text()).toContain('Overview');
        expect(wrapper.find('[data-testid="body"]').exists()).toBe(true);
        expect(wrapper.attributes('draggable')).toBe('false');
        expect(wrapper.find('button[aria-label="Hide widget"]').exists()).toBe(false);
    });

    it('spans both columns for full widgets', () => {
        expect(mountCard({ span: 'full' }).classes()).toContain('lg:col-span-2');
        expect(mountCard({ span: 'half' }).classes()).not.toContain('lg:col-span-2');
    });

    it('drops the card shell when flat', () => {
        const wrapper = mountCard({ flat: true, title: '' });

        expect(wrapper.classes()).not.toContain('bg-surface');
        expect(wrapper.find('h3').exists()).toBe(false);
    });

    it('emits hide from the edit-mode hide button', async () => {
        const wrapper = mountCard({ editing: true });

        await wrapper.find('button[aria-label="Hide widget"]').trigger('click');

        expect(wrapper.emitted('hide')).toEqual([['kpis']]);
    });

    it('emits a reorder on drop with the dragged widget key', async () => {
        const wrapper = mountCard({ editing: true });

        await wrapper.trigger('drop', {
            dataTransfer: { getData: () => 'revenue-chart' },
        });

        expect(wrapper.emitted('reorder')).toEqual([
            [{ fromKey: 'revenue-chart', toKey: 'kpis', position: 'before' }],
        ]);
    });

    it('ignores drops outside edit mode and self-drops', async () => {
        const idle = mountCard();
        await idle.trigger('drop', { dataTransfer: { getData: () => 'revenue-chart' } });
        expect(idle.emitted('reorder')).toBeUndefined();

        const editing = mountCard({ editing: true });
        await editing.trigger('drop', { dataTransfer: { getData: () => 'kpis' } });
        expect(editing.emitted('reorder')).toBeUndefined();
    });
});
