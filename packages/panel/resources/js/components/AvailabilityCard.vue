<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import Icon from './Icon.vue';
import SideCard from './SideCard.vue';
import TextInput from './TextInput.vue';
import Tooltip from './Tooltip.vue';

export interface AvailabilityRow {
    id: number;
    name: string;
    // The draft field key this row reads and writes (channel:{id} / customer_group:{id}).
    field: string;
}

export interface AvailabilityValue {
    enabled: boolean;
    visible?: boolean;
    purchasable?: boolean;
    starts_at: string | null;
    ends_at: string | null;
}

const props = defineProps<{
    channels: AvailabilityRow[];
    customerGroups: AvailabilityRow[];
    // The edit page's draft values object; rows mutate their own key so
    // autosave, dirty tracking and conflicts ride the shared draft.
    values: Record<string, unknown>;
    // Products expose the customer-group pivot's extra purchasable flag;
    // collections (whose pivot has no such column) leave this off.
    withPurchasable?: boolean;
}>();

const { t } = useI18n();

const valueFor = (row: AvailabilityRow): AvailabilityValue => {
    const value = props.values[row.field] as AvailabilityValue | undefined;

    return value ?? { enabled: false, starts_at: null, ends_at: null };
};

const write = (row: AvailabilityRow, next: AvailabilityValue): void => {
    // eslint-disable-next-line vue/no-mutating-props
    props.values[row.field] = next;
};

const isScheduled = (row: AvailabilityRow): boolean => {
    const value = valueFor(row);

    return value.enabled && !!value.starts_at && new Date(value.starts_at) > new Date();
};

const isOn = (row: AvailabilityRow): boolean => valueFor(row).enabled && !isScheduled(row);

const togglePower = (row: AvailabilityRow): void => {
    if (isScheduled(row)) {
        return;
    }

    const value = valueFor(row);

    write(row, { ...value, enabled: !value.enabled, starts_at: null, ends_at: null });
};

const toggleVisible = (row: AvailabilityRow): void => {
    const value = valueFor(row);

    write(row, { ...value, visible: !(value.visible ?? true) });
};

const togglePurchasable = (row: AvailabilityRow): void => {
    const value = valueFor(row);

    write(row, { ...value, purchasable: !(value.purchasable ?? true) });
};

// Schedule dialog: start date (the "turns on" moment) plus an optional end.
const scheduling = ref<AvailabilityRow | null>(null);
const scheduleStart = ref('');
const scheduleEnd = ref('');

const scheduleOpen = computed({
    get: () => scheduling.value !== null,
    set: (value: boolean) => {
        if (!value) {
            scheduling.value = null;
        }
    },
});

const toggleSchedule = (row: AvailabilityRow): void => {
    if (isScheduled(row)) {
        write(row, { ...valueFor(row), enabled: false, starts_at: null, ends_at: null });

        return;
    }

    scheduleStart.value = '';
    scheduleEnd.value = '';
    scheduling.value = row;
};

const applySchedule = (): void => {
    if (!scheduling.value || !scheduleStart.value) {
        return;
    }

    write(scheduling.value, {
        ...valueFor(scheduling.value),
        enabled: true,
        starts_at: scheduleStart.value,
        ends_at: scheduleEnd.value || null,
    });

    scheduling.value = null;
};

const scheduleLabel = (row: AvailabilityRow): string => {
    const startsAt = valueFor(row).starts_at;

    return startsAt ? new Date(startsAt).toLocaleDateString() : '';
};

// Collapsible sections summarise as All/Some/None when closed.
const channelsOpen = ref(false);
const groupsOpen = ref(false);

interface Summary {
    tone: 'all' | 'some' | 'none';
    enabled: number;
    scheduled: number;
    total: number;
}

const summarize = (rows: AvailabilityRow[]): Summary => {
    const enabled = rows.filter((row) => isOn(row)).length;
    const scheduled = rows.filter((row) => isScheduled(row)).length;

    return {
        tone: enabled === rows.length ? 'all' : enabled === 0 ? 'none' : 'some',
        enabled,
        scheduled,
        total: rows.length,
    };
};

const channelSummary = computed(() => summarize(props.channels));
const groupSummary = computed(() => summarize(props.customerGroups));

const TONE_DOT: Record<Summary['tone'], string> = {
    all: 'bg-sage',
    some: 'bg-warn-ink',
    none: 'bg-danger',
};

