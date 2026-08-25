<script setup lang="ts">
import { CalendarDateTime, parseDate, parseDateTime, type DateValue } from '@internationalized/date';
import {
    DatePickerArrow,
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerContent,
    DatePickerField,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerInput,
    DatePickerNext,
    DatePickerPrev,
    DatePickerRoot,
    DatePickerTrigger,
} from 'reka-ui';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * A date-and-time picker matching the panel's control styling. The public
 * v-model is a zoneless `YYYY-MM-DDTHH:mm` string (the same value a native
 * `datetime-local` input produces), so callers keep that contract while getting
 * a styled, keyboard-accessible field with a calendar popover. A date-only
 * `YYYY-MM-DD` value renders as midnight; an empty string means "no value".
 */
const props = withDefaults(
    defineProps<{
        modelValue?: string;
        invalid?: boolean;
    }>(),
    { modelValue: '' },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const { t, locale } = useI18n();

// The staff member's panel locale, as a BCP 47 tag for reka-ui (pt_BR -> pt-BR).
const pickerLocale = computed(() => locale.value.replace('_', '-'));

function pad(value: number): string {
    return String(value).padStart(2, '0');
}

const value = computed<DateValue | undefined>({
    get() {
        if (!props.modelValue) {
            return undefined;
        }
        try {
            return parseDateTime(props.modelValue);
        } catch {
            // Fall through: a date-only YYYY-MM-DD value renders as midnight.
        }
        try {
            const date = parseDate(props.modelValue);

            return new CalendarDateTime(date.year, date.month, date.day, 0, 0);
        } catch {
            return undefined;
        }
    },
    set(next) {
        if (!next) {
            emit('update:modelValue', '');
            return;
        }
        // Duck-typed rather than instanceof: reka-ui may resolve its own copy
        // of @internationalized/date, and a cross-copy instanceof would
        // silently zero the chosen time.
        const dt = 'hour' in next
            ? next
            : new CalendarDateTime(next.year, next.month, next.day, 0, 0);
        emit(
            'update:modelValue',
            `${dt.year}-${pad(dt.month)}-${pad(dt.day)}T${pad(dt.hour)}:${pad(dt.minute)}`,
        );
    },
});
</script>

<template>
    <DatePickerRoot
        v-model="value"
        granularity="minute"
        :hour-cycle="24"
        :locale="pickerLocale"
        class="block w-full"
    >
        <DatePickerField
            v-slot="{ segments }"
            :class="[
                'flex h-8 w-full items-center gap-0.5 rounded-md border bg-surface px-2.5 text-[13px] text-ink-900 transition-[border-color,box-shadow] duration-100',
                'focus-within:outline-none focus-within:ring-3',
                invalid
                    ? 'border-danger focus-within:border-danger focus-within:ring-danger/25'
                    : 'border-line-strong focus-within:border-sage focus-within:ring-sage/35',
            ]"
        >
            <template v-for="(item, index) in segments" :key="index">
                <DatePickerInput
                    v-if="item.part === 'literal'"
                    :part="item.part"
                    class="px-0.5 text-ink-500"
                >
                    {{ item.value }}
                </DatePickerInput>
                <DatePickerInput
                    v-else
                    :part="item.part"
                    class="rounded px-0.5 tabular-nums outline-none focus:bg-sage/15 data-[placeholder]:text-ink-400"
                >
                    {{ item.value }}
                </DatePickerInput>
            </template>

            <DatePickerTrigger class="ml-1 text-ink-500 hover:text-ink-900" :aria-label="t('common.open_calendar')">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <path d="M16 2v4M8 2v4M3 10h18" />
                </svg>
            </DatePickerTrigger>
        </DatePickerField>

        <DatePickerContent
            :side-offset="6"
            class="z-50 rounded-md border border-line bg-surface p-3 shadow-lg"
        >
            <DatePickerArrow class="fill-surface stroke-line" />
            <DatePickerCalendar v-slot="{ weekDays, grid }">
                <DatePickerHeader class="mb-2 flex items-center justify-between">
                    <DatePickerPrev class="flex h-7 w-7 items-center justify-center rounded text-ink-500 hover:bg-line hover:text-ink-900">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6" /></svg>
                    </DatePickerPrev>
                    <DatePickerHeading class="text-[13px] font-medium text-ink-900" />
                    <DatePickerNext class="flex h-7 w-7 items-center justify-center rounded text-ink-500 hover:bg-line hover:text-ink-900">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6" /></svg>
                    </DatePickerNext>
                </DatePickerHeader>

                <DatePickerGrid
                    v-for="month in grid"
                    :key="month.value.toString()"
                    class="w-full border-collapse select-none"
                >
                    <DatePickerGridHead>
                        <DatePickerGridRow class="mb-1 flex">
                            <DatePickerHeadCell
                                v-for="day in weekDays"
                                :key="day"
                                class="flex h-8 w-8 items-center justify-center text-[11px] font-normal text-ink-500"
                            >
                                {{ day }}
                            </DatePickerHeadCell>
                        </DatePickerGridRow>
                    </DatePickerGridHead>
                    <DatePickerGridBody>
                        <DatePickerGridRow
                            v-for="(weekDates, index) in month.rows"
                            :key="`week-${index}`"
                            class="flex"
                        >
                            <DatePickerCell
                                v-for="weekDate in weekDates"
                                :key="weekDate.toString()"
                                :date="weekDate"
                                class="p-0"
                            >
                                <DatePickerCellTrigger
                                    :day="weekDate"
                                    :month="month.value"
                                    class="flex h-8 w-8 items-center justify-center rounded text-[13px] text-ink-900 tabular-nums hover:bg-line data-[disabled]:pointer-events-none data-[outside-view]:text-ink-300 data-[selected]:bg-sage data-[selected]:text-white data-[disabled]:opacity-40 data-[today]:font-semibold data-[today]:ring-1 data-[today]:ring-line-strong"
                                />
                            </DatePickerCell>
                        </DatePickerGridRow>
                    </DatePickerGridBody>
                </DatePickerGrid>
            </DatePickerCalendar>
        </DatePickerContent>
    </DatePickerRoot>
</template>
