<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import Button from './Button.vue';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
}

defineProps<{ meta: PaginationMeta }>();

const go = (url: string | null): void => {
    if (!url) {
        return;
    }

    router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true });
};
</script>

<template>
    <div
        v-if="meta.last_page > 1"
        class="flex items-center justify-between text-xs text-ink-500"
    >
        <div>Showing <span class="text-ink-900 font-medium">{{ meta.from ?? 0 }}–{{ meta.to ?? 0 }}</span> of {{ meta.total }}</div>
        <div class="flex items-center gap-2">
            <Button
                variant="ghost"
                size="sm"
                icon="chevronLeft"
                aria-label="Previous page"
                class="!w-[26px] !h-[26px]"
                :disabled="!meta.prev_page_url"
                @click="go(meta.prev_page_url)"
            />
            <span class="[font-variant-numeric:tabular-nums]">
                <span class="text-ink-900 font-medium">{{ meta.current_page }}</span> / {{ meta.last_page }}
            </span>
            <Button
                variant="ghost"
                size="sm"
                icon="chevronRight"
                aria-label="Next page"
                class="!w-[26px] !h-[26px]"
                :disabled="!meta.next_page_url"
                @click="go(meta.next_page_url)"
            />
        </div>
    </div>
</template>
