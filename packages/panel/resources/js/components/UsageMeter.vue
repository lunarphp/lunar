<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    used: number;
    /** null means no redemption limit, so there is no bar to draw. */
    max: number | null;
}>();

const percent = computed(() => {
    if (!props.max) {
        return 0;
    }

    return Math.min(100, Math.round((props.used / props.max) * 100));
});

const tone = computed(() => {
    if (!props.max) {
        return 'bg-ink-300';
    }

    if (percent.value >= 100) {
        return 'bg-danger';
    }

    return percent.value >= 80 ? 'bg-warn' : 'bg-sage';
});
</script>

<template>
    <div class="min-w-0">
        <div class="text-[11.5px] text-ink-900 [font-variant-numeric:tabular-nums]">
            <template v-if="max">{{ t('discounts.usage_of', { used, max }) }}</template>
            <template v-else>{{ used }} <span class="text-ink-400">{{ t('discounts.usage_unlimited') }}</span></template>
        </div>
        <div v-if="max" class="mt-1 h-1 rounded-full bg-surface-2 overflow-hidden">
            <div class="h-full rounded-full" :class="tone" :style="{ width: `${percent}%` }" />
        </div>
    </div>
</template>
