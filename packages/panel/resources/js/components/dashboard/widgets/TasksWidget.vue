<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '../../Icon.vue';

interface TaskRow {
    key: string;
    label: string;
    count: number;
    url: string | null;
}

const props = defineProps<{ data: { tasks: TaskRow[] } }>();

const { t } = useI18n();

const ICONS: Record<string, string> = {
    unfulfilled_orders: 'cart',
    draft_products: 'edit',
    out_of_stock: 'box',
};

const TONES: Record<string, string> = {
    unfulfilled_orders: 'bg-warn-soft border-warn-border text-warn-ink',
    draft_products: 'bg-surface-2 border-line text-ink-700',
    out_of_stock: 'bg-danger-soft border-danger-border text-danger',
};

const pending = computed(() => props.data.tasks.filter((task) => task.count > 0));
</script>

<template>
    <div v-if="!pending.length" class="text-[12.5px] text-ink-500 py-2">
        {{ t('dashboard.tasks_done') }}
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2.5">
        <component
            :is="task.url ? 'a' : 'div'"
            v-for="task in pending"
            :key="task.key"
            :href="task.url ?? undefined"
            :class="[
                'flex items-center gap-3 p-2.5 border border-line rounded-md bg-surface transition-colors',
                task.url ? 'hover:bg-surface-2' : '',
            ]"
        >
            <div :class="['w-9 h-9 rounded-md grid place-items-center border shrink-0', TONES[task.key] || TONES.draft_products]">
                <Icon :name="ICONS[task.key] || 'check'" cls="sm" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[12.5px] text-ink-900 truncate">{{ task.label }}</div>
                <div class="text-[11px] text-ink-500 [font-variant-numeric:tabular-nums]">
                    {{ t('dashboard.task_items', task.count) }}
                </div>
            </div>
        </component>
    </div>
</template>
