<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '../Icon.vue';

export interface ReorderEvent {
    fromKey: string;
    toKey: string;
    position: 'before' | 'after';
}

const props = withDefaults(
    defineProps<{
        widgetKey: string;
        title?: string;
        icon?: string | null;
        span?: 'full' | 'half';
        editing?: boolean;
        /** No card shell (the KPI row); editing still wraps it in a dashed frame. */
        flat?: boolean;
    }>(),
    { title: '', icon: null, span: 'half', editing: false, flat: false },
);

const emit = defineEmits<{ hide: [key: string]; reorder: [event: ReorderEvent] }>();

const { t } = useI18n();

const dragOver = ref<'before' | 'after' | null>(null);
const isDragging = ref(false);

const onDragStart = (event: DragEvent): void => {
    if (!props.editing || !event.dataTransfer) {
        return;
    }

    isDragging.value = true;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/x-widget-key', props.widgetKey);
};

const onDragEnd = (): void => {
    isDragging.value = false;
    dragOver.value = null;
};

const onDragOver = (event: DragEvent): void => {
    if (!props.editing) {
        return;
    }

    event.preventDefault();

    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }

    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
    dragOver.value = event.clientY < rect.top + rect.height / 2 ? 'before' : 'after';
};

const onDrop = (event: DragEvent): void => {
    if (!props.editing) {
        return;
    }

    event.preventDefault();

    const fromKey = event.dataTransfer?.getData('text/x-widget-key');
    const position = dragOver.value ?? 'before';
    dragOver.value = null;

    if (fromKey && fromKey !== props.widgetKey) {
        emit('reorder', { fromKey, toKey: props.widgetKey, position });
    }
};

const showHeader = computed(() => (!!props.title && !props.flat) || props.editing);
</script>

<template>
    <div
        :class="[
            'relative flex flex-col min-w-0',
            span === 'full' ? 'lg:col-span-2' : '',
            flat ? '' : 'bg-surface border border-line rounded-xl shadow-sm',
            isDragging ? 'opacity-50' : '',
            editing && !flat ? 'ring-1 ring-ink-300/40' : '',
            editing && flat ? 'border border-dashed border-line-strong rounded-xl p-2' : '',
        ]"
        :draggable="editing"
        :data-widget="widgetKey"
        @dragstart="onDragStart"
        @dragend="onDragEnd"
        @dragover="onDragOver"
        @dragleave="dragOver = null"
        @drop="onDrop"
    >
        <!-- Drop indicators -->
        <div
            v-if="editing && dragOver === 'before'"
            class="absolute -top-1.5 left-2 right-2 h-[3px] rounded-full bg-chart-1 pointer-events-none z-10"
        />
        <div
            v-if="editing && dragOver === 'after'"
            class="absolute -bottom-1.5 left-2 right-2 h-[3px] rounded-full bg-chart-1 pointer-events-none z-10"
        />

        <div
            v-if="showHeader"
            :class="['flex items-center gap-2.5 min-w-0', flat ? 'px-1 pb-2' : 'px-4 py-3 border-b border-line']"
        >
            <div
                v-if="editing"
                class="text-ink-400 cursor-grab active:cursor-grabbing -ml-1 select-none"
                :title="t('dashboard.drag_handle')"
                :aria-label="t('dashboard.drag_handle')"
            >
                <Icon name="grip" />
            </div>
            <Icon v-if="icon && !editing && !flat" :name="icon" cls="sm" class="text-ink-500" />
            <h3 v-if="title" class="m-0 text-[13px] font-semibold tracking-[-0.01em] text-ink-900 truncate flex-1">
                {{ title }}
            </h3>
            <div v-else class="flex-1" />
            <button
                v-if="editing"
                type="button"
                class="w-6 h-6 rounded-md grid place-items-center text-ink-500 hover:bg-surface-2 hover:text-ink-900 shrink-0"
                :aria-label="t('dashboard.hide_widget')"
                :title="t('dashboard.hide_widget')"
                @click="emit('hide', widgetKey)"
            >
                <Icon name="x" cls="sm" />
            </button>
        </div>

        <div :class="['min-w-0 flex-1', flat ? '' : 'p-4']">
            <slot />
        </div>
    </div>
</template>
