<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Section from '../../../components/Section.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Currency = {
    id: number;
    code: string;
    name: string;
    exchange_rate: number;
    decimal_places: number;
    enabled: boolean;
    default: boolean;
    sync_prices: boolean;
};

const props = defineProps<{
    currency: Currency;
    hasPrices: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('currencies.title'), href: props.urls.index },
    { label: props.currency.code, current: true },
]);

const form = useForm({
    code: props.currency.code,
    name: props.currency.name,
    exchange_rate: String(props.currency.exchange_rate),
    decimal_places: String(props.currency.decimal_places),
    enabled: props.currency.enabled,
    default: props.currency.default,
    sync_prices: props.currency.sync_prices,
});

const submit = (): void => {
    form.put(props.urls.update);
};

// The default currency cannot be deleted or un-defaulted; promote another currency instead.
const deleteBlockedReason = computed<string>(() => {
    if (props.currency.default) return t('currencies.delete_blocked_default');
    if (props.hasPrices) return t('currencies.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('currencies.edit_title', { code: currency.code })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('currencies.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required :hint="t('currencies.code_hint')">{{ t('currencies.field_code') }}</FieldLabel>
                    <TextInput :model-value="currency.code" disabled mono />
                </div>
                <div>
                    <FieldLabel required>{{ t('currencies.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('currencies.field_exchange_rate') }}</FieldLabel>
                    <TextInput v-model="form.exchange_rate" type="number" step="0.0001" min="0" :invalid="!!form.errors.exchange_rate" />
                    <div v-if="form.errors.exchange_rate" class="mt-1 text-[11px] text-danger">{{ form.errors.exchange_rate }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('currencies.field_decimal_places') }}</FieldLabel>
                    <TextInput v-model="form.decimal_places" type="number" min="0" max="4" :invalid="!!form.errors.decimal_places" />
                    <div v-if="form.errors.decimal_places" class="mt-1 text-[11px] text-danger">{{ form.errors.decimal_places }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('currencies.section_state')">
            <div class="flex flex-col gap-4">
                <label class="flex items-center gap-3" :class="currency.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                    <Toggle :on="form.default" :disabled="currency.default" @toggle="form.default = !form.default" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('currencies.default_currency') }}</div>
                        <div class="text-[11px] text-ink-500">
                            {{ currency.default ? t('currencies.default_locked_hint') : t('currencies.default_currency_hint') }}
                        </div>
                    </div>
                </label>
                <label class="flex items-center gap-3" :class="currency.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                    <Toggle :on="form.enabled" :disabled="currency.default" @toggle="form.enabled = !form.enabled" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('common.enabled') }}</div>
                        <div class="text-[11px] text-ink-500">
                            {{ currency.default ? t('currencies.enabled_locked_hint') : t('currencies.enabled_hint') }}
                        </div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.sync_prices" @toggle="form.sync_prices = !form.sync_prices" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('currencies.sync_prices') }}</div>
                        <div class="text-[11px] text-ink-500">{{ t('currencies.sync_prices_hint') }}</div>
                    </div>
                </label>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('currencies.confirm_delete_title')"
        :description="t('currencies.confirm_delete_body', { code: currency.code })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
