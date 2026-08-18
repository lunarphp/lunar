<script setup lang="ts">
import { computed } from 'vue';
import { CheckboxIndicator, CheckboxRoot } from 'reka-ui';

const props = defineProps<{
    modelValue?: boolean;
    indeterminate?: boolean;
    ariaLabel?: string;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>();

const state = computed(() => (props.indeterminate ? 'indeterminate' : !!props.modelValue));
</script>

<template>
    <CheckboxRoot
        :model-value="state"
        :aria-label="ariaLabel"
        class="relative inline-flex items-center justify-center w-[15px] h-[15px] shrink-0 cursor-pointer rounded-[4px] border border-line-strong bg-surface transition-[background-color,border-color] duration-100 hover:border-ink-400 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35 data-[state=checked]:bg-ink-900 data-[state=checked]:border-ink-900 data-[state=indeterminate]:bg-ink-900 data-[state=indeterminate]:border-ink-900"
        @update:model-value="(v) => emit('update:modelValue', Boolean(v))"
    >
        <CheckboxIndicator class="pointer-events-none flex items-center justify-center text-white">
            <span v-if="indeterminate" class="block w-[9px] h-[2px] bg-white rounded-[1px]" />
            <span v-else class="block w-1 h-2 border-r-2 border-b-2 border-white rotate-45 -translate-y-[1px]" />
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
