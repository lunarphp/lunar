<script setup lang="ts">
import { computed, ref, type Component, type VNode } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, ConfirmDialog, FieldLabel, Select, SettingsShell, StatusBadge } from '@lunarphp/panel';

// SettingsShell supplies the whole chrome, so opt out of the auto-applied PanelLayout.
defineOptions({
    layout: (_h: unknown, page: Component): VNode => page as unknown as VNode,
});

const props = defineProps<{
    mode: string;
    modes: string[];
    weights: Record<string, number>;
    versions: { model: string; label: string; version: string }[];
    urls: { update: string };
}>();

const { t } = useI18n();

const form = useForm({ mode: props.mode });
const confirmOpen = ref(false);

const modeHelp = computed(() => t(`search-relevance::panel.settings_mode_help_${form.mode}`));

const modeTone = (mode: string): 'sage' | 'warn' | 'archived' => {
    if (mode === 'on') {
        return 'sage';
    }

    return mode === 'shadow' ? 'warn' : 'archived';
};

const save = (): void => {
    form.post(props.urls.update, { preserveScroll: true });
};

// Switching to `on` changes what shoppers see, so it is confirmed first.
const submit = (): void => {
    if (form.mode === 'on' && props.mode !== 'on') {
        confirmOpen.value = true;

        return;
    }

    save();
};
</script>

<template>
    <SettingsShell
        :title="t('search-relevance::panel.settings_title')"
        :description="t('search-relevance::panel.settings_description')"
    >
        <div data-screen-label="Search relevance settings" class="flex flex-col gap-6">
            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <div class="max-w-sm">
                    <FieldLabel>{{ t('search-relevance::panel.settings_mode') }}</FieldLabel>
                    <Select v-model="form.mode" :invalid="!!form.errors.mode">
                        <option v-for="option in modes" :key="option" :value="option">
                            {{ t(`search-relevance::panel.settings_mode_${option}`) }}
                        </option>
                    </Select>
                    <div class="mt-1 text-[11px]" :class="form.errors.mode ? 'text-danger' : 'text-ink-500'">
                        {{ form.errors.mode ?? modeHelp }}
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button type="submit" variant="primary" :disabled="form.processing">{{ t('common.save') }}</Button>
                    <StatusBadge :tone="modeTone(mode)" size="sm" dot>
                        {{ t(`search-relevance::panel.settings_mode_${mode}`) }}
                    </StatusBadge>
                </div>
            </form>

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

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="t('search-relevance::panel.settings_confirm_on_title')"
            :description="t('search-relevance::panel.settings_confirm_on_description')"
            @confirm="save"
        />
    </SettingsShell>
</template>
