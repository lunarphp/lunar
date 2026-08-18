import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import TimeSeriesChart from './TimeSeriesChart.vue';

const points = [
    { label: 'Jan', value: 0 },
    { label: 'Feb', value: 50, display: '£50.00' },
    { label: 'Mar', value: 100, display: '£100.00' },
];

const mountChart = (overrides = {}) =>
    mount(TimeSeriesChart, {
        props: { points, width: 400, ariaLabel: 'Order value', ...overrides },
    });

describe('TimeSeriesChart', () => {
    it('renders an accessible svg with a line and area for the series', () => {
        const wrapper = mountChart();

        const svg = wrapper.find('svg[role="img"]');
        expect(svg.exists()).toBe(true);
        expect(svg.attributes('aria-label')).toBe('Order value');

        const paths = wrapper.findAll('path');
        expect(paths).toHaveLength(2);
        // Line path has one command per point.
        expect(paths[1].attributes('d')!.split(/[ML]/).filter(Boolean)).toHaveLength(points.length);
    });

    it('renders nothing without points', () => {
        const wrapper = mountChart({ points: [] });

        expect(wrapper.find('svg').exists()).toBe(false);
    });

    it('renders x labels and formatted y ticks', () => {
        const wrapper = mountChart({ formatValue: (value: number) => `£${value}` });

        const text = wrapper.text();
        expect(text).toContain('Jan');
        expect(text).toContain('Mar');
        expect(text).toContain('£100');
    });

    it('shows a tooltip via keyboard navigation with the display value leading', async () => {
        const wrapper = mountChart();

        await wrapper.find('[tabindex="0"]').trigger('keydown', { key: 'ArrowRight' });

        expect(wrapper.find('[role="status"]').text()).toContain('Jan');

        await wrapper.find('[tabindex="0"]').trigger('keydown', { key: 'ArrowRight' });
        await wrapper.find('[tabindex="0"]').trigger('keydown', { key: 'ArrowRight' });

        const tooltip = wrapper.find('[role="status"]');
        expect(tooltip.text()).toContain('£100.00');
        expect(tooltip.text()).toContain('Mar');

        await wrapper.find('[tabindex="0"]').trigger('keydown', { key: 'Escape' });
        expect(wrapper.find('[role="status"]').exists()).toBe(false);
    });

    it('animates the line draw-in with normalised dash math', () => {
        const wrapper = mountChart();

        const line = wrapper.find('path.tsc-line');
        expect(line.exists()).toBe(true);
        expect(line.attributes('pathLength')).toBe('1');
        expect(wrapper.find('path.tsc-area').exists()).toBe(true);
    });

    it('replays the draw-in only when point values actually change', async () => {
        const wrapper = mountChart();

        const lineBefore = wrapper.findAll('path')[1].element;

        // A fresh but value-identical array (a partial reload after a draft
        // commit) must not remount the series paths.
        await wrapper.setProps({ points: points.map((point) => ({ ...point })) });
        expect(wrapper.findAll('path')[1].element).toBe(lineBefore);

        // Different values (a range switch) restart the animation via remount.
        await wrapper.setProps({ points: [...points, { label: 'Apr', value: 25 }] });
        expect(wrapper.findAll('path')[1].element).not.toBe(lineBefore);
    });

    it('scales a flat zero series without dividing by zero', () => {
        const wrapper = mountChart({ points: [{ label: 'Jan', value: 0 }, { label: 'Feb', value: 0 }] });

        expect(wrapper.find('svg').exists()).toBe(true);
        expect(wrapper.findAll('path')[1].attributes('d')).not.toContain('NaN');
    });
});
