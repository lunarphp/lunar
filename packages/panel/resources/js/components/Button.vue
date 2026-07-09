<script setup lang="ts">
import { computed, useSlots } from 'vue';
import Icon from './Icon.vue';

const props = withDefaults(
    defineProps<{
        variant?: 'default' | 'primary' | 'ghost';
        size?: 'md' | 'sm';
        icon?: string;
        iconCls?: string;
        type?: 'button' | 'submit';
        disabled?: boolean;
    }>(),
    { variant: 'default', size: 'md', iconCls: 'sm', type: 'button' },
);

const slots = useSlots();
const iconOnly = computed(() => !slots.default && !!props.icon);

const base =
    'inline-flex items-center justify-center gap-1.5 rounded-md font-medium whitespace-nowrap transition-[background-color,border-color,box-shadow,transform,color] duration-100 active:translate-y-[0.5px] active:shadow-none focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35 disabled:opacity-60 disabled:cursor-not-allowed';

const sizeCls = computed(() => {
    if (iconOnly.value) {
        return props.size === 'sm' ? 'h-[26px] w-[26px] p-0' : 'h-[30px] w-[30px] p-0';
    }

    return props.size === 'sm' ? 'h-[26px] px-2 text-xs' : 'h-[30px] px-2.5 text-[12.5px]';
});

const variantCls = computed(() => {
    if (props.variant === 'primary') {
        return 'bg-ink-900 text-paper shadow-sm hover:bg-ink-700';
    }

    if (props.variant === 'ghost') {
        return 'bg-transparent border border-transparent text-ink-900 hover:bg-surface-2 focus-visible:border-sage';
    }

    return 'bg-surface border border-line-strong text-ink-900 shadow-sm hover:bg-surface-2 hover:border-ink-300 focus-visible:border-sage';
});
</script>

<template>
    <button :type="type" :disabled="disabled" :class="[base, sizeCls, variantCls]">
        <Icon v-if="icon" :name="icon" :cls="iconCls" />
        <slot />
    </button>
</template>
