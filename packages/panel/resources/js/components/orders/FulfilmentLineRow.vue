<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '../Icon.vue';
import type { FulfilmentLineData } from './types';

const props = defineProps<{ line: FulfilmentLineData }>();

const { t } = useI18n();

const expanded = ref(false);

const detailRows = (): { label: string; value: string | null; highlight?: boolean }[] => [
    { label: t('orders.line_unit_price'), value: props.line.unit_price },
    { label: t('orders.line_quantity'), value: String(props.line.quantity) },
    { label: t('orders.line_sub_total'), value: props.line.sub_total },
    ...(props.line.discount_total ? [{ label: t('orders.line_discount'), value: props.line.discount_total }] : []),
    ...props.line.tax.map((tax) => ({ label: tax.label, value: tax.amount })),
    { label: t('orders.line_total'), value: props.line.total, highlight: true },
];
</script>

<template>
    <div class="border-b border-line last:border-0">
        <button
            type="button"
            class="w-full flex items-center gap-2.5 py-2 text-left hover:bg-surface-2/50 rounded-sm"
            :aria-expanded="expanded"
            @click="expanded = !expanded"
        >
            <img v-if="line.thumbnail" :src="line.thumbnail" alt="" class="w-9 h-9 rounded-md object-cover border border-line shrink-0" />
            <div v-else class="w-9 h-9 rounded-md bg-surface-2 border border-line grid place-items-center text-ink-400 shrink-0">
                <Icon name="image" cls="sm" />
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[12.5px] text-ink-900 truncate">{{ line.description }}</div>
                <div class="flex items-center gap-1.5 text-[11px] text-ink-500">
                    <span v-if="line.option" class="truncate">{{ line.option }}</span>
                    <span v-if="line.option && line.identifier">·</span>
                    <span v-if="line.identifier" class="font-mono">{{ line.identifier }}</span>
                </div>
            </div>
            <span class="text-[12.5px] text-ink-700 [font-variant-numeric:tabular-nums] shrink-0">
                {{ line.quantity }} <span class="text-ink-400">@</span> {{ line.unit_price }}
            </span>
        </button>

        <dl v-if="expanded" class="ml-[46px] mb-2 text-[12px] max-w-[320px]" data-testid="line-detail">
            <div
                v-for="(row, index) in detailRows()"
                :key="index"
                class="flex justify-between py-0.5"
                :class="row.highlight ? 'border-t border-line mt-1 pt-1 font-medium text-ink-900' : 'text-ink-700'"
            >
                <dt :class="row.highlight ? '' : 'text-ink-500'">{{ row.label }}</dt>
                <dd class="[font-variant-numeric:tabular-nums]">{{ row.value }}</dd>
            </div>
            <p v-if="line.notes" class="m-0 mt-1.5 italic text-ink-500">{{ line.notes }}</p>
        </dl>
    </div>
</template>