const summaryLabel = (summary: Summary): string =>
    summary.tone === 'all'
        ? t('availability.summary_all')
        : summary.tone === 'none'
            ? t('availability.summary_none')
            : t('availability.summary_some');

const powerTip = (row: AvailabilityRow): string => {
    if (isScheduled(row)) {
        return t('availability.scheduled_locked');
    }

    return isOn(row) ? t('availability.enabled') : t('availability.disabled');
};

const calendarTip = (row: AvailabilityRow): string =>
    isScheduled(row)
        ? `${t('availability.scheduled_tip', { date: scheduleLabel(row) })} — ${t('availability.clear_schedule')}`
        : t('availability.not_scheduled');

const pillBase = 'w-[26px] h-[26px] rounded-sm grid place-items-center transition-all duration-100 shrink-0';

const powerPillClass = (row: AvailabilityRow): string => {
    if (isScheduled(row)) {
        return `${pillBase} bg-sage-soft text-sage-ink border border-dashed border-sage-border cursor-not-allowed`;
    }

    return isOn(row)
        ? `${pillBase} bg-sage-soft text-sage-ink`
        : `${pillBase} bg-transparent text-ink-300 hover:text-ink-500`;
};

const calendarPillClass = (row: AvailabilityRow): string =>
    isScheduled(row)
        ? `${pillBase} bg-sage-soft text-sage-ink`
        : `${pillBase} bg-transparent text-ink-300 hover:text-ink-500`;
</script>

