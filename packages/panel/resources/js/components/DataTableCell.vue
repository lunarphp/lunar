<script setup lang="ts">
import { computed } from 'vue';
import Icon from './Icon.vue';
import StatusBadge from './StatusBadge.vue';

export interface DataTableColumnType {
    name: string;
    options: Record<string, unknown>;
}

// Default cell renderer for columns without a page-provided cell slot — in
// practice add-on columns, which can never fill one. Renders the column's
// registered add-on component if it has one, else a generic renderer for the
// column's declared type (badge, date, boolean, currency, image), else text.
const props = defineProps<{
    component?: string;
    type?: DataTableColumnType;
    row: Record<string, unknown>;
    value: unknown;
}>();

// Resolved from the add-on registry; an unregistered name warns once (see
// registry.ts) and the cell falls back to the type/text renderers.
const custom = computed(() => (props.component ? window.LunarPanel.resolveExtensionComponent(props.component) : undefined));

const typeName = computed(() => props.type?.name);
const options = computed(() => props.type?.options ?? {});

const isEmpty = computed(() => props.value === null || props.value === undefined || props.value === '');

const DATE_STYLES = new Set(['short', 'medium', 'long', 'full']);

const formattedDate = computed(() => {
    const date = new Date(String(props.value));

    if (Number.isNaN(date.getTime())) {
        return String(props.value);
    }

    const format = options.value.format;
    const dateStyle = typeof format === 'string' && DATE_STYLES.has(format) ? (format as Intl.DateTimeFormatOptions['dateStyle']) : undefined;

    return dateStyle ? new Intl.DateTimeFormat(undefined, { dateStyle }).format(date) : date.toLocaleDateString();
});

const formattedCurrency = computed(() => {
    const amount = typeof props.value === 'number' ? props.value : Number(props.value);
    const code = options.value.code;

    if (!Number.isFinite(amount) || typeof code !== 'string' || !code) {
        return String(props.value);
    }

    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: code }).format(amount);
    } catch {
        return String(props.value);
    }
});
</script>

<template>
    <component :is="custom" v-if="custom" :row="row" :value="value" />
    <span v-else-if="isEmpty" class="text-[12.5px] text-ink-400">—</span>
    <StatusBadge v-else-if="typeName === 'badge'" size="sm">{{ value }}</StatusBadge>
    <span v-else-if="typeName === 'boolean'" class="inline-flex text-sage-ink">
        <Icon v-if="value" name="check" cls="sm" />
        <span v-else class="text-[12.5px] text-ink-400">—</span>
    </span>
    <span v-else-if="typeName === 'date'" class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ formattedDate }}</span>
    <span v-else-if="typeName === 'currency'" class="text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ formattedCurrency }}</span>
    <img v-else-if="typeName === 'image'" :src="String(value)" alt="" class="w-7 h-7 rounded-md border border-line object-cover" />
    <template v-else>{{ value }}</template>
</template>
