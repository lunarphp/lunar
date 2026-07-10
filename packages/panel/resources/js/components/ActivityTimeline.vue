<script setup lang="ts">
import { computed } from 'vue';
import Icon from './Icon.vue';

interface ActivityEvent {
    id?: string | number;
    type?: string;
    label?: string;
    detail?: string;
    when?: string;
    actor?: string;
    /** spatie/activitylog shape — falls back to `label` when set. */
    description?: string;
    /** spatie/activitylog shape — falls back to `when` when set. */
    created_at?: string;
    /** spatie/activitylog shape — falls back to `actor` when set. */
    causer_name?: string | null;
}

const props = withDefaults(
    defineProps<{
        events: ActivityEvent[];
        // newest first by default; events come in ascending order from the data layer.
        reverse?: boolean;
    }>(),
    { reverse: true },
);

interface ToneMeta {
    icon: string;
    tone: 'neutral' | 'sage' | 'warn' | 'danger' | 'archived';
}

// type -> { icon, tone }
const TYPE_META: Record<string, ToneMeta> = {
    placed: { icon: 'cart', tone: 'neutral' },
    payment_authorized: { icon: 'card', tone: 'neutral' },
    payment_captured: { icon: 'check', tone: 'sage' },
    payment_pending: { icon: 'clock', tone: 'warn' },
    payment_failed: { icon: 'alert', tone: 'danger' },
    dispatch_partial: { icon: 'truck', tone: 'warn' },
    dispatch_full: { icon: 'truck', tone: 'sage' },
    delivered: { icon: 'check', tone: 'sage' },
    returned: { icon: 'undo', tone: 'danger' },
    refund_issued: { icon: 'refund', tone: 'danger' },
    cancelled: { icon: 'x', tone: 'danger' },
    fulfilment_created: { icon: 'box', tone: 'neutral' },
    fulfilment_split: { icon: 'split', tone: 'neutral' },
    fulfilment_merged: { icon: 'merge', tone: 'neutral' },
    fulfilment_dispatched: { icon: 'truck', tone: 'sage' },
    fulfilment_delivered: { icon: 'check', tone: 'sage' },
    fulfilment_cancelled: { icon: 'x', tone: 'archived' },
    fulfilment_tracking_updated: { icon: 'truck', tone: 'neutral' },
    shipment_change: { icon: 'truck', tone: 'neutral' },
    return_created: { icon: 'undo', tone: 'warn' },
    return_approved: { icon: 'check', tone: 'warn' },
    return_received: { icon: 'truck', tone: 'sage' },
    return_resolved: { icon: 'checkCircle', tone: 'sage' },
    return_cancelled: { icon: 'x', tone: 'archived' },
    // 'message' isn't in the panel's Icon.vue set — use 'fileText' so the (very common)
    // untyped spatie/activitylog entry doesn't render a blank icon.
    note_added: { icon: 'fileText', tone: 'neutral' },
    comment: { icon: 'fileText', tone: 'neutral' },
    tag_added: { icon: 'flag', tone: 'warn' },
    email_sent: { icon: 'mail', tone: 'neutral' },
    invoice_generated: { icon: 'receipt', tone: 'sage' },
    invoice_downloaded: { icon: 'download', tone: 'neutral' },
    proforma_sent: { icon: 'fileText', tone: 'neutral' },
    credit_note_issued: { icon: 'refund', tone: 'sage' },
    balance_collected: { icon: 'card', tone: 'sage' },
    balance_refunded: { icon: 'refund', tone: 'sage' },
    registered: { icon: 'userPlus', tone: 'sage' },
    group_added: { icon: 'flag', tone: 'neutral' },
    group_removed: { icon: 'flag', tone: 'neutral' },
    address_added: { icon: 'pin', tone: 'neutral' },
    user_invited: { icon: 'mail', tone: 'neutral' },
};

// intentionally omits 'archived' to match the prototype — it falls back to TONE.neutral below.
const TONE: Partial<Record<ToneMeta['tone'], string>> = {
    neutral: 'bg-surface-2 border-line text-ink-700',
    sage: 'bg-sage-soft border-sage-border text-sage-ink',
    warn: 'bg-warn-soft border-warn-border text-warn-ink',
    danger: 'bg-danger-soft border-danger-border text-danger',
};

const ordered = computed(() => (props.reverse ? [...props.events].reverse() : props.events));

const meta = (type?: string): ToneMeta => TYPE_META[type ?? ''] ?? TYPE_META.note_added;

const eventLabel = (ev: ActivityEvent): string => ev.label ?? ev.description ?? '';
const eventWhen = (ev: ActivityEvent): string => ev.when ?? ev.created_at ?? '';
const eventActor = (ev: ActivityEvent): string => ev.actor ?? ev.causer_name ?? '';
</script>

<template>
    <ol class="relative pl-1">
        <li
            v-for="(ev, i) in ordered"
            :key="ev.id ?? i"
            class="relative flex gap-3 pb-4 last:pb-0"
        >
            <!-- Vertical connector (excluding last item) -->
            <span
                v-if="i < ordered.length - 1"
                class="absolute left-[15px] top-7 bottom-0 w-px bg-line"
                aria-hidden="true"
            />
            <div
                :class="[
                    'relative z-10 w-[30px] h-[30px] rounded-full grid place-items-center border shrink-0',
                    TONE[meta(ev.type).tone] || TONE.neutral,
                ]"
            >
                <Icon :name="meta(ev.type).icon" cls="sm" />
            </div>
            <div class="flex-1 min-w-0 pt-1">
                <div class="text-[13px] text-ink-900 font-medium">{{ eventLabel(ev) }}</div>
                <div v-if="ev.detail" class="text-xs text-ink-700 mt-0.5">{{ ev.detail }}</div>
                <div class="text-[11px] text-ink-500 mt-0.5">
                    <span v-if="eventActor(ev)">{{ eventActor(ev) }}</span>
                    <span v-if="eventActor(ev) && eventWhen(ev)" class="mx-1.5 text-ink-300">·</span>
                    <span v-if="eventWhen(ev)">{{ eventWhen(ev) }}</span>
                </div>
            </div>
        </li>
    </ol>
</template>
