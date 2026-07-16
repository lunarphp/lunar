<script setup lang="ts">
// A settings page renders SettingsShell as its entire chrome — the settings
// sidebar, mobile drawer, breadcrumbs, page header (title + description +
// actions), and flash messages all come from the shell, exactly as on
// first-party settings pages like Channels.
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, FieldLabel, SettingsShell, TextInput, Toggle } from '@lunarphp/panel';

// The panel auto-applies its PanelLayout (the main-nav shell) as the persistent
// layout of add-on pages that don't set one. SettingsShell replaces that chrome
// wholesale, so opt out with a no-op persistent layout — the resolver leaves an
// add-on page with its own layout alone.
defineOptions({
    layout: (_h: unknown, page: unknown) => page,
});

const props = defineProps<{
    settings: { webhook_url: string | null; ping_enabled: boolean };
    urls: { update: string };
}>();

const { t } = useI18n();

const form = useForm({
    webhook_url: props.settings.webhook_url ?? '',
    ping_enabled: props.settings.ping_enabled,
});

const submit = (): void => {
    form.post(props.urls.update, { preserveScroll: true });
};
</script>

<template>
    <SettingsShell
        :title="t('example-addon::example.settings_title')"
        :description="t('example-addon::example.settings_description')"
    >
        <div data-screen-label="Example Add-on Settings">
            <form class="flex flex-col gap-4" @submit.prevent="submit">
                <div>
                    <FieldLabel>{{ t('example-addon::example.settings_webhook_url') }}</FieldLabel>
                    <TextInput
                        v-model="form.webhook_url"
                        type="url"
                        :invalid="!!form.errors.webhook_url"
                        placeholder="https://example.com/webhooks"
                    />
                    <div v-if="form.errors.webhook_url" class="mt-1 text-[11px] text-danger">{{ form.errors.webhook_url }}</div>
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.ping_enabled" @toggle="form.ping_enabled = !form.ping_enabled" />
                    <span class="text-[12.5px] text-ink-900 font-medium">{{ t('example-addon::example.settings_ping_enabled') }}</span>
                </label>

                <div>
                    <!-- Panel-group keys (common.*) resolve too — add-ons share the
                         panel's vue-i18n instance and message store. -->
                    <Button type="submit" variant="primary" :disabled="form.processing">{{ t('common.save') }}</Button>
                </div>
            </form>
        </div>
    </SettingsShell>
</template>