<template>
    <SideCard :title="t('availability.title')" body-class="px-3 pt-1 pb-2">
        <!-- Sales channels -->
        <button
            type="button"
            class="w-full flex items-center gap-2 px-1 py-2 bg-transparent border-0 text-left cursor-pointer rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35 focus-visible:ring-inset"
            :aria-expanded="channelsOpen"
            @click="channelsOpen = !channelsOpen"
        >
            <span
                class="inline-flex items-center justify-center w-3.5 h-3.5 text-ink-400 shrink-0 transition-transform duration-200"
                :class="{ 'rotate-90': channelsOpen }"
            >
                <Icon name="chevRight" cls="sm" />
            </span>
            <span class="flex-1 min-w-0">
                <span class="block text-[10.5px] font-medium text-ink-500 uppercase tracking-[0.06em] leading-tight">
                    {{ t('availability.channels') }}
                </span>
                <span v-if="!channelsOpen" class="flex items-center gap-1.5 mt-[3px] text-xs leading-[1.3]">
                    <span class="w-[7px] h-[7px] rounded-full shrink-0" :class="TONE_DOT[channelSummary.tone]" />
                    <span class="text-ink-900 font-medium">{{ summaryLabel(channelSummary) }}</span>
                    <span class="text-ink-500">{{ t('availability.summary_channels', { enabled: channelSummary.enabled, total: channelSummary.total }) }}</span>
                </span>
            </span>
            <Tooltip v-if="channelSummary.scheduled > 0 && !channelsOpen" :text="t('availability.scheduled_count', { count: channelSummary.scheduled })">
                <span class="inline-flex items-center gap-[3px] rounded-full px-1.5 py-0.5 text-[10.5px] font-medium bg-sage-soft text-sage-ink">
                    <Icon name="calendar" cls="!w-[10px] !h-[10px]" />
                    {{ channelSummary.scheduled }}
                </span>
            </Tooltip>
            <span v-if="channelsOpen" class="text-[11px] text-ink-400 [font-variant-numeric:tabular-nums]">
                {{ channelSummary.enabled }}/{{ channelSummary.total }}
            </span>
        </button>
        <div v-if="channelsOpen" class="pl-[22px] pb-1.5">
            <div
                v-for="(row, index) in channels"
                :key="row.id"
                class="py-1.5"
                :class="[
                    index === 0 ? '' : 'border-t border-line',
                    !valueFor(row).enabled ? 'opacity-55' : 'opacity-100',
                ]"
            >
                <div class="flex items-center gap-1.5">
                    <div class="flex-1 min-w-0 text-[12.5px] font-medium text-ink-900 truncate">{{ row.name }}</div>
                    <Tooltip :text="powerTip(row)">
                        <button
                            type="button"
                            :class="powerPillClass(row)"
                            :disabled="isScheduled(row)"
                            :aria-label="powerTip(row)"
                            @click="togglePower(row)"
                        ><Icon name="power" cls="!w-3 !h-3" /></button>
                    </Tooltip>
                    <Tooltip :text="calendarTip(row)">
                        <button
                            type="button"
                            :class="calendarPillClass(row)"
                            :aria-label="calendarTip(row)"
                            @click="toggleSchedule(row)"
                        ><Icon name="calendar" cls="!w-3 !h-3" /></button>
                    </Tooltip>
                </div>
                <div v-if="isScheduled(row)" class="text-[10.5px] mt-[3px] flex items-center gap-1 whitespace-nowrap text-sage-ink">
                    <Icon name="clock" cls="!w-[9px] !h-[9px] shrink-0 opacity-80" />
                    <span class="opacity-70">{{ t('availability.turns_on') }}</span>
                    <span class="font-medium">{{ scheduleLabel(row) }}</span>
                </div>
            </div>
        </div>

        <div class="border-t border-line mt-0.5 -mx-1" />

        <!-- Customer groups -->
        <button
            type="button"
            class="w-full flex items-center gap-2 px-1 py-2 bg-transparent border-0 text-left cursor-pointer rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35 focus-visible:ring-inset"
            :aria-expanded="groupsOpen"
            @click="groupsOpen = !groupsOpen"
        >
            <span
                class="inline-flex items-center justify-center w-3.5 h-3.5 text-ink-400 shrink-0 transition-transform duration-200"
                :class="{ 'rotate-90': groupsOpen }"
            >
                <Icon name="chevRight" cls="sm" />
            </span>
            <span class="flex-1 min-w-0">
                <span class="block text-[10.5px] font-medium text-ink-500 uppercase tracking-[0.06em] leading-tight">
                    {{ t('availability.customer_groups') }}
                </span>
                <span v-if="!groupsOpen" class="flex items-center gap-1.5 mt-[3px] text-xs leading-[1.3]">
                    <span class="w-[7px] h-[7px] rounded-full shrink-0" :class="TONE_DOT[groupSummary.tone]" />
                    <span class="text-ink-900 font-medium">{{ summaryLabel(groupSummary) }}</span>
                    <span class="text-ink-500">{{ t('availability.summary_groups', { enabled: groupSummary.enabled, total: groupSummary.total }) }}</span>
                </span>
            </span>
            <Tooltip v-if="groupSummary.scheduled > 0 && !groupsOpen" :text="t('availability.scheduled_count', { count: groupSummary.scheduled })">
                <span class="inline-flex items-center gap-[3px] rounded-full px-1.5 py-0.5 text-[10.5px] font-medium bg-sage-soft text-sage-ink">
                    <Icon name="calendar" cls="!w-[10px] !h-[10px]" />
                    {{ groupSummary.scheduled }}
                </span>
            </Tooltip>
            <span v-if="groupsOpen" class="text-[11px] text-ink-400 [font-variant-numeric:tabular-nums]">
                {{ groupSummary.enabled }}/{{ groupSummary.total }}
            </span>
        </button>
        <div v-if="groupsOpen" class="pl-[22px] pb-1.5">
            <div
                v-for="(row, index) in customerGroups"
                :key="row.id"
                class="group/row py-1.5"
                :class="[
                    index === 0 ? '' : 'border-t border-line',
                    !valueFor(row).enabled ? 'opacity-55' : 'opacity-100',
                ]"
            >
                <div class="flex items-center gap-1.5">
                    <div class="flex-1 min-w-0 text-[12.5px] font-medium text-ink-900 truncate">{{ row.name }}</div>

                    <!-- Hidden-from-browsing flag (visible at rest when hidden) -->
                    <Tooltip v-if="valueFor(row).enabled && valueFor(row).visible === false" :text="t('availability.make_visible')">
                        <button
                            type="button"
                            class="inline-flex items-center gap-[3px] rounded-full text-[10.5px] font-medium leading-none cursor-pointer shrink-0 bg-warn-soft text-warn-ink border border-warn-border pt-[2px] pb-[2px] pl-[6px] pr-[7px]"
                            :aria-label="t('availability.make_visible')"
                            @click="toggleVisible(row)"
                        >
                            <Icon name="eyeOff" cls="!w-[10px] !h-[10px]" />
                            <span>{{ t('availability.hidden') }}</span>
                        </button>
                    </Tooltip>

                    <!-- View-only flag: enabled but not purchasable (products only) -->
                    <Tooltip v-if="withPurchasable && valueFor(row).enabled && valueFor(row).purchasable === false" :text="t('availability.make_purchasable')">
                        <button
                            type="button"
                            class="inline-flex items-center gap-[3px] rounded-full text-[10.5px] font-medium leading-none cursor-pointer shrink-0 bg-warn-soft text-warn-ink border border-warn-border pt-[2px] pb-[2px] pl-[6px] pr-[7px]"
                            :aria-label="t('availability.make_purchasable')"
                            @click="togglePurchasable(row)"
                        >
                            <Icon name="cart" cls="!w-[10px] !h-[10px]" />
                            <span>{{ t('availability.view_only') }}</span>
                        </button>
                    </Tooltip>

                    <!-- Ghost-cart (hover only) to make a purchasable group view-only -->
                    <Tooltip v-if="withPurchasable && valueFor(row).enabled && valueFor(row).purchasable !== false" :text="t('availability.make_view_only')">
                        <button
                            type="button"
                            class="hidden group-hover/row:grid place-items-center w-[18px] h-[18px] rounded-full bg-transparent cursor-pointer shrink-0 p-0 border border-dashed border-ink-300 text-ink-400"
                            :aria-label="t('availability.make_view_only')"
                            @click="togglePurchasable(row)"
                        >
                            <Icon name="cart" cls="!w-[9px] !h-[9px]" />
                        </button>
                    </Tooltip>

                    <!-- Ghost-eye (hover only) to hide a browsable group -->
                    <Tooltip v-if="valueFor(row).enabled && valueFor(row).visible !== false" :text="t('availability.make_hidden')">
                        <button
                            type="button"
                            class="hidden group-hover/row:grid place-items-center w-[18px] h-[18px] rounded-full bg-transparent cursor-pointer shrink-0 p-0 border border-dashed border-ink-300 text-ink-400"
                            :aria-label="t('availability.make_hidden')"
                            @click="toggleVisible(row)"
                        >
                            <Icon name="eye" cls="!w-[9px] !h-[9px]" />
                        </button>
                    </Tooltip>

                    <Tooltip :text="powerTip(row)">
                        <button
                            type="button"
                            :class="powerPillClass(row)"
                            :disabled="isScheduled(row)"
                            :aria-label="powerTip(row)"
                            @click="togglePower(row)"
                        ><Icon name="power" cls="!w-3 !h-3" /></button>
                    </Tooltip>
                    <Tooltip :text="calendarTip(row)">
                        <button
                            type="button"
                            :class="calendarPillClass(row)"
                            :aria-label="calendarTip(row)"
                            @click="toggleSchedule(row)"
                        ><Icon name="calendar" cls="!w-3 !h-3" /></button>
                    </Tooltip>
                </div>
                <div v-if="isScheduled(row)" class="text-[10.5px] mt-[3px] flex items-center gap-1 whitespace-nowrap text-sage-ink">
                    <Icon name="clock" cls="!w-[9px] !h-[9px] shrink-0 opacity-80" />
                    <span class="opacity-70">{{ t('availability.turns_on') }}</span>
                    <span class="font-medium">{{ scheduleLabel(row) }}</span>
                </div>
            </div>
        </div>
    </SideCard>

    <Dialog
        :open="scheduleOpen"
        size="sm"
        :title="t('availability.schedule_title')"
        :description="t('availability.schedule_description')"
        @update:open="scheduleOpen = $event"
    >
        <div class="grid grid-cols-1 gap-3">
            <div>
                <FieldLabel for="schedule-start" required>{{ t('availability.schedule_starts') }}</FieldLabel>
                <TextInput id="schedule-start" v-model="scheduleStart" type="datetime-local" />
            </div>
            <div>
                <FieldLabel for="schedule-end">{{ t('availability.schedule_ends') }}</FieldLabel>
                <TextInput id="schedule-end" v-model="scheduleEnd" type="datetime-local" />
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" @click="scheduleOpen = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!scheduleStart" @click="applySchedule">
                {{ t('availability.schedule_apply') }}
            </Button>
        </template>
    </Dialog>
</template>
