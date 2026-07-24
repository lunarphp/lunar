<script setup lang="ts">
import { computed, ref } from 'vue';
import { Deferred, Link, router, useForm, usePage } from '@inertiajs/vue3';
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
import type { BreadcrumbItem } from '../../components/Breadcrumbs.vue';

interface Charge { id: number; reference: string | null; amount: number; amount_formatted: string | null }

type Tone = 'sage' | 'warn' | 'danger' | 'archived' | 'neutral';

interface Address {
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    line_one: string | null;
    line_two: string | null;
    line_three: string | null;
    city: string | null;
    state: string | null;
    postcode: string | null;
    country: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    delivery_instructions: string | null;
    shipping_option: string | null;
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
    lines: { id: number; description: string; option: string | null; identifier: string | null; quantity: number; unit_price: string | null; total: string | null }[];
    shippingLines: { id: number; description: string; total: string | null }[];
    fulfilments: {
        id: number;
        reference: string;
        state: string;
        state_label: string;
        method: string;
        shipped_at: string | null;
        notes: string | null;
        lines: { id: number; quantity: number; description: string | null; identifier: string | null; option: string | null }[];
        trackings: { carrier: string | null; tracking_number: string | null; url: string | null }[];
        can_ship: boolean;
        ship_url: string;
    }[];
    transactions: { id: number; type: string; success: boolean; driver: string; amount: string | null; reference: string | null; status: string | null; card_type: string | null; last_four: string | null; created_at: string }[];
    totals: { sub_total: string | null; discount_total: string | null; shipping_total: string | null; tax_total: string | null; total: string | null; refunded: string | null; net: string | null };
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
    carriers: Record<string, string>;
    canCreateFulfilment: boolean;
    urls: { index: string; capture: string; refund: string; cancel: string; notify: string; note: string; tags: string; fulfilmentsStore: string };
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
const STATE_TONES: Record<string, Tone> = {
    shipped: 'sage',
    collected: 'sage',
    provisioned: 'sage',
    pending: 'warn',
    'in-progress': 'warn',
    'ready-for-collection': 'warn',
    returned: 'danger',
    cancelled: 'archived',
};
const paymentTone = (key: string): Tone => PAYMENT_TONES[key] ?? 'neutral';
const fulfilmentTone = (key: string): Tone => FULFILMENT_TONES[key] ?? 'neutral';
const stateTone = (key: string): Tone => STATE_TONES[key] ?? 'neutral';

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
const refundForm = useForm({ transaction_id: props.charges[0]?.id ?? null, amount: props.availableToRefund, notes: '' });
const cancelForm = useForm({ reason: '' as string, note: '', notify: true });
const notifyForm = useForm({ notification: Object.keys(props.notifications)[0] ?? '', message: '' });

const submitCapture = (): void => captureForm.post(props.urls.capture, { preserveScroll: true, onSuccess: closeDialog });
const submitRefund = (): void => refundForm.post(props.urls.refund, { preserveScroll: true, onSuccess: closeDialog });
const submitCancel = (): void => cancelForm.post(props.urls.cancel, { preserveScroll: true, onSuccess: closeDialog });
const submitNotify = (): void => notifyForm.post(props.urls.notify, { preserveScroll: true, onSuccess: closeDialog });

// Fulfilments.
const creatingFulfilment = ref(false);
const createFulfilment = (): void => {
    creatingFulfilment.value = true;
    router.post(props.urls.fulfilmentsStore, {}, { preserveScroll: true, onFinish: () => (creatingFulfilment.value = false) });
};

const shipUrl = ref<string | null>(null);
const shipForm = useForm({ carrier: '', tracking_number: '', tracking_url: '', notify: true });
const openShip = (url: string): void => {
    shipForm.reset();
    shipForm.clearErrors();
    shipUrl.value = url;
};
const submitShip = (): void => {
    if (shipUrl.value) {
        shipForm.post(shipUrl.value, { preserveScroll: true, onSuccess: () => (shipUrl.value = null) });
    }
};

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
                <PageZone region="main" position="before" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <!-- Fulfilments -->
                        <Section :title="t('orders.section_fulfilments')">
                            <template v-if="canCreateFulfilment" #actions>
                                <Button icon="plus" size="sm" :disabled="creatingFulfilment" @click="createFulfilment">{{ t('orders.create_fulfilment') }}</Button>
                            </template>
                            <div v-if="fulfilments.length" class="flex flex-col gap-3">
                                <div v-for="fulfilment in fulfilments" :key="fulfilment.id" class="bg-surface border border-line rounded-xl overflow-hidden">
                                    <div class="flex items-center gap-2.5 px-4 py-3 border-b border-line">
                                        <Icon name="box" cls="sm" />
                                        <span class="text-[12.5px] font-mono tracking-[-0.01em] text-ink-900">{{ fulfilment.reference }}</span>
                                        <StatusBadge :tone="stateTone(fulfilment.state)" size="sm">{{ fulfilment.state_label }}</StatusBadge>
                                        <div class="flex-1" />
                                        <span v-if="fulfilment.shipped_at" class="text-[11px] text-ink-500">{{ formatDate(fulfilment.shipped_at) }}</span>
                                        <Button v-if="fulfilment.can_ship" icon="box" size="sm" @click="openShip(fulfilment.ship_url)">{{ t('orders.mark_shipped') }}</Button>
                                    </div>
                                    <div class="px-4 py-3">
                                        <div v-for="line in fulfilment.lines" :key="line.id" class="flex items-center gap-2 py-1 text-[12.5px]">
                                            <span class="text-ink-500 [font-variant-numeric:tabular-nums] w-8">{{ line.quantity }}×</span>
                                            <span class="text-ink-900 truncate">{{ line.description }}</span>
                                            <span v-if="line.option" class="text-ink-500 truncate">{{ line.option }}</span>
                                            <span v-if="line.identifier" class="text-ink-400 font-mono text-[11px] ml-auto">{{ line.identifier }}</span>
                                        </div>
                                        <div v-for="tracking in fulfilment.trackings" :key="tracking.tracking_number ?? tracking.carrier ?? ''" class="mt-2 flex items-center gap-1.5 text-[11.5px] text-ink-500">
                                            <Icon name="box" cls="sm" />
                                            <span v-if="tracking.carrier">{{ tracking.carrier }}</span>
                                            <a v-if="tracking.url" :href="tracking.url" target="_blank" rel="noopener" class="text-sage-ink underline underline-offset-2">{{ tracking.tracking_number }}</a>
                                            <span v-else-if="tracking.tracking_number" class="font-mono">{{ tracking.tracking_number }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.fulfilments_empty') }}</p>
                        </Section>

                        <!-- Line items -->
                        <Section :title="t('orders.section_items')">
                            <div v-if="lines.length" class="overflow-x-auto">
                                <table class="w-full text-[12.5px] border-collapse">
                                    <thead>
                                        <tr class="text-ink-500 text-[11px] text-left border-b border-line">
                                            <th class="font-medium py-1.5 pr-2">{{ t('orders.col_product') }}</th>
                                            <th class="font-medium py-1.5 px-2">{{ t('orders.col_sku') }}</th>
                                            <th class="font-medium py-1.5 px-2 text-right">{{ t('orders.col_qty') }}</th>
                                            <th class="font-medium py-1.5 px-2 text-right">{{ t('orders.col_unit') }}</th>
                                            <th class="font-medium py-1.5 pl-2 text-right">{{ t('orders.col_total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="line in lines" :key="line.id" class="border-b border-line last:border-0">
                                            <td class="py-2 pr-2">
                                                <div class="text-ink-900">{{ line.description }}</div>
                                                <div v-if="line.option" class="text-ink-500 text-[11px]">{{ line.option }}</div>
                                            </td>
                                            <td class="py-2 px-2 text-ink-500 font-mono text-[11px]">{{ line.identifier ?? '—' }}</td>
                                            <td class="py-2 px-2 text-right text-ink-700 [font-variant-numeric:tabular-nums]">{{ line.quantity }}</td>
                                            <td class="py-2 px-2 text-right text-ink-700 [font-variant-numeric:tabular-nums]">{{ line.unit_price }}</td>
                                            <td class="py-2 pl-2 text-right text-ink-900 [font-variant-numeric:tabular-nums]">{{ line.total }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="m-0 text-[12.5px] text-ink-500 italic">{{ t('orders.items_empty') }}</p>
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

                        <!-- Shipping -->
                        <Section v-if="shippingLines.length || shippingAddress?.shipping_option" :title="t('orders.section_shipping')">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-md bg-surface-2 border border-line grid place-items-center text-ink-700 shrink-0">
                                    <Icon name="box" cls="sm" />
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[12.5px] text-ink-900">{{ shippingAddress?.shipping_option ?? shippingLines[0]?.description }}</div>
                                    <div v-if="shippingLines[0]" class="text-[11px] text-ink-500 [font-variant-numeric:tabular-nums]">{{ shippingLines[0].total }}</div>
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
                            <address class="not-italic text-[12.5px] text-ink-700 leading-relaxed">
                                <div v-for="(line, i) in addressLines(shippingAddress)" :key="i">{{ line }}</div>
                                <div v-if="shippingAddress.contact_phone" class="mt-1.5 pt-1.5 border-t border-line">
                                    <a :href="`tel:${shippingAddress.contact_phone}`" class="text-ink-500 hover:text-ink-900">{{ shippingAddress.contact_phone }}</a>
                                </div>
                            </address>
                        </SideCard>

                        <SideCard v-if="billingAddress" :title="t('orders.side_billing_address')">
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
                    </div>
                </div>

                <PageZone region="main" position="after" />
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
            <Dialog :open="dialog === 'refund'" :title="t('orders.action_refund')" size="sm" @update:open="(v: boolean) => !v && closeDialog()">
                <div class="space-y-3">
                    <div v-if="charges.length > 1">
                        <FieldLabel>{{ t('orders.refund_transaction') }}</FieldLabel>
                        <Select v-model="refundForm.transaction_id">
                            <option v-for="charge in charges" :key="charge.id" :value="charge.id">{{ charge.reference || ('#' + charge.id) }} — {{ charge.amount_formatted }}</option>
                        </Select>
                    </div>
                    <div>
                        <FieldLabel :hint="availableToRefundFormatted ?? undefined">{{ t('orders.refund_amount') }}</FieldLabel>
                        <TextInput v-model="refundForm.amount" type="number" :invalid="!!refundForm.errors.amount" />
                        <p v-if="refundForm.errors.amount" class="text-danger text-[11px] mt-1">{{ refundForm.errors.amount }}</p>
                    </div>
                    <div>
                        <FieldLabel>{{ t('orders.refund_notes') }}</FieldLabel>
                        <Textarea v-model="refundForm.notes" :rows="2" />
                    </div>
                </div>
                <template #footer>
                    <Button variant="ghost" @click="closeDialog">{{ t('common.cancel') }}</Button>
                    <Button variant="primary" :disabled="refundForm.processing" @click="submitRefund">{{ t('orders.action_refund') }}</Button>
                </template>
            </Dialog>

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

            <!-- Ship fulfilment -->
            <Dialog :open="shipUrl !== null" :title="t('orders.mark_shipped')" size="sm" @update:open="(v: boolean) => !v && (shipUrl = null)">
                <div class="space-y-3">
                    <div>
                        <FieldLabel>{{ t('orders.ship_carrier') }}</FieldLabel>
                        <Select v-model="shipForm.carrier">
                            <option value="">{{ t('orders.ship_carrier_none') }}</option>
                            <option v-for="(name, key) in carriers" :key="key" :value="key">{{ name }}</option>
                        </Select>
                    </div>
                    <div>
                        <FieldLabel>{{ t('orders.ship_tracking_number') }}</FieldLabel>
                        <TextInput v-model="shipForm.tracking_number" :invalid="!!shipForm.errors.tracking_number" />
                    </div>
                    <div>
                        <FieldLabel>{{ t('orders.ship_tracking_url') }}</FieldLabel>
                        <TextInput v-model="shipForm.tracking_url" type="url" :invalid="!!shipForm.errors.tracking_url" />
                        <p v-if="shipForm.errors.tracking_url" class="text-danger text-[11px] mt-1">{{ shipForm.errors.tracking_url }}</p>
                    </div>
                    <label class="flex items-center gap-2 text-[12.5px] text-ink-700">
                        <Toggle :on="shipForm.notify" @toggle="shipForm.notify = !shipForm.notify" />
                        {{ t('orders.ship_notify') }}
                    </label>
                </div>
                <template #footer>
                    <Button variant="ghost" @click="shipUrl = null">{{ t('common.cancel') }}</Button>
                    <Button variant="primary" :disabled="shipForm.processing" @click="submitShip">{{ t('orders.mark_shipped') }}</Button>
                </template>
            </Dialog>
        </div>
    </PanelLayout>
</template>
