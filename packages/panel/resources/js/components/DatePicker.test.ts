import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { CalendarDate, CalendarDateTime } from '@internationalized/date';
import { DatePickerRoot } from 'reka-ui';
import DatePicker from './DatePicker.vue';

describe('DatePicker', () => {
    it('renders the segments for a datetime-local string value', () => {
        const wrapper = mount(DatePicker, {
            props: { modelValue: '2026-06-01T12:30' },
        });

        const text = wrapper.text();
        expect(text).toContain('2026');
        expect(text).toContain('12');
        expect(text).toContain('30');
    });

    it('renders without a value and exposes the calendar trigger', () => {
        const wrapper = mount(DatePicker, {
            props: { modelValue: '' },
        });

        // The test i18n instance carries no messages, so t() yields the raw key.
        expect(wrapper.find('[aria-label="common.open_calendar"]').exists()).toBe(true);
    });

    it('renders a date-only string as midnight', () => {
        const wrapper = mount(DatePicker, {
            props: { modelValue: '2026-06-01' },
        });

        const text = wrapper.text();
        expect(text).toContain('2026');
        expect(text).toContain('00');
    });

    it('ignores an unparseable value instead of throwing', () => {
        expect(() =>
            mount(DatePicker, { props: { modelValue: 'not-a-date' } }),
        ).not.toThrow();
    });

    it('emits a zero-padded datetime-local string from a CalendarDateTime', async () => {
        const wrapper = mount(DatePicker, { props: { modelValue: '' } });

        wrapper.findComponent(DatePickerRoot).vm.$emit('update:modelValue', new CalendarDateTime(2026, 8, 6, 9, 5));
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['2026-08-06T09:05']);
    });

    it('emits midnight when only a calendar date (no time) is chosen', async () => {
        const wrapper = mount(DatePicker, { props: { modelValue: '' } });

        wrapper.findComponent(DatePickerRoot).vm.$emit('update:modelValue', new CalendarDate(2026, 8, 6));
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('update:modelValue')?.at(-1)).toEqual(['2026-08-06T00:00']);
    });
});
