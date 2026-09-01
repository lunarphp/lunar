<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuTrigger,
} from 'reka-ui';
import Button from '../Button.vue';
import Icon from '../Icon.vue';
import StatusBadge from '../StatusBadge.vue';
import FulfilmentLineRow from './FulfilmentLineRow.vue';
import type { FulfilmentData, FulfilmentStateCategory, FulfilmentTrackingData, FulfilmentTransitionData } from './types';

export type FulfilmentCardAction =
    | { type: 'ship' | 'fulfil' | 'split' | 'merge' | 'hold' | 'release' | 'undo-return' | 'cancel' | 'add-tracking' | 'change-location' }
    | { type: 'transition' | 'return'; transition: FulfilmentTransitionData }
    | { type: 'remove-tracking'; tracking: FulfilmentTrackingData };

const props = defineProps<{ fulfilment: FulfilmentData }>();

const emit = defineEmits<{ action: [action: FulfilmentCardAction] }>();

const { t } = useI18n();

type Tone = 'sage' | 'warn' | 'danger' | 'archived' | 'neutral';

const CATEGORY_TONES: Record<FulfilmentStateCategory, Tone> = {
    outstanding: 'warn',
    fulfilled: 'sage',
    returned: 'danger',
    cancelled: 'archived',
};
const CATEGORY_ICON_CLASSES: Record<FulfilmentStateCategory, string> = {
    outstanding: 'bg-warn-soft border-warn-border text-warn-ink',
    fulfilled: 'bg-sage-soft border-sage-border text-sage-ink',
    returned: 'bg-danger-soft border-danger-border text-danger',
    cancelled: 'bg-surface-2 border-line text-ink-500',
};

const tone = computed<Tone>(() => CATEGORY_TONES[props.fulfilment.state_category] ?? 'neutral');
const iconClasses = computed(() => CATEGORY_ICON_CLASSES[props.fulfilment.state_category] ?? 'bg-surface-2 border-line text-ink-500');

// The method's terminal verb, when currently reachable: "Mark shipped" opens
// the tracking dialog; "Mark collected" / "Mark fulfilled" confirm directly.
const primary = computed(() => props.fulfilment.transitions.find((tr) => tr.via === 'ship' || tr.via === 'fulfil') ?? null);
const primaryLabel = computed(() =>
    primary.value?.via === 'ship' ? t('orders.mark_shipped') : props.fulfilment.fulfil_label,
);

// The intermediate/return steps left for the "Update status" dropdown.
const menuTransitions = computed(() => props.fulfilment.transitions.filter((tr) => tr !== primary.value));

const totalQuantity = computed(() => props.fulfilment.lines.reduce((sum, line) => sum + line.quantity, 0));

const formatDate = (value: string): string =>
    new Date(value).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });

interface MenuItem {
    key: string;
    label: string;
    icon: string;
    danger?: boolean;
    action: FulfilmentCardAction;
}

const menuItems = computed<MenuItem[]>(() => {
    const can = props.fulfilment.can;
    const items: MenuItem[] = [];

    if (can.split) items.push({ key: 'split', label: t('orders.action_split'), icon: 'scissors', action: { type: 'split' } });
    if (can.merge) items.push({ key: 'merge', label: t('orders.action_merge'), icon: 'merge', action: { type: 'merge' } });
    if (can.change_location) items.push({ key: 'change-location', label: t('orders.action_change_location'), icon: 'mapPin', action: { type: 'change-location' } });
    if (can.add_tracking) items.push({ key: 'add-tracking', label: t('orders.action_add_tracking'), icon: 'truck', action: { type: 'add-tracking' } });
    if (can.undo_return) items.push({ key: 'undo-return', label: t('orders.action_undo_return'), icon: 'redo', action: { type: 'undo-return' } });
    if (can.hold) items.push({ key: 'hold', label: t('orders.action_hold'), icon: 'pause', action: { type: 'hold' } });
    if (can.release) items.push({ key: 'release', label: t('orders.action_release'), icon: 'play', action: { type: 'release' } });
    if (can.cancel) items.push({ key: 'cancel', label: t('orders.action_cancel_fulfilment'), icon: 'x', danger: true, action: { type: 'cancel' } });

    return items;
});

const onTransition = (transition: FulfilmentTransitionData): void => {
    if (transition.via === 'ship') {
        emit('action', { type: 'ship' });
    } else if (transition.via === 'fulfil') {
        emit('action', { type: 'fulfil' });
    } else if (transition.via === 'return') {
        emit('action', { type: 'return', transition });
    } else {
        emit('action', { type: 'transition', transition });
    }
};

const menuItemClass =
    'flex items-center gap-2 px-2 py-1.5 text-[12.5px] rounded outline-none cursor-pointer data-[highlighted]:bg-surface-2';
</script>

