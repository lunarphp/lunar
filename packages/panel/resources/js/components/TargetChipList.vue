<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Icon from './Icon.vue';
import Tooltip from './Tooltip.vue';

const { t } = useI18n();

export interface TargetChip {
    id: number;
    label: string;
    hint: string | null;
}

const props = defineProps<{
    /** Resolved rows per kind, e.g. { products: [...], collections: [...] }. */
    chips: Record<string, TargetChip[]>;
    /** The kinds this bucket can target, in the order they should read. */
    kinds: string[];
    label: string;
    description?: string;
}>();

const emit = defineEmits<{ add: []; remove: [kind: string, id: number] }>();

const total = computed(() => props.kinds.reduce((sum, kind) => sum + (props.chips[kind]?.length ?? 0), 0));
</script>

<template>
    <div>
        <div class="flex items-start justify-between gap-3 mb-2">
            <div class="min-w-0">
                <div class="text-[12.5px] font-medium text-ink-900">{{ label }}</div>
                <div v-if="description" class="text-[11.5px] text-ink-500 leading-normal">{{ description }}</div>
            </div>
            <Button icon="plus" @click="emit('add')">{{ t('discounts.target_add') }}</Button>
        </div>

        <div v-if="total" class="flex flex-col gap-2">
            <div v-for="kind in kinds" :key="kind">
                <template v-if="chips[kind]?.length">
                    <div class="text-[11px] uppercase tracking-wide text-ink-400 mb-1">{{ t(`discounts.kind_${kind}`) }}</div>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="chip in chips[kind]"
                            :key="`${kind}-${chip.id}`"
                            class="inline-flex items-center gap-1.5 max-w-full rounded-full border border-line bg-surface-2 pl-2.5 pr-1 py-1 text-[11.5px] text-ink-900"
                        >
                            <Tooltip :text="chip.hint ?? ''">
                                <span class="truncate" :class="chip.hint ? 'cursor-help' : ''">{{ chip.label }}</span>
                            </Tooltip>
                            <button
                                type="button"
                                class="shrink-0 rounded-full p-0.5 text-ink-400 hover:text-ink-900 hover:bg-line focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                                :aria-label="t('discounts.target_remove', { label: chip.label })"
                                @click="emit('remove', kind, chip.id)"
                            >
                                <Icon name="x" cls="sm" />
                            </button>
                        </span>
                    </div>
                </template>
            </div>
        </div>
        <div v-else class="text-[11.5px] text-ink-500">{{ t('discounts.target_empty') }}</div>
    </div>
</template>
