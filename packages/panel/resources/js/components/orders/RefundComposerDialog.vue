<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Checkbox from '../Checkbox.vue';
import Dialog from '../Dialog.vue';
import FieldLabel from '../FieldLabel.vue';
import Icon from '../Icon.vue';
import Select from '../Select.vue';
import Textarea from '../Textarea.vue';
import TextInput from '../TextInput.vue';
import Toggle from '../Toggle.vue';
import type { RefundableLineData, ShippingLineData } from './types';

interface Charge {
    id: number;
    reference: string | null;
    amount: number;
    amount_formatted: string | null;
}

const props = defineProps<{
    open: boolean;
    lines: RefundableLineData[];
    shippingLines: ShippingLineData[];
    charges: Charge[];
    availableToRefund: number;
    availableToRefundFormatted: string | null;
    url: string;
    /** Pre-fills the manual adjustment field, e.g. from the settlement banner's variance. */
    prefillAdjustment?: number;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const shippingLine = computed<ShippingLineData | null>(() => props.shippingLines[0] ?? null);

const form = useForm<{
    transaction_id: number | null;
    lines: Record<number, number>;
    shipping: boolean;
    adjustment: number | string;
    notes: string;
    notify: boolean;
}>({
    transaction_id: null,
    lines: {},
    shipping: false,
    adjustment: 0,
    notes: '',
    notify: true,
});

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        form.clearErrors();
        form.transaction_id = props.charges[0]?.id ?? null;
        form.lines = Object.fromEntries(props.lines.map((line) => [line.id, 0]));
        form.shipping = false;
        form.adjustment = props.prefillAdjustment || 0;
        form.notes = '';
        form.notify = true;
    },
    { immediate: true },
);

const clamp = (lineId: number, max: number): void => {
    form.lines[lineId] = Math.max(0, Math.min(max, Math.floor(Number(form.lines[lineId]) || 0)));
};

const linesQuantity = computed(() => Object.values(form.lines).reduce((sum, quantity) => sum + (Number(quantity) || 0), 0));
const linesAmount = computed(() =>
    props.lines.reduce((sum, line) => sum + (Number(form.lines[line.id]) || 0) * line.refund_unit_amount, 0),
);
const shippingAmount = computed(() => (form.shipping && shippingLine.value ? shippingLine.value.amount : 0));
const adjustmentAmount = computed(() => Number(form.adjustment) || 0);
const totalAmount = computed(() => linesAmount.value + shippingAmount.value + adjustmentAmount.value);
const canConfirm = computed(() => totalAmount.value > 0 && totalAmount.value <= props.availableToRefund + 0.001);

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (!canConfirm.value) {
        return;
    }

    form.transform((data) => ({
        transaction_id: data.transaction_id,
        lines: props.lines
            .map((line) => ({ order_line_id: line.id, quantity: data.lines[line.id] ?? 0 }))
            .filter((line) => line.quantity > 0),
        shipping: data.shipping,
        adjustment: data.adjustment || 0,
        notes: data.notes || null,
        notify: data.notify,
    })).post(props.url, { preserveScroll: true, onSuccess: close });
};
</script>

<template>
    <Dialog :open="open" :title="t('orders.action_refund')" size="md" @update:open="(v: boolean) => !v && close()">
        <div class="space-y-3">
            <div v-if="charges.length > 1">
                <FieldLabel>{{ t('orders.refund_transaction') }}</FieldLabel>
                <Select v-model="form.transaction_id">
                    <option v-for="charge in charges" :key="charge.id" :value="charge.id">
                        {{ charge.reference || '#' + charge.id }} — {{ charge.amount_formatted }}
                    </option>
                </Select>
            </div>

            <ul v-if="lines.length || shippingLine" class="divide-y divide-line border border-line rounded-md overflow-hidden">
                <li v-for="line in lines" :key="line.id" class="flex items-center gap-3 px-3 py-2.5">
                    <img v-if="line.thumbnail" :src="line.thumbnail" alt="" class="w-9 h-9 rounded-md object-cover border border-line shrink-0" />
                    <div v-else class="w-9 h-9 rounded-md bg-surface-2 border border-line grid place-items-center text-ink-400 shrink-0">
                        <Icon name="image" cls="sm" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[12.5px] text-ink-900 truncate">{{ line.description }}</div>
                        <div class="text-[11px] text-ink-500">{{ t('orders.refund_up_to', { quantity: line.refundable_quantity }) }}</div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <TextInput
                            v-model.number="form.lines[line.id]"
                            type="number"
                            :min="0"
                            :max="line.refundable_quantity"
                            class="!w-[64px] text-right"
                            :aria-label="line.description ?? undefined"
                            @change="clamp(line.id, line.refundable_quantity)"
                        />
                        <span class="text-[11.5px] text-ink-500">/ {{ line.refundable_quantity }}</span>
                    </div>
                </li>

                <li v-if="shippingLine" class="flex items-center gap-3 px-3 py-2.5 cursor-pointer hover:bg-surface-2" @click="form.shipping = !form.shipping">
                    <Checkbox :model-value="form.shipping" :aria-label="t('orders.refund_include_shipping')" @click.stop @update:model-value="(v: boolean) => (form.shipping = v)" />
                    <div class="w-9 h-9 rounded-md bg-surface-2 border border-line grid place-items-center text-ink-700 shrink-0">
                        <Icon name="truck" cls="sm" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[12.5px] text-ink-900 truncate">{{ t('orders.refund_shipping') }}</div>
                        <div class="text-[11px] text-ink-500 truncate">{{ shippingLine.description }}</div>
                    </div>
                    <div class="text-[12.5px] font-medium [font-variant-numeric:tabular-nums]" :class="form.shipping ? 'text-ink-900' : 'text-ink-500'">
                        {{ shippingLine.total }}
                    </div>
                </li>
            </ul>
            <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.refund_nothing_selectable') }}</p>
            <p v-if="form.errors.lines" class="m-0 text-danger text-[11px]">{{ form.errors.lines }}</p>

            <div>
                <FieldLabel :hint="t('orders.refund_adjustment_hint')">{{ t('orders.refund_adjustment') }}</FieldLabel>
                <TextInput v-model="form.adjustment" type="number" :invalid="!!form.errors.adjustment" />
                <p v-if="form.errors.adjustment" class="m-0 text-danger text-[11px] mt-1">{{ form.errors.adjustment }}</p>
            </div>

            <div>
                <FieldLabel>{{ t('orders.refund_notes') }}</FieldLabel>
                <Textarea v-model="form.notes" :rows="2" />
            </div>

            <label class="flex items-center gap-2 text-[12.5px] text-ink-700">
                <Toggle :on="form.notify" @toggle="form.notify = !form.notify" />
                {{ t('orders.refund_notify') }}
            </label>
        </div>
        <template #footer>
            <div class="flex-1 text-[11.5px] text-ink-500 [font-variant-numeric:tabular-nums]">
                <template v-if="totalAmount > 0">
                    {{ t('orders.refund_total', { amount: totalAmount.toFixed(2) }) }}
                    <span v-if="linesQuantity > 0">· {{ t('orders.refund_item_count', { count: linesQuantity }, linesQuantity) }}</span>
                </template>
                <span class="block text-ink-400">{{ t('orders.refund_available', { amount: availableToRefundFormatted }) }}</span>
            </div>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!canConfirm || form.processing" @click="submit">{{ t('orders.action_refund') }}</Button>
        </template>
    </Dialog>
</template>
