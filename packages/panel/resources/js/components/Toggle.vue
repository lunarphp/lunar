<script setup lang="ts">
import { SwitchRoot, SwitchThumb } from 'reka-ui';

defineProps<{ on?: boolean; disabled?: boolean }>();
const emit = defineEmits<{ toggle: [] }>();
</script>

<template>
    <SwitchRoot
        :model-value="on"
        :disabled="disabled"
        :class="[
            'relative inline-block w-[34px] h-5 rounded-full transition-colors duration-150 shrink-0 border border-transparent focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35',
            disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
            on ? 'bg-ink-900' : 'bg-line-strong',
        ]"
        @update:model-value="emit('toggle')"
    >
        <SwitchThumb
            :class="[
                'block absolute top-0.5 left-0.5 w-3.5 h-3.5 bg-white rounded-full transition-transform duration-150 shadow-[0_1px_2px_rgba(0,0,0,0.2)]',
                on ? 'translate-x-3.5' : '',
            ]"
        />
    </SwitchRoot>
</template>