<template>
    <div class="bg-surface border border-line rounded-xl overflow-hidden" :data-testid="`fulfilment-${fulfilment.id}`">
        <!-- Header -->
        <div class="flex items-start gap-3 px-4 py-3 border-b border-line">
            <div :class="['w-9 h-9 rounded-md grid place-items-center shrink-0 border', iconClasses]">
                <Icon name="box" />
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[12.5px] font-mono tracking-[-0.01em] text-ink-900">{{ fulfilment.reference }}</span>
                    <StatusBadge :tone="tone" size="sm" dot>{{ fulfilment.state_label }}</StatusBadge>
                    <StatusBadge size="sm">{{ fulfilment.method_label }}</StatusBadge>
                    <StatusBadge v-if="fulfilment.on_hold" tone="warn" size="sm" :title="fulfilment.hold_note ?? undefined">
                        {{ t('orders.on_hold') }}<template v-if="fulfilment.hold_reason_label"> · {{ fulfilment.hold_reason_label }}</template>
                    </StatusBadge>
                </div>
                <div class="mt-0.5 flex items-center gap-1.5 text-[11.5px] text-ink-500 flex-wrap">
                    <!-- The checkout's delivery method leads the subline — it's what
                         the admin needs when choosing a service to dispatch with. -->
                    <template v-if="fulfilment.delivery_method">
                        <Icon name="truck" cls="sm" class="text-ink-700" />
                        <span class="text-ink-700 font-medium">{{ fulfilment.delivery_method }}</span>
                        <span>·</span>
                    </template>
                    <template v-if="fulfilment.shipped_at">
                        <span>{{ fulfilment.handed_over_label }} {{ formatDate(fulfilment.shipped_at) }}</span>
                    </template>
                    <template v-else>
                        <span>{{ t('orders.item_count', { count: totalQuantity }, totalQuantity) }}</span>
                    </template>
                    <template v-if="fulfilment.location">
                        <span>·</span>
                        <Icon name="mapPin" cls="sm" />
                        <span class="truncate">{{ fulfilment.location }}</span>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <Button v-if="primary" size="sm" icon="check" @click="onTransition(primary)">{{ primaryLabel }}</Button>

                <DropdownMenuRoot v-if="menuTransitions.length">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="sm">
                            {{ t('orders.update_status') }}
                            <Icon name="chevDown" cls="sm" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuPortal>
                        <DropdownMenuContent :side-offset="4" align="end" class="z-50 min-w-[180px] rounded-md border border-line bg-surface p-1 shadow-md">
                            <DropdownMenuItem
                                v-for="transition in menuTransitions"
                                :key="transition.state"
                                :class="[menuItemClass, 'text-ink-700']"
                                @select="onTransition(transition)"
                            >
                                {{ transition.label }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenuPortal>
                </DropdownMenuRoot>

                <DropdownMenuRoot v-if="menuItems.length">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="sm" icon="more" :aria-label="t('common.more_actions')" class="!w-[30px]" />
                    </DropdownMenuTrigger>
                    <DropdownMenuPortal>
                        <DropdownMenuContent :side-offset="4" align="end" class="z-50 min-w-[200px] rounded-md border border-line bg-surface p-1 shadow-md">
                            <DropdownMenuItem
                                v-for="item in menuItems"
                                :key="item.key"
                                :class="[menuItemClass, item.danger ? 'text-danger data-[highlighted]:text-danger' : 'text-ink-700']"
                                @select="emit('action', item.action)"
                            >
                                <Icon :name="item.icon" cls="sm" />
                                {{ item.label }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenuPortal>
                </DropdownMenuRoot>
            </div>
        </div>

        <!-- Lines -->
        <div class="px-4 py-1">
            <FulfilmentLineRow v-for="line in fulfilment.lines" :key="line.id" :line="line" />
        </div>

        <!-- Tracking -->
        <div v-if="fulfilment.trackings.length" class="px-4 py-2.5 border-t border-line bg-surface-2/40">
            <div
                v-for="tracking in fulfilment.trackings"
                :key="tracking.id"
                class="flex items-center gap-1.5 py-0.5 text-[11.5px] text-ink-500"
            >
                <Icon name="truck" cls="sm" />
                <span v-if="tracking.carrier_name">{{ tracking.carrier_name }}</span>
                <a
                    v-if="tracking.url"
                    :href="tracking.url"
                    target="_blank"
                    rel="noopener"
                    class="font-mono text-sage-ink underline underline-offset-2"
                >{{ tracking.tracking_number }}</a>
                <span v-else-if="tracking.tracking_number" class="font-mono">{{ tracking.tracking_number }}</span>
                <span v-if="tracking.shipping_method" class="text-ink-400">· {{ tracking.shipping_method }}</span>
                <span class="flex-1" />
                <button
                    type="button"
                    class="text-ink-400 hover:text-danger"
                    :aria-label="t('orders.tracking_remove_row')"
                    @click="emit('action', { type: 'remove-tracking', tracking })"
                >
                    <Icon name="trash" cls="sm" />
                </button>
            </div>
        </div>

        <p v-if="fulfilment.notes" class="m-0 px-4 py-2 border-t border-line text-[11.5px] text-ink-500 italic">{{ fulfilment.notes }}</p>
    </div>
</template>
