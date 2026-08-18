<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref, type Component } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from '../components/Button.vue';
import Icon from '../components/Icon.vue';
import PageHeader from '../components/PageHeader.vue';
import PageZone from '../components/PageZone.vue';
import CustomiseDialog from '../components/dashboard/CustomiseDialog.vue';
import RangeSelector from '../components/dashboard/RangeSelector.vue';
import WidgetCard, { type ReorderEvent } from '../components/dashboard/WidgetCard.vue';
import ChannelsWidget from '../components/dashboard/widgets/ChannelsWidget.vue';
import CustomerGroupsWidget from '../components/dashboard/widgets/CustomerGroupsWidget.vue';
import KpisWidget from '../components/dashboard/widgets/KpisWidget.vue';
import LowStockWidget from '../components/dashboard/widgets/LowStockWidget.vue';
import NewVsRepeatWidget from '../components/dashboard/widgets/NewVsRepeatWidget.vue';
import RecentOrdersWidget from '../components/dashboard/widgets/RecentOrdersWidget.vue';
import RevenueChartWidget from '../components/dashboard/widgets/RevenueChartWidget.vue';
import TasksWidget from '../components/dashboard/widgets/TasksWidget.vue';
import TopProductsWidget from '../components/dashboard/widgets/TopProductsWidget.vue';
import { http } from '../lib/http';
import PanelLayout from '../layouts/PanelLayout.vue';

interface WidgetMeta {
    key: string;
    component: string;
    label: string;
    description: string | null;
    icon: string | null;
    span: 'full' | 'half';
    flat: boolean;
    visible: boolean;
}

const props = defineProps<{
    range: string;
    widgets: WidgetMeta[];
    widgetData: Record<string, Record<string, unknown> | undefined>;
    urls: { preferences: string };
}>();

const { t } = useI18n();

// First-party widget bodies; add-on components resolve through the runtime
// registry, the same path PanelSlot uses.
const LOCAL_COMPONENTS: Record<string, Component> = {
    KpisWidget,
    RevenueChartWidget,
    RecentOrdersWidget,
    TopProductsWidget,
    ChannelsWidget,
    NewVsRepeatWidget,
    CustomerGroupsWidget,
    LowStockWidget,
    TasksWidget,
};

const resolveComponent = (name: string): Component | null => {
    if (LOCAL_COMPONENTS[name]) {
        return LOCAL_COMPONENTS[name];
    }

    const resolved = window.LunarPanel?.resolveExtensionComponent(name);

    if (!resolved) {
        console.warn(`[lunar-panel] Unresolvable dashboard widget component [${name}].`);
    }

    return resolved ?? null;
};

// Layout is client-owned after load: order and visibility mutate locally and
// persist on every change, so the props never need to round-trip.
const layout = ref(props.widgets.map((widget) => ({ key: widget.key, visible: widget.visible })));
const range = ref(props.range);
const editMode = ref(false);
const customiseOpen = ref(false);

const metaByKey = computed(() => new Map(props.widgets.map((widget) => [widget.key, widget])));

const visibleWidgets = computed(() =>
    layout.value
        .filter((entry) => entry.visible)
        .map((entry) => metaByKey.value.get(entry.key))
        .filter((meta): meta is WidgetMeta => !!meta));

const hiddenWidgets = computed(() =>
    layout.value
        .filter((entry) => !entry.visible)
        .map((entry) => metaByKey.value.get(entry.key))
        .filter((meta): meta is WidgetMeta => !!meta));

const persist = (): Promise<null> =>
    http.put<null>(props.urls.preferences, {
        range: range.value,
        widgets: layout.value,
    }).catch(() => null);

const reloadWidgetData = (keys: string[]): void => {
    if (!keys.length) {
        return;
    }

    router.reload({
        data: { range: range.value },
        only: keys.map((key) => `widgetData.${key}`),
    });
};

const setRange = (value: string): void => {
    range.value = value;
    void persist();
    reloadWidgetData(visibleWidgets.value.map((widget) => widget.key));
};

const entryOf = (key: string) => layout.value.find((entry) => entry.key === key);

const hideWidget = (key: string): void => {
    const entry = entryOf(key);

    if (entry) {
        entry.visible = false;
        void persist();
    }
};

