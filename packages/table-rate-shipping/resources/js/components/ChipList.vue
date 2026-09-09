<script setup lang="ts">
import { Flag, Icon } from '@lunarphp/panel';

export interface Chip {
    id: number | string;
    label: string;
    flag?: string | null;
    mono?: boolean;
}

defineProps<{
    chips: Chip[];
    removeLabel: string;
    emptyText: string;
}>();

const emit = defineEmits<{ remove: [id: number | string] }>();
</script>

<template>
    <div class="flex flex-wrap gap-1.5">
        <span
            v-for="chip in chips"
            :key="chip.id"
            class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-surface-2 border border-line text-xs text-ink-900"
        >
            <Flag v-if="chip.flag" :code="chip.flag" class="text-[13px]" />
            <span :class="chip.mono ? 'font-mono' : ''">{{ chip.label }}</span>
            <button
                type="button"
                class="w-4 h-4 inline-flex items-center justify-center rounded-full text-ink-500 hover:text-danger"
                :aria-label="removeLabel"
                @click="emit('remove', chip.id)"
            >
                <Icon name="x" cls="xs" />
            </button>
        </span>
        <span v-if="!chips.length" class="text-xs text-ink-500">{{ emptyText }}</span>
    </div>
</template>
