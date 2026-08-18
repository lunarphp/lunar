<script setup lang="ts">
import { computed } from 'vue';
import {
    DropdownMenuContent,
    DropdownMenuPortal,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuRoot,
    DropdownMenuTrigger,
} from 'reka-ui';
import Icon from './Icon.vue';

export interface FilterOption {
    value: string | number;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: string | number;
        label: string;
        icon?: string | null;
        options: FilterOption[];
        defaultValue?: string | number;
    }>(),
    { icon: null, defaultValue: 'all' },
);

const emit = defineEmits<{ 'update:modelValue': [value: string | number] }>();

const current = computed(() => props.options.find((o) => o.value === props.modelValue));
const isDefault = computed(() => props.modelValue === props.defaultValue);
const triggerLabel = computed(() => (isDefault.value || !current.value ? props.label : current.value.label));
</script>

<template>
    <DropdownMenuRoot>
        <DropdownMenuTrigger
            :class="[
                'inline-flex items-center gap-1.5 h-[30px] px-2.5 rounded-md font-medium text-[12.5px] whitespace-nowrap transition-[background-color,border-color,box-shadow,transform,color] duration-100 active:translate-y-[0.5px] active:shadow-none focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35',
                'bg-surface border border-line-strong shadow-sm hover:bg-surface-2 hover:border-ink-300 focus-visible:border-sage data-[state=open]:bg-surface-2 data-[state=open]:border-ink-300',
                isDefault ? 'text-ink-700' : 'text-ink-900',
            ]"
        >
            <Icon v-if="icon" :name="icon" cls="sm" class="text-ink-500" />
            <span>{{ triggerLabel }}</span>
            <Icon name="chevUpDown" cls="sm" class="text-ink-400" />
        </DropdownMenuTrigger>

        <DropdownMenuPortal>
            <DropdownMenuContent
                :side-offset="4"
                align="start"
                class="z-[60] min-w-[180px] bg-surface border border-line rounded-md shadow-lg py-1"
            >
                <DropdownMenuRadioGroup
                    :model-value="String(modelValue)"
                    @update:model-value="(v) => {
                        const opt = options.find((o) => String(o.value) === v);
                        if (opt) emit('update:modelValue', opt.value);
                    }"
                >
                    <DropdownMenuRadioItem
                        v-for="opt in options"
                        :key="String(opt.value)"
                        :value="String(opt.value)"
                        :class="[
                            'w-full flex items-center gap-2 px-2.5 h-8 text-[12.5px] text-left cursor-pointer outline-none data-[highlighted]:bg-surface-2',
                            opt.value === modelValue ? 'text-ink-900 font-medium' : 'text-ink-700',
                        ]"
                    >
                        <Icon name="check" cls="sm" :class="opt.value === modelValue ? 'text-ink-900' : 'invisible'" />
                        <span class="truncate">{{ opt.label }}</span>
                    </DropdownMenuRadioItem>
                </DropdownMenuRadioGroup>
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>
