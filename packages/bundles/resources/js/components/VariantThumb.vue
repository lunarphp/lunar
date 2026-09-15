<script setup lang="ts">
import { Icon } from '@lunarphp/panel';
import type { VariantRow } from '../types';

defineProps<{ variant: VariantRow }>();
</script>

<template>
    <div class="flex items-center gap-2.5 min-w-0">
        <div class="w-8 h-8 shrink-0 rounded-md border border-line bg-surface-2 overflow-hidden flex items-center justify-center text-ink-400">
            <img v-if="variant.thumbnail" :src="variant.thumbnail" alt="" class="w-full h-full object-cover" />
            <Icon v-else name="image" cls="sm" />
        </div>
        <div class="min-w-0">
            <div class="text-[12.5px] font-medium text-ink-900 truncate">{{ variant.name }}</div>
            <div class="text-[11px] text-ink-500 truncate">
                <span v-if="variant.sku" class="font-mono">{{ variant.sku }}</span>
                <span v-if="variant.sku && variant.option"> / </span>
                <span v-if="variant.option">{{ variant.option }}</span>
            </div>
        </div>
    </div>
</template>
