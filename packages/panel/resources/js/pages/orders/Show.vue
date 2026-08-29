<script setup lang="ts">
import { computed, ref } from 'vue';
import { Deferred, Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../../components/Button.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import Breadcrumbs from '../../components/Breadcrumbs.vue';
import Icon from '../../components/Icon.vue';
import Section from '../../components/Section.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import Dialog from '../../components/Dialog.vue';
import TextInput from '../../components/TextInput.vue';
import Textarea from '../../components/Textarea.vue';
import Select from '../../components/Select.vue';
import Toggle from '../../components/Toggle.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import FulfilmentCard, { type FulfilmentCardAction } from '../../components/orders/FulfilmentCard.vue';
import FulfilmentLineRow from '../../components/orders/FulfilmentLineRow.vue';
import ShipFulfilmentDialog from '../../components/orders/ShipFulfilmentDialog.vue';
import SplitFulfilmentDialog from '../../components/orders/SplitFulfilmentDialog.vue';
import MergeFulfilmentDialog from '../../components/orders/MergeFulfilmentDialog.vue';
import HoldFulfilmentDialog from '../../components/orders/HoldFulfilmentDialog.vue';
import AddTrackingDialog from '../../components/orders/AddTrackingDialog.vue';
import ChangeLocationDialog from '../../components/orders/ChangeLocationDialog.vue';
import FulfilmentConfirmDialog, { type PendingFulfilmentAction } from '../../components/orders/FulfilmentConfirmDialog.vue';
import OrderAddressDialog from '../../components/orders/OrderAddressDialog.vue';
import RefundComposerDialog from '../../components/orders/RefundComposerDialog.vue';
import SettlementBanner from '../../components/orders/SettlementBanner.vue';
import type { BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import type { CarrierData, FulfilmentData, FulfilmentLineData, LocationData, RefundableLineData, SettlementData, ShippingLineData } from '../../components/orders/types';

interface Charge { id: number; reference: string | null; amount: number; amount_formatted: string | null }

type Tone = 'sage' | 'warn' | 'danger' | 'archived' | 'neutral';

interface Address {
    id: number;
    type: string;
    title: string | null;
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    tax_identifier: string | null;
    line_one: string | null;
    line_two: string | null;
    line_three: string | null;
    city: string | null;
    state: string | null;
    postcode: string | null;
    country: string | null;
    country_id: number | null;
    contact_email: string | null;
    contact_phone: string | null;
    delivery_instructions: string | null;
    shipping_option: string | null;
    update_url: string;
}

const props = defineProps<{
    order: {
        id: number;
        reference: string;
        customer_reference: string | null;
        payment_status: string;
        payment_status_label: string;
        fulfilment_status: string;
        fulfilment_status_label: string;
        lifecycle: string;
        lifecycle_label: string;
        cancelled: boolean;
        cancel_reason_label: string | null;
        cancel_note: string | null;
        channel: string | null;
        new_customer: boolean;
        notes: string | null;
        meta: Record<string, unknown> | null;
        placed_at: string | null;
        created_at: string;
        closed_at: string | null;
        cancelled_at: string | null;
    };
    otherLines: FulfilmentLineData[];
    refundableLines: RefundableLineData[];
    shippingLines: ShippingLineData[];
    fulfilments: FulfilmentData[];
    transactions: { id: number; type: string; success: boolean; driver: string; amount: string | null; reference: string | null; status: string | null; card_type: string | null; last_four: string | null; created_at: string; lines_summary: string | null }[];
    totals: { sub_total: string | null; discount_total: string | null; shipping_total: string | null; tax_total: string | null; total: string | null; refunded: string | null; net: string | null };
    settlement: SettlementData;
    customer: { name: string | null; email: string | null; new_customer: boolean; url: string | null };
    shippingAddress: Address | null;
    billingAddress: Address | null;
    tags: string[];
    actions: { can_capture: boolean; can_refund: boolean; can_cancel: boolean; is_open: boolean };
    intents: Charge[];
    charges: Charge[];
    availableToRefund: number;
    availableToRefundFormatted: string | null;
    cancelReasons: Record<string, string>;
    notifications: Record<string, string>;
    carriers: CarrierData[];
    holdReasons: Record<string, string>;
    locations: LocationData[];
    shippingOption: { name: string; identifier: string | null; price: string | null } | null;
    countries: { id: number; name: string }[];
    urls: { index: string; capture: string; refund: string; cancel: string; notify: string; note: string; tags: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.sales') },
    { label: t('orders.title'), href: props.urls.index },
    { label: props.order.reference, current: true },
]);

const PAYMENT_TONES: Record<string, Tone> = {
    paid: 'sage',
    authorized: 'warn',
    pending: 'warn',
    'partially-paid': 'warn',
    'partially-refunded': 'danger',
    refunded: 'danger',
    voided: 'danger',
};
const FULFILMENT_TONES: Record<string, Tone> = {
    fulfilled: 'sage',
    unfulfilled: 'warn',
    'partially-fulfilled': 'warn',
    'partially-returned': 'warn',
    returned: 'danger',
};
const paymentTone = (key: string): Tone => PAYMENT_TONES[key] ?? 'neutral';
const fulfilmentTone = (key: string): Tone => FULFILMENT_TONES[key] ?? 'neutral';

const TXN_TYPE_LABELS: Record<string, string> = {
    capture: t('orders.txn_type_capture'),
    refund: t('orders.txn_type_refund'),
    intent: t('orders.txn_type_intent'),
};
const txnTypeLabel = (type: string): string => TXN_TYPE_LABELS[type] ?? type;

const page = usePage();
const activities = computed(() => (page.props.activities as any[] | undefined) ?? []);

const metaEntries = computed(() => Object.entries(props.order.meta ?? {}));

// Which action dialog is open (null = none).
const dialog = ref<null | 'capture' | 'refund' | 'cancel' | 'notify'>(null);
const closeDialog = (): void => {
    dialog.value = null;
};

const captureForm = useForm({ transaction_id: props.intents[0]?.id ?? null, amount: props.intents[0]?.amount ?? 0 });
const cancelForm = useForm({ reason: '' as string, note: '', notify: true });
const notifyForm = useForm({ notification: Object.keys(props.notifications)[0] ?? '', message: '' });

const submitCapture = (): void => captureForm.post(props.urls.capture, { preserveScroll: true, onSuccess: closeDialog });
const submitCancel = (): void => cancelForm.post(props.urls.cancel, { preserveScroll: true, onSuccess: closeDialog });

// The settlement banner's actions pre-fill with the variance rather than the
// dialog's usual default (the full intent / the full available-to-refund).
const refundPrefillAdjustment = ref(0);
const openCaptureFromSettlement = (): void => {
    captureForm.transaction_id = props.intents[0]?.id ?? null;
    captureForm.amount = Math.min(props.settlement.varianceMajor, props.intents[0]?.amount ?? 0);
    dialog.value = 'capture';
};
const openRefundFromSettlement = (): void => {
    refundPrefillAdjustment.value = Math.min(props.settlement.varianceMajor, props.availableToRefund);
    dialog.value = 'refund';
};
const submitNotify = (): void => notifyForm.post(props.urls.notify, { preserveScroll: true, onSuccess: closeDialog });

// Fulfilments — the card emits an action, this routes it to the right dialog
// or confirm step. Form-bearing actions (ship, split, merge, hold, tracking,
// location) get a dialog; the rest confirm and post.
type FulfilmentDialog = 'ship' | 'split' | 'merge' | 'hold' | 'tracking' | 'location';

const activeFulfilment = ref<FulfilmentData | null>(null);
const fulfilmentDialog = ref<FulfilmentDialog | null>(null);
const pendingAction = ref<PendingFulfilmentAction | null>(null);

const closeFulfilmentDialog = (): void => {
    fulfilmentDialog.value = null;
    activeFulfilment.value = null;
};

const shipNotify = computed(
    () => activeFulfilment.value?.transitions.find((tr) => tr.via === 'ship')?.notify ?? false,
);

const openFulfilmentDialog = (fulfilment: FulfilmentData, dialog: FulfilmentDialog): void => {
    activeFulfilment.value = fulfilment;
    fulfilmentDialog.value = dialog;
};

const onFulfilmentAction = (fulfilment: FulfilmentData, action: FulfilmentCardAction): void => {
    switch (action.type) {
        case 'ship':
            return openFulfilmentDialog(fulfilment, 'ship');
        case 'split':
            return openFulfilmentDialog(fulfilment, 'split');
        case 'merge':
            return openFulfilmentDialog(fulfilment, 'merge');
        case 'hold':
            return openFulfilmentDialog(fulfilment, 'hold');
        case 'add-tracking':
            return openFulfilmentDialog(fulfilment, 'tracking');
        case 'change-location':
            return openFulfilmentDialog(fulfilment, 'location');
        case 'fulfil':
            pendingAction.value = {
                title: fulfilment.fulfil_label,
                url: fulfilment.urls.fulfil,
                showNotify: fulfilment.transitions.find((tr) => tr.via === 'fulfil')?.notify ?? false,
            };
            return;
        case 'transition':
            pendingAction.value = {
                title: t('orders.transition_confirm', { status: action.transition.label }),
                confirmLabel: action.transition.label,
                url: fulfilment.urls.transition,
                data: { state: action.transition.state },
                showNotify: action.transition.notify,
            };
            return;
        case 'return':
            pendingAction.value = {
                title: t('orders.transition_confirm', { status: action.transition.label }),
                confirmLabel: action.transition.label,
                url: fulfilment.urls.return,
                showNotify: action.transition.notify,
            };
            return;
        case 'undo-return':
            pendingAction.value = {
                title: t('orders.action_undo_return'),
                description: t('orders.undo_return_confirm'),
                url: fulfilment.urls.undoReturn,
            };
            return;
        case 'release':
            pendingAction.value = {
                title: t('orders.action_release'),
                description: t('orders.release_confirm'),
                url: fulfilment.urls.release,
            };
            return;
        case 'cancel':
            pendingAction.value = {
                title: t('orders.cancel_fulfilment_confirm'),
                description: t('orders.cancel_fulfilment_warning'),
                confirmLabel: t('orders.action_cancel_fulfilment'),
                tone: 'danger',
                url: fulfilment.urls.cancel,
            };
            return;
        case 'remove-tracking':
            pendingAction.value = {
                title: t('orders.remove_tracking_confirm'),
                confirmLabel: t('orders.tracking_remove_row'),
                tone: 'danger',
                url: action.tracking.destroy_url,
                method: 'delete',
            };
    }
};

// Address corrections — one dialog, pointed at whichever address is being edited.
const editingAddress = ref<Address | null>(null);
const addressDialogTitle = computed(() =>
    editingAddress.value?.type === 'billing' ? t('orders.edit_billing_address') : t('orders.edit_shipping_address'),
);

// Inline note editing.
const editingNote = ref(false);
const noteForm = useForm({ notes: props.order.notes ?? '' });
const saveNote = (): void => noteForm.put(props.urls.note, { preserveScroll: true, onSuccess: () => (editingNote.value = false) });

// Inline tag editing.
const editingTags = ref(false);
const tagsForm = useForm<{ tags: string[] }>({ tags: [...props.tags] });
const newTag = ref('');
const addTag = (): void => {
    const value = newTag.value.trim().toUpperCase();
    if (value && !tagsForm.tags.includes(value)) {
        tagsForm.tags.push(value);
    }
    newTag.value = '';
};
const removeTag = (tag: string): void => {
    tagsForm.tags = tagsForm.tags.filter((t) => t !== tag);
};
const saveTags = (): void => tagsForm.put(props.urls.tags, { preserveScroll: true, onSuccess: () => (editingTags.value = false) });

const formatDate = (value: string): string =>
    new Date(value).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
const formatDateTime = (value: string): string =>
    new Date(value).toLocaleString(undefined, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const addressLines = (address: Address): string[] =>
    [
        [address.first_name, address.last_name].filter(Boolean).join(' '),
        address.company_name,
        address.line_one,
        address.line_two,
        address.line_three,
        [address.city, address.postcode].filter(Boolean).join(' '),
        address.state,
        address.country,
    ].filter((line): line is string => !!line && line.trim().length > 0);
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Order detail" class="contents">
            <Breadcrumbs :items="breadcrumbs" />

            <PageHeader :title="order.reference" icon="cart">
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <StatusBadge :tone="paymentTone(order.payment_status)" size="sm" dot>{{ order.payment_status_label }}</StatusBadge>
                        <StatusBadge :tone="fulfilmentTone(order.fulfilment_status)" size="sm" dot>{{ order.fulfilment_status_label }}</StatusBadge>
                        <StatusBadge v-if="order.cancelled" tone="danger" size="sm">{{ t('orders.lifecycle_cancelled') }}</StatusBadge>
                        <template v-if="order.channel">
                            <span class="text-ink-500">·</span>
                            <span class="text-ink-700">{{ order.channel }}</span>
                        </template>
                        <template v-if="order.placed_at">
                            <span class="text-ink-500">·</span>
                            <span>{{ formatDateTime(order.placed_at) }}</span>
                        </template>
                        <template v-if="totals.total">
                            <span class="text-ink-500">·</span>
                            <span class="font-medium text-ink-900 [font-variant-numeric:tabular-nums]">{{ totals.total }}</span>
                        </template>
                    </div>
                </template>
                <template #actions>
                    <Button v-if="actions.can_capture" variant="primary" icon="check" @click="dialog = 'capture'">{{ t('orders.action_capture') }}</Button>
                    <Button v-if="actions.can_refund" icon="undo" @click="dialog = 'refund'">{{ t('orders.action_refund') }}</Button>
                    <Button icon="mail" @click="dialog = 'notify'">{{ t('orders.action_notify') }}</Button>
                    <Button v-if="actions.can_cancel" icon="x" class="!text-danger" @click="dialog = 'cancel'">{{ t('orders.action_cancel') }}</Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" :order="order" :shipping-option="shippingOption" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <SettlementBanner
                            :settlement="settlement"
                            :can-capture="actions.can_capture"
                            :can-refund="actions.can_refund"
                            @capture="openCaptureFromSettlement"
                            @refund="openRefundFromSettlement"
                        />

                        <!-- Fulfilments — one card per fulfilment, the order's contents. -->
                        <Section :title="t('orders.section_fulfilments')">
                            <template #actions>
                                <span class="text-[11.5px] text-ink-500">
                                    {{ t('orders.fulfilment_count', { count: fulfilments.length }, fulfilments.length) }}
                                </span>
                            </template>
                            <div v-if="fulfilments.length" class="flex flex-col gap-3">
                                <FulfilmentCard
                                    v-for="fulfilment in fulfilments"
                                    :key="fulfilment.id"
                                    :fulfilment="fulfilment"
                                    @action="onFulfilmentAction(fulfilment, $event)"
                                />
                            </div>
                            <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.fulfilments_empty') }}</p>
                        </Section>

                        <!-- Lines with no fulfilment: services and other non-fulfillable purchasables. -->
                        <Section v-if="otherLines.length" :title="t('orders.section_other_items')">
                            <FulfilmentLineRow v-for="line in otherLines" :key="line.id" :line="line" />
                        </Section>

                        <!-- Totals -->
                        <Section :title="t('orders.section_totals')">
                            <dl class="text-[12.5px] max-w-[360px] ml-auto">
                                <div class="flex justify-between py-1">
                                    <dt class="text-ink-500">{{ t('orders.totals_subtotal') }}</dt>
                                    <dd class="text-ink-900 [font-variant-numeric:tabular-nums]">{{ totals.sub_total }}</dd>
                                </div>
                                <div v-if="totals.discount_total" class="flex justify-between py-1">
                                    <dt class="text-ink-500">{{ t('orders.totals_discount') }}</dt>
                                    <dd class="text-ink-900 [font-variant-numeric:tabular-nums]">−{{ totals.discount_total }}</dd>
                                </div>
                                <div class="flex justify-between py-1">
                                    <dt class="text-ink-500">{{ t('orders.totals_shipping') }}</dt>
                                    <dd class="text-ink-900 [font-variant-numeric:tabular-nums]">{{ totals.shipping_total }}</dd>
                                </div>
                                <div class="flex justify-between py-1">
                                    <dt class="text-ink-500">{{ t('orders.totals_tax') }}</dt>
                                    <dd class="text-ink-900 [font-variant-numeric:tabular-nums]">{{ totals.tax_total }}</dd>
                                </div>
                                <div class="flex justify-between py-1.5 border-t border-line mt-1 font-semibold">
                                    <dt class="text-ink-900">{{ t('orders.totals_total') }}</dt>
                                    <dd class="text-ink-900 [font-variant-numeric:tabular-nums]">{{ totals.total }}</dd>
                                </div>
                                <template v-if="totals.refunded">
                                    <div class="flex justify-between py-1 text-danger">
                                        <dt>{{ t('orders.totals_refunded') }}</dt>
                                        <dd class="[font-variant-numeric:tabular-nums]">−{{ totals.refunded }}</dd>
                                    </div>
                                    <div class="flex justify-between py-1 font-semibold">
                                        <dt class="text-ink-900">{{ t('orders.totals_net') }}</dt>
                                        <dd class="text-ink-900 [font-variant-numeric:tabular-nums]">{{ totals.net }}</dd>
                                    </div>
                                </template>
                            </dl>
                        </Section>

                        <!-- Transactions -->
                        <Section :title="t('orders.section_transactions')">
                            <div v-if="transactions.length" class="overflow-x-auto">
                                <table class="w-full text-[12.5px] border-collapse">
                                    <thead>
                                        <tr class="text-ink-500 text-[11px] text-left border-b border-line">
                                            <th class="font-medium py-1.5 pr-2">{{ t('orders.txn_type') }}</th>
                                            <th class="font-medium py-1.5 px-2">{{ t('orders.txn_method') }}</th>
                                            <th class="font-medium py-1.5 px-2">{{ t('orders.txn_reference') }}</th>
                                            <th class="font-medium py-1.5 px-2">{{ t('orders.txn_date') }}</th>
                                            <th class="font-medium py-1.5 pl-2 text-right">{{ t('orders.txn_amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="txn in transactions" :key="txn.id" class="border-b border-line last:border-0">
                                            <td class="py-2 pr-2">
                                                <StatusBadge :tone="txn.type === 'refund' ? 'danger' : (txn.success ? 'sage' : 'warn')" size="sm">{{ txnTypeLabel(txn.type) }}</StatusBadge>
                                            </td>
                                            <td class="py-2 px-2 text-ink-700">
                                                {{ txn.card_type || txn.driver }}<span v-if="txn.last_four" class="text-ink-500"> ····{{ txn.last_four }}</span>
                                                <div v-if="txn.lines_summary" class="text-[11px] text-ink-500">{{ txn.lines_summary }}</div>
                                            </td>
                                            <td class="py-2 px-2 text-ink-500 font-mono text-[11px]">{{ txn.reference ?? '—' }}</td>
                                            <td class="py-2 px-2 text-ink-500 [font-variant-numeric:tabular-nums]">{{ formatDateTime(txn.created_at) }}</td>
                                            <td class="py-2 pl-2 text-right [font-variant-numeric:tabular-nums]" :class="txn.type === 'refund' ? 'text-danger' : 'text-ink-900'">
                                                <span v-if="txn.type === 'refund'">−</span>{{ txn.amount }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.transactions_empty') }}</p>
                        </Section>

                        <!-- The delivery method chosen at checkout. -->
                        <Section v-if="shippingOption" :title="t('orders.section_shipping')">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-md bg-surface-2 border border-line grid place-items-center text-ink-700 shrink-0">
                                    <Icon name="truck" cls="sm" />
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[12.5px] text-ink-900 font-medium">{{ shippingOption.name }}</div>
                                    <div class="text-[11px] text-ink-500">
                                        <span v-if="shippingOption.identifier" class="font-mono">{{ shippingOption.identifier }}</span>
                                        <span v-if="shippingOption.identifier && shippingOption.price"> · </span>
                                        <span v-if="shippingOption.price" class="[font-variant-numeric:tabular-nums]">{{ shippingOption.price }}</span>
                                    </div>
                                </div>
                            </div>
                        </Section>

                        <!-- Activity -->
                        <Section :title="t('orders.section_activity')">
                            <Deferred data="activities">
                                <template #fallback>
                                    <div class="h-16 rounded-md bg-surface-2 animate-pulse" role="status" :aria-label="t('common.loading')" />
                                </template>
                                <ActivityTimeline v-if="activities.length" :events="activities" :reverse="false" />
                                <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.activity_empty') }}</p>
                            </Deferred>
                        </Section>
                    </div>

                    <!-- Sidebar -->
                    <div class="min-w-0">
                        <PageZone region="sidebar" position="before" :order="order" :shipping-option="shippingOption" />

                        <SideCard :title="t('orders.side_status')">
                            <dl class="text-[12.5px] space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <dt class="text-ink-500">{{ t('orders.column_payment') }}</dt>
                                    <dd><StatusBadge :tone="paymentTone(order.payment_status)" size="sm" dot>{{ order.payment_status_label }}</StatusBadge></dd>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <dt class="text-ink-500">{{ t('orders.column_fulfilment') }}</dt>
                                    <dd><StatusBadge :tone="fulfilmentTone(order.fulfilment_status)" size="sm" dot>{{ order.fulfilment_status_label }}</StatusBadge></dd>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <dt class="text-ink-500">{{ t('orders.filter_lifecycle') }}</dt>
                                    <dd class="text-ink-900">{{ order.lifecycle_label }}</dd>
                                </div>
                                <div v-if="order.cancel_reason_label" class="flex items-center justify-between gap-2">
                                    <dt class="text-ink-500">{{ t('orders.cancel_reason') }}</dt>
                                    <dd class="text-ink-900">{{ order.cancel_reason_label }}</dd>
                                </div>
                            </dl>
                        </SideCard>

                        <SideCard :title="t('orders.side_customer')">
                            <div class="text-[12.5px]">
                                <div class="flex items-center gap-2">
                                    <span class="text-ink-900">{{ customer.name ?? t('orders.guest') }}</span>
                                    <StatusBadge size="sm" :tone="customer.new_customer ? 'sage' : 'neutral'">
                                        {{ customer.new_customer ? t('orders.new_customer') : t('orders.returning_customer') }}
                                    </StatusBadge>
                                </div>
                                <a v-if="customer.email" :href="`mailto:${customer.email}`" class="text-ink-500 hover:text-ink-900 block mt-0.5 truncate">{{ customer.email }}</a>
                                <Link v-if="customer.url" :href="customer.url" class="text-sage-ink hover:underline inline-flex items-center gap-1 mt-1.5">
                                    <Icon name="externalLink" cls="sm" />{{ t('orders.view_customer') }}
                                </Link>
                            </div>
                        </SideCard>

                        <SideCard v-if="shippingAddress" :title="t('orders.side_shipping_address')">
                            <template #actions>
                                <button type="button" class="text-[11.5px] text-ink-500 hover:text-ink-900" @click="editingAddress = shippingAddress">
                                    {{ t('common.edit') }}
                                </button>
                            </template>
                            <address class="not-italic text-[12.5px] text-ink-700 leading-relaxed">
                                <div v-for="(line, i) in addressLines(shippingAddress)" :key="i">{{ line }}</div>
                                <div v-if="shippingAddress.contact_phone" class="mt-1.5 pt-1.5 border-t border-line">
                                    <a :href="`tel:${shippingAddress.contact_phone}`" class="text-ink-500 hover:text-ink-900">{{ shippingAddress.contact_phone }}</a>
                                </div>
                            </address>
                        </SideCard>

                        <SideCard v-if="billingAddress" :title="t('orders.side_billing_address')">
                            <template #actions>
                                <button type="button" class="text-[11.5px] text-ink-500 hover:text-ink-900" @click="editingAddress = billingAddress">
                                    {{ t('common.edit') }}
                                </button>
                            </template>
                            <address class="not-italic text-[12.5px] text-ink-700 leading-relaxed">
                                <div v-for="(line, i) in addressLines(billingAddress)" :key="i">{{ line }}</div>
                                <div v-if="billingAddress.contact_phone" class="mt-1.5 pt-1.5 border-t border-line">
                                    <a :href="`tel:${billingAddress.contact_phone}`" class="text-ink-500 hover:text-ink-900">{{ billingAddress.contact_phone }}</a>
                                </div>
                            </address>
                        </SideCard>

                        <SideCard :title="t('orders.side_tags')">
                            <template #actions>
                                <button type="button" class="text-[11.5px] text-ink-500 hover:text-ink-900" @click="editingTags = !editingTags">
                                    {{ editingTags ? t('common.cancel') : t('common.edit') }}
                                </button>
                            </template>
                            <template v-if="!editingTags">
                                <div v-if="tags.length" class="flex flex-wrap gap-1">
                                    <StatusBadge v-for="tag in tags" :key="tag" size="sm">{{ tag }}</StatusBadge>
                                </div>
                                <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.no_tags') }}</p>
                            </template>
                            <div v-else>
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <StatusBadge v-for="tag in tagsForm.tags" :key="tag" size="sm">
                                        {{ tag }}
                                        <button type="button" class="ml-1 text-ink-400 hover:text-danger" @click="removeTag(tag)"><Icon name="x" cls="sm" /></button>
                                    </StatusBadge>
                                </div>
                                <div class="flex gap-1.5">
                                    <TextInput v-model="newTag" :placeholder="t('orders.add_tag')" @keydown.enter.prevent="addTag" />
                                    <Button icon="plus" @click="addTag" />
                                </div>
                                <Button variant="primary" size="sm" class="mt-2" :disabled="tagsForm.processing" @click="saveTags">{{ t('common.save') }}</Button>
                            </div>
                        </SideCard>

                        <SideCard :title="t('orders.side_notes')">
                            <template #actions>
                                <button type="button" class="text-[11.5px] text-ink-500 hover:text-ink-900" @click="editingNote = !editingNote">
                                    {{ editingNote ? t('common.cancel') : t('common.edit') }}
                                </button>
                            </template>
                            <template v-if="!editingNote">
                                <p v-if="order.notes" class="m-0 text-[12.5px] text-ink-700 whitespace-pre-line">{{ order.notes }}</p>
                                <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.no_notes') }}</p>
                            </template>
                            <div v-else>
                                <Textarea v-model="noteForm.notes" :rows="4" :placeholder="t('orders.note_placeholder')" />
                                <Button variant="primary" size="sm" class="mt-2" :disabled="noteForm.processing" @click="saveNote">{{ t('common.save') }}</Button>
                            </div>
                        </SideCard>

                        <SideCard :title="t('orders.side_metadata')">
                            <dl v-if="metaEntries.length" class="text-[12px] space-y-1.5">
                                <div v-for="[key, value] in metaEntries" :key="key" class="flex justify-between gap-2">
                                    <dt class="text-ink-500 font-mono">{{ key }}</dt>
                                    <dd class="text-ink-900 text-right truncate">{{ value }}</dd>
                                </div>
                            </dl>
                            <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.no_metadata') }}</p>
                        </SideCard>

                        <PageZone region="sidebar" position="after" :order="order" :shipping-option="shippingOption" />
                    </div>
                </div>

                <PageZone region="main" position="after" :order="order" :shipping-option="shippingOption" />
            </div>

            <!-- Capture -->
            <Dialog :open="dialog === 'capture'" :title="t('orders.action_capture')" size="sm" @update:open="(v: boolean) => !v && closeDialog()">
                <div class="space-y-3">
                    <div v-if="intents.length > 1">
                        <FieldLabel>{{ t('orders.capture_transaction') }}</FieldLabel>
                        <Select v-model="captureForm.transaction_id">
                            <option v-for="intent in intents" :key="intent.id" :value="intent.id">{{ intent.reference || ('#' + intent.id) }} — {{ intent.amount_formatted }}</option>
                        </Select>
                    </div>
                    <div>
                        <FieldLabel>{{ t('orders.capture_amount') }}</FieldLabel>
                        <TextInput v-model="captureForm.amount" type="number" :invalid="!!captureForm.errors.amount" />
                        <p v-if="captureForm.errors.amount" class="text-danger text-[11px] mt-1">{{ captureForm.errors.amount }}</p>
                    </div>
                </div>
                <template #footer>
                    <Button variant="ghost" @click="closeDialog">{{ t('common.cancel') }}</Button>
                    <Button variant="primary" :disabled="captureForm.processing" @click="submitCapture">{{ t('orders.action_capture') }}</Button>
                </template>
            </Dialog>

            <!-- Refund -->
            <RefundComposerDialog
                :open="dialog === 'refund'"
                :lines="refundableLines"
                :shipping-lines="shippingLines"
                :charges="charges"
                :available-to-refund="availableToRefund"
                :available-to-refund-formatted="availableToRefundFormatted"
                :url="urls.refund"
                :prefill-adjustment="refundPrefillAdjustment"
                @update:open="(v: boolean) => !v && closeDialog()"
            />

            <!-- Cancel -->
            <Dialog :open="dialog === 'cancel'" :title="t('orders.action_cancel')" size="sm" @update:open="(v: boolean) => !v && closeDialog()">
                <div class="space-y-3">
                    <div>
                        <FieldLabel>{{ t('orders.cancel_reason') }}</FieldLabel>
                        <Select v-model="cancelForm.reason">
                            <option value="">{{ t('orders.no_reason') }}</option>
                            <option v-for="(label, key) in cancelReasons" :key="key" :value="key">{{ label }}</option>
                        </Select>
                    </div>
                    <div>
                        <FieldLabel>{{ t('orders.cancel_note') }}</FieldLabel>
                        <Textarea v-model="cancelForm.note" :rows="2" />
                    </div>
                    <label class="flex items-center gap-2 text-[12.5px] text-ink-700">
                        <Toggle :on="cancelForm.notify" @toggle="cancelForm.notify = !cancelForm.notify" />
                        {{ t('orders.cancel_notify') }}
                    </label>
                </div>
                <template #footer>
                    <Button variant="ghost" @click="closeDialog">{{ t('common.cancel') }}</Button>
                    <Button variant="primary" class="!bg-danger hover:!bg-danger/90 text-paper" :disabled="cancelForm.processing" @click="submitCancel">{{ t('orders.action_cancel') }}</Button>
                </template>
            </Dialog>

            <!-- Notify -->
            <Dialog :open="dialog === 'notify'" :title="t('orders.action_notify')" size="sm" @update:open="(v: boolean) => !v && closeDialog()">
                <div class="space-y-3">
                    <div>
                        <FieldLabel>{{ t('orders.notify_notification') }}</FieldLabel>
                        <Select v-model="notifyForm.notification">
                            <option v-for="(label, key) in notifications" :key="key" :value="key">{{ label }}</option>
                        </Select>
                    </div>
                    <div>
                        <FieldLabel>{{ t('orders.notify_message') }}</FieldLabel>
                        <Textarea v-model="notifyForm.message" :rows="3" :placeholder="t('orders.notify_message_placeholder')" />
                    </div>
                </div>
                <template #footer>
                    <Button variant="ghost" @click="closeDialog">{{ t('common.cancel') }}</Button>
                    <Button variant="primary" :disabled="notifyForm.processing" @click="submitNotify">{{ t('orders.action_notify') }}</Button>
                </template>
            </Dialog>

            <!-- Fulfilment dialogs -->
            <ShipFulfilmentDialog
                :open="fulfilmentDialog === 'ship'"
                :fulfilment="activeFulfilment"
                :carriers="carriers"
                :show-notify="shipNotify"
                @update:open="(v: boolean) => !v && closeFulfilmentDialog()"
            />
            <SplitFulfilmentDialog
                :open="fulfilmentDialog === 'split'"
                :fulfilment="activeFulfilment"
                @update:open="(v: boolean) => !v && closeFulfilmentDialog()"
            />
            <MergeFulfilmentDialog
                :open="fulfilmentDialog === 'merge'"
                :fulfilment="activeFulfilment"
                @update:open="(v: boolean) => !v && closeFulfilmentDialog()"
            />
            <HoldFulfilmentDialog
                :open="fulfilmentDialog === 'hold'"
                :fulfilment="activeFulfilment"
                :hold-reasons="holdReasons"
                @update:open="(v: boolean) => !v && closeFulfilmentDialog()"
            />
            <AddTrackingDialog
                :open="fulfilmentDialog === 'tracking'"
                :fulfilment="activeFulfilment"
                :carriers="carriers"
                @update:open="(v: boolean) => !v && closeFulfilmentDialog()"
            />
            <ChangeLocationDialog
                :open="fulfilmentDialog === 'location'"
                :fulfilment="activeFulfilment"
                :locations="locations"
                @update:open="(v: boolean) => !v && closeFulfilmentDialog()"
            />
            <FulfilmentConfirmDialog :action="pendingAction" @close="pendingAction = null" />
            <OrderAddressDialog
                :open="!!editingAddress"
                :address="editingAddress"
                :title="addressDialogTitle"
                :countries="countries"
                @update:open="(v: boolean) => !v && (editingAddress = null)"
            />
        </div>
    </PanelLayout>
</template>
