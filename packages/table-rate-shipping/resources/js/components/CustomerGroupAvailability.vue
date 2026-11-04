<script setup lang="ts">
// A row per customer group mapping to the availability pivot: enabled,
// visible, and an optional start and end date.
import { useI18n } from 'vue-i18n';
import { Checkbox, DatePicker } from '@lunarphp/panel';

export interface GroupRow {
    id: number;
    name: string;
    enabled: boolean;
    visible: boolean;
    starts_at: string | null;
    ends_at: string | null;
}

const props = defineProps<{
    modelValue: GroupRow[];
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: GroupRow[]] }>();

const { t } = useI18n();

const update = (index: number, patch: Partial<GroupRow>): void => {
    emit('update:modelValue', props.modelValue.map((row, i) => (i === index ? { ...row, ...patch } : row)));
};

const gridStyle = { gridTemplateColumns: 'minmax(0, 1.4fr) 90px 90px 170px 170px' };
</script>

<template>
    <div>
        <div v-if="!modelValue.some((row) => row.enabled)" class="mb-3 text-xs text-warn">{{ t('shipping::methods.no_groups_enabled') }}</div>

        <div class="bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
            <div class="grid items-center gap-3 px-3.5 py-2.5 bg-surface-2 border-b border-line text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium min-w-max" :style="gridStyle">
                <div>{{ t('shipping::methods.column_group') }}</div>
                <div>{{ t('shipping::methods.column_enabled') }}</div>
                <div>{{ t('shipping::methods.column_visible') }}</div>
                <div>{{ t('shipping::methods.column_starts_at') }}</div>
                <div>{{ t('shipping::methods.column_ends_at') }}</div>
            </div>
            <div v-for="(row, index) in modelValue" :key="row.id" class="grid items-start gap-3 px-3.5 py-2 border-b border-line last:border-b-0 min-w-max" :style="gridStyle">
                <div class="text-[12.5px] text-ink-900 min-h-[30px] flex items-center">{{ row.name }}</div>
                <div class="min-h-[30px] flex items-center"><Checkbox :model-value="row.enabled" :aria-label="t('shipping::methods.column_enabled')" @update:model-value="update(index, { enabled: $event })" /></div>
                <div class="min-h-[30px] flex items-center"><Checkbox :model-value="row.visible" :aria-label="t('shipping::methods.column_visible')" @update:model-value="update(index, { visible: $event })" /></div>
                <DatePicker :model-value="row.starts_at ?? ''" @update:model-value="update(index, { starts_at: $event || null })" />
                <div>
                    <DatePicker :model-value="row.ends_at ?? ''" :invalid="!!errors?.[`customer_groups.${index}.ends_at`]" @update:model-value="update(index, { ends_at: $event || null })" />
                    <div v-if="errors?.[`customer_groups.${index}.ends_at`]" class="mt-1 text-[11px] text-danger">{{ errors[`customer_groups.${index}.ends_at`] }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
