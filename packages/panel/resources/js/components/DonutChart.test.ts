import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import DonutChart from './DonutChart.vue';

const segments = [
    { label: 'Online store', value: 600, display: '£600.00' },
    { label: 'Point of sale', value: 300, display: '£300.00' },
    { label: 'Amazon', value: 100, display: '£100.00' },
];

const mountChart = (overrides = {}) =>
    mount(DonutChart, { props: { segments, ariaLabel: 'Sales by channel', ...overrides } });

describe('DonutChart', () => {
    it('renders an accessible svg with one arc per positive segment', () => {
        const wrapper = mountChart();

        expect(wrapper.find('svg[role="img"]').attributes('aria-label')).toBe('Sales by channel');
        expect(wrapper.findAll('path')).toHaveLength(3);
    });

    it('skips zero-value segments in the ring but keeps them in the legend', () => {
        const wrapper = mountChart({
            segments: [...segments, { label: 'Wholesale', value: 0 }],
        });

        expect(wrapper.findAll('path')).toHaveLength(3);
        expect(wrapper.text()).toContain('Wholesale');
    });

    it('renders a legend with labels, display values, and shares', () => {
        const wrapper = mountChart();

        const legend = wrapper.find('ul');
        expect(legend.exists()).toBe(true);
        expect(legend.text()).toContain('Online store');
        expect(legend.text()).toContain('£600.00');
        expect(legend.text()).toContain('60%');
    });

    it('folds segments beyond the fourth into Other', () => {
        const wrapper = mountChart({
            segments: [
                { label: 'A', value: 500 },
                { label: 'B', value: 400 },
                { label: 'C', value: 300 },
                { label: 'D', value: 200 },
                { label: 'E', value: 100 },
            ],
            otherLabel: 'Everything else',
        });

        const rows = wrapper.findAll('li');
        expect(rows).toHaveLength(4);
        expect(rows[3].text()).toContain('Everything else');
        expect(rows[3].text()).toContain('300');
    });

    it('renders an empty ring and no arcs when every value is zero', () => {
        const wrapper = mountChart({ segments: [{ label: 'A', value: 0 }] });

        expect(wrapper.findAll('path')).toHaveLength(0);
        expect(wrapper.find('circle').exists()).toBe(true);
    });

    it('shows the centre readout', () => {
        const wrapper = mountChart({ centreValue: '£1,000.00', centreLabel: 'Total' });

        expect(wrapper.text()).toContain('£1,000.00');
        expect(wrapper.text()).toContain('Total');
    });

    it('can hide the legend', () => {
        const wrapper = mountChart({ showLegend: false });

        expect(wrapper.find('ul').exists()).toBe(false);
    });

    it('exposes the exact value as a centre tooltip when abbreviated', () => {
        const wrapper = mountChart({ centreValue: '£1.4M', centreTitle: '£1,398,635.13' });

        const centre = wrapper.find('[title]');
        expect(centre.text()).toBe('£1.4M');
        expect(centre.attributes('title')).toBe('£1,398,635.13');
    });
});
