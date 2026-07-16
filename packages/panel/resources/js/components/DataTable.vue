<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Checkbox from './Checkbox.vue';
import DataTableCell, { type DataTableColumnType } from './DataTableCell.vue';
import RowActions, { type RowAction } from './RowActions.vue';

interface DataTableColumn {
    key: string;
    label: string;
    width?: string;
    align?: 'left' | 'right' | 'center';
    /** Namespaced add-on cell component (e.g. "my-addon::RatingCell"), resolved via window.LunarPanel. */
    component?: string;
    /** Generic renderer descriptor for add-on columns without a component. */
    type?: DataTableColumnType;
}

type RowKey = string | number;

const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: Record<string, unknown>[];
        rowKey?: string;
        rowTo?: (row: Record<string, unknown>) => string | null | undefined;
        rowActions?: RowAction[];
        selectable?: boolean;
        selected?: RowKey[];
        emptyText?: string;
    }>(),
    { rowKey: 'id', rowActions: () => [], selectable: false, selected: () => [] },
);

const emit = defineEmits<{ 'update:selected': [value: RowKey[]] }>();

const { t } = useI18n();

const hasRowActions = computed(() => props.rowActions.length > 0);

const gridTemplate = computed(() => {
    const tracks: string[] = [];

    if (props.selectable) {
        tracks.push('min-content');
    }

    tracks.push(...props.columns.map((c) => c.width || 'minmax(0, 1fr)'));

    if (hasRowActions.value) {
        // Fixed, not content-based: the header and each row are separate grids, and the
        // actions cell holds the 26px trigger in rows but an empty placeholder in the
        // header. A `max-content` track would then resolve wider in rows than in the
        // header, shifting every flexible column out of alignment.
        tracks.push('2rem');
    }

    return tracks.join(' ');
});

const keyOf = (row: Record<string, unknown>): RowKey => row[props.rowKey] as RowKey;

const selectedSet = computed(() => new Set(props.selected));

const allSelected = computed(() => props.rows.length > 0 && props.rows.every((row) => selectedSet.value.has(keyOf(row))));
const someSelected = computed(() => props.rows.some((row) => selectedSet.value.has(keyOf(row))));

const toggleAll = (checked: boolean): void => {
    emit('update:selected', checked ? props.rows.map(keyOf) : []);
};

const toggleRow = (row: Record<string, unknown>, checked: boolean): void => {
    const next = new Set(props.selected);
    checked ? next.add(keyOf(row)) : next.delete(keyOf(row));
    emit('update:selected', [...next]);
};

// rowTo may return null/undefined for individual rows to keep them non-clickable.
const linkFor = (row: Record<string, unknown>): string | null => (props.rowTo ? props.rowTo(row) : null) || null;
</script>

<template>
    <div class="bg-surface border border-line rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div
            class="grid items-center gap-3 px-3.5 py-2.5 bg-surface-2 border-b border-line text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium"
            :style="{ gridTemplateColumns: gridTemplate }"
        >
            <div v-if="selectable" class="flex items-center">
                <Checkbox
                    :model-value="allSelected"
                    :indeterminate="someSelected && !allSelected"
                    :aria-label="t('common.select_all_rows')"
                    @update:model-value="toggleAll"
                />
            </div>
            <div
                v-for="c in columns"
                :key="c.key"
                :class="c.align === 'right' ? 'text-right' : c.align === 'center' ? 'text-center' : ''"
            >{{ c.label }}</div>
            <div v-if="hasRowActions" aria-hidden="true" />
        </div>

        <!-- Empty -->
        <div
            v-if="!rows.length"
            class="px-6 py-10 text-center text-xs text-ink-500"
        >
            <slot name="empty">{{ emptyText ?? t('common.no_records') }}</slot>
        </div>

        <!-- Rows -->
        <component
            v-for="row in rows"
            :key="keyOf(row)"
            :is="linkFor(row) ? Link : 'div'"
            :href="linkFor(row) || undefined"
            :class="[
                'grid items-center gap-3 px-3.5 py-2.5 border-b border-line last:border-b-0 transition-[background-color] duration-100',
                linkFor(row) ? 'cursor-pointer hover:bg-surface-2' : '',
            ]"
            :style="{ gridTemplateColumns: gridTemplate }"
        >
            <div v-if="selectable" class="flex items-center" @click.stop.prevent>
                <Checkbox
                    :model-value="selectedSet.has(keyOf(row))"
                    :aria-label="`Select row`"
                    @update:model-value="(v: boolean) => toggleRow(row, v)"
                />
            </div>
            <div
                v-for="c in columns"
                :key="c.key"
                :class="[
                    'min-w-0',
                    c.align === 'right' ? 'text-right' : c.align === 'center' ? 'text-center' : '',
                ]"
            >
                <slot :name="`cell-${c.key}`" :row="row" :value="row[c.key]">
                    <DataTableCell :component="c.component" :type="c.type" :row="row" :value="row[c.key]" />
                </slot>
            </div>
            <RowActions v-if="hasRowActions" :actions="rowActions" :row="row" />
        </component>
    </div>
</template>
