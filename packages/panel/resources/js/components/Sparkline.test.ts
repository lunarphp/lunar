import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import Sparkline from './Sparkline.vue';

describe('Sparkline', () => {
    it('renders an area and line path for the points', () => {
        const wrapper = mount(Sparkline, { props: { points: [1, 3, 2, 5] } });

        const paths = wrapper.findAll('path');
        expect(paths).toHaveLength(2);
        expect(paths[1].attributes('d')!.split(/[ML]/).filter(Boolean)).toHaveLength(4);
    });

    it('renders nothing without points', () => {
        const wrapper = mount(Sparkline, { props: { points: [] } });

        expect(wrapper.find('svg').exists()).toBe(false);
    });

    it('is decorative by default and accessible when labelled', () => {
        const decorative = mount(Sparkline, { props: { points: [1, 2] } });
        expect(decorative.find('svg').attributes('aria-hidden')).toBe('true');

        const labelled = mount(Sparkline, { props: { points: [1, 2], ariaLabel: 'Revenue trend' } });
        const svg = labelled.find('svg[role="img"]');
        expect(svg.exists()).toBe(true);
        expect(svg.attributes('aria-label')).toBe('Revenue trend');
    });

    it('omits the fill when unfilled', () => {
        const wrapper = mount(Sparkline, { props: { points: [1, 2, 3], filled: false } });

        expect(wrapper.findAll('path')).toHaveLength(1);
    });

    it('renders a flat zero series without NaN geometry', () => {
        const wrapper = mount(Sparkline, { props: { points: [0, 0, 0] } });

        expect(wrapper.findAll('path')[1].attributes('d')).not.toContain('NaN');
    });
});
