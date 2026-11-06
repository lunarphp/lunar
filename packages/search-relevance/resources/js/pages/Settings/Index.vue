<script setup lang="ts">
import { computed, type Component, type VNode } from 'vue';
import { useI18n } from 'vue-i18n';
import { SettingsShell, StatusBadge } from '@lunarphp/panel';

// SettingsShell supplies the whole chrome, so opt out of the auto-applied PanelLayout.
defineOptions({
    layout: (_h: unknown, page: Component): VNode => page as unknown as VNode,
});

// Read-only: mode and weights are configured in code, like the rest of Lunar.
const props = defineProps<{
    mode: string;
    mode_env: string;
    weights: Record<string, number>;
    schedule: string;
    last_run: string | null;
    versions: { model: string; label: string; version: string }[];
}>();

const { t } = useI18n();

const modeHelp = computed(() => t(`search-relevance::panel.settings_mode_help_${props.mode}`));

const modeTone = computed((): 'sage' | 'warn' | 'archived' => {
    if (props.mode === 'on') {
        return 'sage';
    }

    return props.mode === 'shadow' ? 'warn' : 'archived';
});

const formatDate = (value: string | null): string => {
    if (!value) {
        return t('search-relevance::panel.settings_last_run_never');
    }

    const date = new Date(value.replace(' ', 'T'));

    return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
};
</script>

<template>
    <SettingsShell
        :title="t('search-relevance::panel.settings_title')"
        :description="t('search-relevance::panel.settings_description')"
    >
        <div data-screen-label="Search relevance settings" class="flex flex-col gap-6">
            <div>
                <h3 class="text-[13px] font-semibold text-ink-900 mb-2">{{ t('search-relevance::panel.settings_mode') }}</h3>
                <div class="flex items-center gap-3">
                    <StatusBadge :tone="modeTone" size="sm" dot>
                        {{ t(`search-relevance::panel.settings_mode_${mode}`) }}
                    </StatusBadge>
                    <span class="text-[12.5px] text-ink-700">{{ modeHelp }}</span>
                </div>
                <p class="mt-2 text-[11px] text-ink-500">{{ t('search-relevance::panel.settings_mode_config', { env: mode_env }) }}</p>
            </div>

            <div>
                <h3 class="text-[13px] font-semibold text-ink-900 mb-2">{{ t('search-relevance::panel.settings_weights') }}</h3>
                <dl class="grid grid-cols-3 gap-2.5 max-w-xl">
                    <div v-for="(weight, event) in weights" :key="event" class="rounded-md border border-line bg-surface-2 px-3 py-2">
                        <dt class="text-[11px] text-ink-500">{{ t(`search-relevance::panel.settings_weight_${event}`) }}</dt>
                        <dd class="text-[15px] font-semibold text-ink-900 [font-variant-numeric:tabular-nums]">{{ weight }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-[13px] font-semibold text-ink-900 mb-2">{{ t('search-relevance::panel.settings_scoring') }}</h3>
                <dl class="flex flex-col gap-1.5 max-w-xl text-[12.5px]">
                    <div class="flex items-center justify-between gap-3 rounded-md border border-line px-3 py-2">
                        <dt class="text-ink-500">{{ t('search-relevance::panel.settings_last_run') }}</dt>
                        <dd class="text-ink-900 font-medium">{{ formatDate(last_run) }}</dd>
                    </div>
                    <div class="px-3 text-[11px] text-ink-500">{{ t('search-relevance::panel.settings_schedule', { time: schedule }) }}</div>
                </dl>
            </div>

            <div>
                <h3 class="text-[13px] font-semibold text-ink-900 mb-1">{{ t('search-relevance::panel.settings_versions') }}</h3>
                <p class="text-[11px] text-ink-500 mb-2">{{ t('search-relevance::panel.settings_versions_help') }}</p>
                <ul class="flex flex-col gap-1.5 max-w-xl">
                    <li v-for="entry in versions" :key="entry.model" class="flex items-center justify-between gap-3 rounded-md border border-line px-3 py-2">
                        <span class="text-[12.5px] text-ink-900 font-medium" :title="entry.model">{{ entry.label }}</span>
                        <code class="text-[11.5px] text-ink-700">{{ entry.version }}</code>
                    </li>
                </ul>
            </div>
        </div>
    </SettingsShell>
</template>
