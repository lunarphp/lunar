<script setup lang="ts">
// A row per customer group mapping to the availability pivot: enabled,
// visible, and an optional start and end date edited in a dialog.
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button, Checkbox, DatePicker, Dialog, FieldLabel } from '@lunarphp/panel';

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

const { t, locale } = useI18n();

const update = (index: number, patch: Partial<GroupRow>): void => {
    emit('update:modelValue', props.modelValue.map((row, i) => (i === index ? { ...row, ...patch } : row)));
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime())
        ? value
        : new Intl.DateTimeFormat(locale.value.replace('_', '-'), { dateStyle: 'medium', timeStyle: 'short' }).format(date);
};

const endsAtError = (index: number): string | undefined => props.errors?.[`customer_groups.${index}.ends_at`];

const editingIndex = ref<number | null>(null);
const draftStart = ref('');
const draftEnd = ref('');

const dialogOpen = computed({
    get: () => editingIndex.value !== null,
    set: (open: boolean) => {
        if (!open) {
            editingIndex.value = null;
        }
    },
});

const editing = computed(() => (editingIndex.value === null ? null : props.modelValue[editingIndex.value]));

const openSchedule = (index: number): void => {
    draftStart.value = props.modelValue[index].starts_at ?? '';
    draftEnd.value = props.modelValue[index].ends_at ?? '';
    editingIndex.value = index;
};

const applySchedule = (): void => {
    if (editingIndex.value !== null) {
        update(editingIndex.value, { starts_at: draftStart.value || null, ends_at: draftEnd.value || null });
    }
    editingIndex.value = null;
};

const clearSchedule = (): void => {
    draftStart.value = '';
    draftEnd.value = '';
    applySchedule();
};

const gridStyle = { gridTemplateColumns: 'minmax(0, 1.4fr) 80px 80px minmax(0, 1fr) minmax(0, 1fr) 30px' };
</script>

<template>
    <div>
        <div v-if="!modelValue.some((row) => row.enabled)" class="mb-3 text-xs text-warn">{{ t('shipping::methods.no_groups_enabled') }}</div>

        <div class="bg-surface border border-line rounded-xl shadow-sm">
            <div class="grid items-center gap-3 px-3.5 py-2.5 bg-surface-2 border-b border-line text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium" :style="gridStyle">
                <div>{{ t('shipping::methods.column_group') }}</div>
                <div>{{ t('shipping::methods.column_enabled') }}</div>
                <div>{{ t('shipping::methods.column_visible') }}</div>
                <div>{{ t('shipping::methods.column_starts_at') }}</div>
                <div>{{ t('shipping::methods.column_ends_at') }}</div>
                <div />
            </div>
            <div v-for="(row, index) in modelValue" :key="row.id" class="grid items-center gap-3 px-3.5 py-2 border-b border-line last:border-b-0" :style="gridStyle">
                <div class="text-[12.5px] text-ink-900 min-h-[34px] flex items-center min-w-0 truncate">{{ row.name }}</div>
                <div class="min-h-[34px] flex items-center"><Checkbox :model-value="row.enabled" :aria-label="t('shipping::methods.column_enabled')" @update:model-value="update(index, { enabled: $event })" /></div>
                <div class="min-h-[34px] flex items-center"><Checkbox :model-value="row.visible" :aria-label="t('shipping::methods.column_visible')" @update:model-value="update(index, { visible: $event })" /></div>
                <div class="min-w-0 truncate text-[12.5px]" :class="row.starts_at ? 'text-ink-900' : 'text-ink-400'">{{ formatDate(row.starts_at) }}</div>
                <div class="min-w-0">
                    <div class="truncate text-[12.5px]" :class="row.ends_at ? 'text-ink-900' : 'text-ink-400'">{{ formatDate(row.ends_at) }}</div>
                    <div v-if="endsAtError(index)" class="mt-1 text-[11px] text-danger">{{ endsAtError(index) }}</div>
                </div>
                <Button variant="ghost" size="sm" icon="calendar" :aria-label="t('availability.schedule_title')" @click="openSchedule(index)" />
            </div>
        </div>

        <Dialog v-model:open="dialogOpen" size="sm" :title="editing ? `${t('availability.schedule_title')} — ${editing.name}` : ''">
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <FieldLabel>{{ t('availability.schedule_starts') }}</FieldLabel>
                    <DatePicker v-model="draftStart" />
                </div>
                <div>
                    <FieldLabel>{{ t('availability.schedule_ends') }}</FieldLabel>
                    <DatePicker v-model="draftEnd" />
                </div>
            </div>

            <template #footer>
                <Button variant="ghost" class="mr-auto" @click="clearSchedule">{{ t('availability.clear_schedule') }}</Button>
                <Button variant="ghost" @click="dialogOpen = false">{{ t('common.cancel') }}</Button>
                <Button variant="primary" @click="applySchedule">{{ t('availability.schedule_apply') }}</Button>
            </template>
        </Dialog>
    </div>
</template>
