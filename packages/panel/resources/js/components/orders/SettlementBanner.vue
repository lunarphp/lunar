<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Icon from '../Icon.vue';
import type { SettlementData } from './types';

const props = defineProps<{
    settlement: SettlementData;
    canCapture: boolean;
    canRefund: boolean;
}>();

const emit = defineEmits<{ capture: []; refund: [] }>();

const { t } = useI18n();

const wrapClasses = computed(() =>
    props.settlement.status === 'refund_due' ? 'bg-danger-soft border-danger-border' : 'bg-warn-soft border-warn-border',
);
const iconClasses = computed(() =>
    props.settlement.status === 'refund_due' ? 'bg-danger-soft border-danger-border text-danger' : 'bg-warn-soft border-warn-border text-warn-ink',
);
</script>

<template>
    <div
        v-if="settlement.status !== 'balanced'"
        class="flex items-start gap-3 rounded-md border px-4 py-3 mb-5"
        :class="wrapClasses"
        data-testid="settlement-banner"
    >
        <div class="w-8 h-8 rounded-md border grid place-items-center shrink-0" :class="iconClasses">
            <Icon name="alertTriangle" cls="sm" />
        </div>
        <div class="min-w-0 flex-1">
            <p class="m-0 text-[13px] font-medium text-ink-900">
                {{
                    settlement.status === 'refund_due'
                        ? t('orders.settlement_refund_due', { amount: settlement.variance })
                        : t('orders.settlement_outstanding', { amount: settlement.variance })
                }}
            </p>
            <p class="m-0 text-[11.5px] text-ink-500 mt-0.5">
                {{ t('orders.settlement_detail', { captured: settlement.captured ?? '—', refunded: settlement.refunded ?? '—', total: settlement.total }) }}
            </p>
        </div>
        <div class="shrink-0">
            <Button v-if="settlement.status === 'outstanding' && canCapture" size="sm" icon="check" @click="emit('capture')">
                {{ t('orders.settlement_take_payment') }}
            </Button>
            <Button v-else-if="settlement.status === 'refund_due' && canRefund" size="sm" icon="undo" @click="emit('refund')">
                {{ t('orders.action_refund') }}
            </Button>
        </div>
    </div>
</template>
