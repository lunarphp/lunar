<script setup lang="ts">
// A row per ISO weekday: enabled, from, until. Stored at data.schedule in the
// shape ShippingMethod::isAvailable() reads. A null schedule means the method
// is always available, so the grid sits behind a toggle.
import { useI18n } from 'vue-i18n';
import { Checkbox, TextInput, Toggle } from '@lunarphp/panel';

export type ScheduleDay = { enabled: boolean; from: string | null; to: string | null };
export type Schedule = Record<string, ScheduleDay>;

const props = defineProps<{
    modelValue: Schedule | null;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: Schedule | null] }>();

const { t } = useI18n();

const days = ['1', '2', '3', '4', '5', '6', '7'];

const blank = (): Schedule => Object.fromEntries(days.map((day) => [day, { enabled: false, from: null, to: null }]));

const toggle = (): void => {
    emit('update:modelValue', props.modelValue ? null : blank());
};

const update = (day: string, patch: Partial<ScheduleDay>): void => {
    if (!props.modelValue) return;

    emit('update:modelValue', { ...props.modelValue, [day]: { ...props.modelValue[day], ...patch } });
};

const errorFor = (day: string, field: string): string | undefined => props.errors?.[`data.schedule.${day}.${field}`];
</script>

<template>
    <div>
        <label class="flex items-center gap-3 cursor-pointer">
            <Toggle :on="modelValue !== null" @toggle="toggle" />
            <span class="text-[12.5px] text-ink-900 font-medium">{{ t('shipping::methods.schedule_toggle') }}</span>
        </label>

        <div v-if="modelValue" class="mt-4 bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
            <div class="grid items-center gap-3 px-3.5 py-2.5 bg-surface-2 border-b border-line text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium min-w-max" style="grid-template-columns: minmax(0, 1fr) 140px 140px">
                <div />
                <div>{{ t('shipping::methods.schedule_from') }}</div>
                <div>{{ t('shipping::methods.schedule_to') }}</div>
            </div>
            <div v-for="day in days" :key="day" class="grid items-start gap-3 px-3.5 py-2 border-b border-line last:border-b-0 min-w-max" style="grid-template-columns: minmax(0, 1fr) 140px 140px">
                <label class="flex items-center gap-2 cursor-pointer text-[12.5px] text-ink-900 min-h-[30px]">
                    <Checkbox :model-value="modelValue[day].enabled" @update:model-value="update(day, { enabled: $event })" />
                    {{ t(`shipping::methods.day_${day}`) }}
                </label>
                <TextInput :model-value="modelValue[day].from ?? ''" type="time" :disabled="!modelValue[day].enabled" @update:model-value="update(day, { from: $event || null })" />
                <div>
                    <TextInput :model-value="modelValue[day].to ?? ''" type="time" :disabled="!modelValue[day].enabled" :invalid="!!errorFor(day, 'to')" @update:model-value="update(day, { to: $event || null })" />
                    <div v-if="errorFor(day, 'to')" class="mt-1 text-[11px] text-danger">{{ errorFor(day, 'to') }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
