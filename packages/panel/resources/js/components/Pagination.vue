<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
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

const { t } = useI18n();

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
        <i18n-t keypath="common.showing_results" tag="div" scope="global">
            <template #range>
                <span class="text-ink-900 font-medium">{{ meta.from ?? 0 }}–{{ meta.to ?? 0 }}</span>
            </template>
            <template #total>{{ meta.total }}</template>
        </i18n-t>
        <div class="flex items-center gap-2">
            <Button
                variant="ghost"
                size="sm"
                icon="chevronLeft"
                :aria-label="t('common.previous_page')"
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
                :aria-label="t('common.next_page')"
                class="!w-[26px] !h-[26px]"
                :disabled="!meta.next_page_url"
                @click="go(meta.next_page_url)"
            />
        </div>
    </div>
</template>
