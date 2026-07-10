<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

interface DataTableColumn {
    key: string;
    label: string;
    width?: string;
    align?: 'left' | 'right' | 'center';
}

const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: Record<string, unknown>[];
        rowKey?: string;
        rowTo?: (row: Record<string, unknown>) => string | null | undefined;
        emptyText?: string;
    }>(),
    { rowKey: 'id', emptyText: 'No records yet' },
);

const gridTemplate = computed(() => props.columns.map((c) => c.width || 'minmax(0, 1fr)').join(' '));

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
            <div
                v-for="c in columns"
                :key="c.key"
                :class="c.align === 'right' ? 'text-right' : c.align === 'center' ? 'text-center' : ''"
            >{{ c.label }}</div>
        </div>

        <!-- Empty -->
        <div
            v-if="!rows.length"
            class="px-6 py-10 text-center text-xs text-ink-500"
        >
            <slot name="empty">{{ emptyText }}</slot>
        </div>

        <!-- Rows -->
        <component
            v-for="row in rows"
            :key="(row[rowKey] as string | number)"
            :is="linkFor(row) ? Link : 'div'"
            :href="linkFor(row) || undefined"
            :class="[
                'grid items-center gap-3 px-3.5 py-2.5 border-b border-line last:border-b-0 transition-[background-color] duration-100',
                linkFor(row) ? 'cursor-pointer hover:bg-surface-2' : '',
            ]"
            :style="{ gridTemplateColumns: gridTemplate }"
        >
            <div
                v-for="c in columns"
                :key="c.key"
                :class="[
                    'min-w-0',
                    c.align === 'right' ? 'text-right' : c.align === 'center' ? 'text-center' : '',
                ]"
            >
                <slot :name="`cell-${c.key}`" :row="row" :value="row[c.key]">{{ row[c.key] }}</slot>
            </div>
        </component>
    </div>
</template>