const showWidget = async (key: string): Promise<void> => {
    const entry = entryOf(key);

    if (!entry) {
        return;
    }

    entry.visible = true;

    // The server only defers data for visible widgets, so persist the new
    // visibility before asking for this widget's payload.
    if (props.widgetData[key] === undefined) {
        await persist();
        reloadWidgetData([key]);
    } else {
        void persist();
    }
};

const reorderWidgets = ({ fromKey, toKey, position }: ReorderEvent): void => {
    const list = layout.value;
    const fromIndex = list.findIndex((entry) => entry.key === fromKey);

    if (fromIndex === -1) {
        return;
    }

    const [moved] = list.splice(fromIndex, 1);
    const targetIndex = list.findIndex((entry) => entry.key === toKey);

    if (targetIndex === -1) {
        list.splice(fromIndex, 0, moved);

        return;
    }

    list.splice(position === 'after' ? targetIndex + 1 : targetIndex, 0, moved);
    void persist();
};

const resetLayout = async (): Promise<void> => {
    await http.delete(props.urls.preferences).catch(() => null);
    customiseOpen.value = false;
    router.reload();
};
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Dashboard" class="contents">
            <PageHeader :title="t('nav.dashboard')" :description="t('dashboard.intro')" icon="dashboard">
                <template #actions>
                    <RangeSelector :model-value="range" @update:model-value="setRange" />
                    <Button
                        :variant="editMode ? 'primary' : 'default'"
                        :icon="editMode ? 'check' : 'settings'"
                        @click="editMode = !editMode"
                    >
                        <span class="hidden sm:inline">{{ editMode ? t('dashboard.done') : t('dashboard.customise') }}</span>
                    </Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <div
                    v-if="editMode"
                    class="mb-4 px-3 py-2 rounded-md bg-surface-2 border border-line text-ink-700 text-[12px] flex items-center gap-2"
                >
                    <Icon name="grip" cls="sm" />
                    <span>{{ t('dashboard.edit_hint') }}</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <WidgetCard
                        v-for="widget in visibleWidgets"
                        :key="widget.key"
                        :widget-key="widget.key"
                        :title="widget.flat ? '' : widget.label"
                        :icon="widget.icon"
                        :span="widget.span"
                        :flat="widget.flat"
                        :editing="editMode"
                        @hide="hideWidget"
                        @reorder="reorderWidgets"
                    >
                        <component
                            :is="resolveComponent(widget.component)"
                            v-if="widgetData[widget.key] !== undefined && resolveComponent(widget.component)"
                            :data="widgetData[widget.key]"
                            :range="range"
                        />
                        <!-- Deferred payload still loading -->
                        <div
                            v-else-if="widgetData[widget.key] === undefined"
                            :class="[
                                'animate-pulse rounded-md bg-surface-2',
                                widget.flat ? 'h-[96px]' : widget.span === 'full' ? 'h-[180px]' : 'h-[140px]',
                            ]"
                            :aria-label="t('dashboard.loading')"
                        />
                    </WidgetCard>
                </div>

                <div
                    v-if="!visibleWidgets.length"
                    class="text-center px-5 py-10 border border-dashed border-line-strong rounded-md bg-surface-2"
                >
                    <div class="w-9 h-9 mx-auto mb-2 bg-surface border border-line rounded-lg grid place-items-center text-ink-500">
                        <Icon name="dashboard" />
                    </div>
                    <div class="text-[13px] font-medium mb-0.5">{{ t('dashboard.empty_title') }}</div>
                    <div class="text-xs text-ink-500 max-w-[320px] mx-auto mb-2.5">
                        {{ t('dashboard.empty_description') }}
                    </div>
                    <Button variant="primary" icon="plus" @click="customiseOpen = true">{{ t('dashboard.add_widget') }}</Button>
                </div>

                <div v-if="editMode && hiddenWidgets.length && visibleWidgets.length" class="mt-4 flex justify-center">
                    <Button icon="plus" @click="customiseOpen = true">
                        {{ t('dashboard.add_widget_hidden_count', { count: hiddenWidgets.length }) }}
                    </Button>
                </div>

                <PageZone region="main" position="after" />
            </div>

            <CustomiseDialog
                :open="customiseOpen"
                :hidden="hiddenWidgets"
                @update:open="customiseOpen = $event"
                @add="showWidget"
                @reset="resetLayout"
            />
        </div>
    </PanelLayout>
</template>
