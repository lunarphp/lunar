<script setup lang="ts">
import { computed, ref, useSlots } from 'vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { useI18n } from 'vue-i18n';
import Flag from './Flag.vue';
import Icon from './Icon.vue';

export type ComboboxOption = { value: string | number; label: string; flag?: string | null };

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        options: ComboboxOption[];
        placeholder?: string;
        emptyText?: string;
        invalid?: boolean;
    }>(),
    { modelValue: null, invalid: false },
);

const emit = defineEmits<{
    'update:modelValue': [value: string | number];
    change: [value: string | number];
}>();

const { t } = useI18n();

const slots = useSlots();

const open = ref(false);

const selected = computed(() => props.options.find((o) => o.value === props.modelValue));
const selectedLabel = computed(() => selected.value?.label ?? '');

// reka-ui's update:model-value can emit null (e.g. cleared selection); ignore it
// since this control always carries a concrete value.
const onSelect = (value: string | number | null) => {
    if (value === null) {
        return;
    }

    emit('update:modelValue', value);
    emit('change', value);
};

const inputCls = computed(() => [
    'w-full h-8 pr-8 border rounded-md bg-surface text-[13px] text-ink-900 placeholder:text-ink-400 transition-[border-color,box-shadow] duration-100 focus:outline-none focus:ring-3 data-[state=open]:border-ink-300',
    slots.prefix ? 'pl-8' : 'pl-2.5',
    props.invalid
        ? 'border-danger-border focus:border-danger focus:ring-danger/30'
        : 'border-line-strong hover:border-ink-300 focus:border-sage focus:ring-sage/35',
]);
</script>

<template>
    <ComboboxRoot
        :model-value="modelValue"
        :open="open"
        open-on-click
        :reset-search-term-on-blur="true"
        @update:model-value="onSelect"
        @update:open="(v) => (open = v)"
    >
        <ComboboxAnchor class="relative block">
            <div
                v-if="$slots.prefix"
                class="absolute inset-y-0 left-0 w-8 grid place-items-center pointer-events-none text-ink-500"
            >
                <slot name="prefix" :selected="selected" />
            </div>
            <ComboboxInput
                :class="inputCls"
                :placeholder="placeholder ?? t('common.search_placeholder')"
                :display-value="() => selectedLabel"
            />
            <ComboboxTrigger
                class="absolute inset-y-0 right-0 w-8 grid place-items-center text-ink-400 hover:text-ink-700 focus:outline-none"
                :aria-label="t('common.toggle_options')"
            >
                <Icon name="chevDown" cls="sm" />
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxPortal>
            <ComboboxContent
                :side-offset="4"
                position="popper"
                class="z-50 bg-surface border border-line rounded-md shadow-lg py-1 w-[var(--reka-combobox-trigger-width)] max-h-[260px] overflow-hidden"
            >
                <ComboboxViewport class="overflow-y-auto max-h-[260px]">
                    <ComboboxEmpty class="px-2.5 py-2 text-[12.5px] text-ink-500">
                        {{ emptyText ?? t('common.no_matches') }}
                    </ComboboxEmpty>
                    <ComboboxItem
                        v-for="opt in options"
                        :key="String(opt.value)"
                        :value="opt.value"
                        :text-value="opt.label"
                        :class="[
                            'flex items-center gap-2 px-2.5 h-8 text-[12.5px] cursor-pointer outline-none data-[highlighted]:bg-surface-2',
                            opt.value === modelValue ? 'text-ink-900 font-medium' : 'text-ink-700',
                        ]"
                    >
                        <Icon
                            name="check"
                            cls="sm"
                            :class="opt.value === modelValue ? 'text-ink-900' : 'invisible'"
                        />
                        <Flag v-if="opt.flag" :code="opt.flag" class="shrink-0 text-[13px]" />
                        <span class="truncate">{{ opt.label }}</span>
                    </ComboboxItem>
                </ComboboxViewport>
            </ComboboxContent>
        </ComboboxPortal>
    </ComboboxRoot>
</template>
