import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import UsageMeter from './UsageMeter.vue';

describe('UsageMeter', () => {
    it('draws no bar when there is no redemption limit', () => {
        const wrapper = mount(UsageMeter, { props: { used: 12, max: null } });

        expect(wrapper.text()).toContain('12');
        expect(wrapper.text()).toContain('discounts.usage_unlimited');
        expect(wrapper.find('[style]').exists()).toBe(false);
    });

    it('fills the bar in proportion to the limit', () => {
        const wrapper = mount(UsageMeter, { props: { used: 25, max: 100 } });

        expect(wrapper.find('[style]').attributes('style')).toContain('width: 25%');
    });

    it('warns as the limit approaches and flags it once reached', () => {
        expect(mount(UsageMeter, { props: { used: 5, max: 100 } }).find('[style]').classes()).toContain('bg-sage');
        expect(mount(UsageMeter, { props: { used: 85, max: 100 } }).find('[style]').classes()).toContain('bg-warn');
        expect(mount(UsageMeter, { props: { used: 100, max: 100 } }).find('[style]').classes()).toContain('bg-danger');
    });

    it('never overfills when usage has passed the limit', () => {
        const wrapper = mount(UsageMeter, { props: { used: 140, max: 100 } });

        expect(wrapper.find('[style]').attributes('style')).toContain('width: 100%');
    });
});
