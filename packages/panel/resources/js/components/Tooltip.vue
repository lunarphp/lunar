<script setup lang="ts">
import { TooltipContent, TooltipPortal, TooltipRoot, TooltipTrigger } from 'reka-ui';

withDefaults(
    defineProps<{
        text?: string;
        disabled?: boolean;
    }>(),
    { text: '', disabled: false },
);
</script>

<template>
    <TooltipRoot v-if="text && !disabled">
        <TooltipTrigger as-child>
            <slot />
        </TooltipTrigger>
        <TooltipPortal>
            <TooltipContent
                :side-offset="6"
                class="bg-ink-900 text-paper text-[11px] font-medium leading-snug px-[7px] py-[5px] rounded-sm max-w-[240px] shadow-md z-[9999] select-none"
            >
                {{ text }}
            </TooltipContent>
        </TooltipPortal>
    </TooltipRoot>
    <slot v-else />
</template>
