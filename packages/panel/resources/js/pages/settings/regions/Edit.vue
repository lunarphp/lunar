<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import Combobox from '../../../components/Combobox.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Flag from '../../../components/Flag.vue';
import Section from '../../../components/Section.vue';
import Select from '../../../components/Select.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Region = {
    id: number;
    name: string;
    handle: string;
    channel_id: number;
    currency_id: number;
    language_id: number;
    tax_zone_id: number | null;
    prices_inc_tax: boolean | null;
    default: boolean;
    countries: number[];
};

type NamedOption = { id: number; name: string };
type CountryOption = { id: number; name: string; iso2: string | null };

const props = defineProps<{
    region: Region;
    channels: NamedOption[];
    currencies: { id: number; code: string; name: string }[];
    languages: { id: number; code: string; name: string }[];
    taxZones: NamedOption[];
    countries: CountryOption[];
    hasOrderHistory: boolean;
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('regions.title'), href: props.urls.index },
    { label: props.region.name, current: true },
]);

// prices_inc_tax is tri-state; '' means "inherit the global default".
const form = useForm({
    name: props.region.name,
    handle: props.region.handle,
    channel_id: props.region.channel_id,
    currency_id: props.region.currency_id,
    language_id: props.region.language_id,
    tax_zone_id: props.region.tax_zone_id,
    prices_inc_tax: props.region.prices_inc_tax === null ? '' : String(props.region.prices_inc_tax),
    default: props.region.default,
    countries: [...props.region.countries],
});

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        prices_inc_tax: data.prices_inc_tax === '' ? null : data.prices_inc_tax === 'true',
    })).put(props.urls.update, { preserveScroll: true });
};

const countryFor = (id: number): CountryOption | undefined => props.countries.find((c) => c.id === id);

const availableCountries = computed(() =>
    props.countries.filter((c) => !form.countries.includes(c.id)).map((c) => ({ value: c.id, label: c.name, flag: c.iso2 })));

const addCountry = (id: string | number): void => {
    form.countries.push(Number(id));
};

// The default region cannot be deleted or un-defaulted; promote another region instead.
const deleteBlockedReason = computed<string>(() => {
    if (props.region.default) return t('regions.delete_blocked_default');
    if (props.hasOrderHistory) return t('regions.delete_blocked');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('regions.edit_title', { name: region.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('regions.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('regions.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel required :hint="t('regions.handle_hint')">{{ t('regions.field_handle') }}</FieldLabel>
                    <TextInput v-model="form.handle" mono :invalid="!!form.errors.handle" />
                    <div v-if="form.errors.handle" class="mt-1 text-[11px] text-danger">{{ form.errors.handle }}</div>
                </div>
                <div>
                    <FieldLabel required>{{ t('regions.field_channel') }}</FieldLabel>
                    <Select v-model="form.channel_id">
                        <option v-for="channel in channels" :key="channel.id" :value="channel.id">{{ channel.name }}</option>
                    </Select>
                </div>
                <div>
                    <FieldLabel required>{{ t('regions.field_currency') }}</FieldLabel>
                    <Select v-model="form.currency_id">
                        <option v-for="currency in currencies" :key="currency.id" :value="currency.id">{{ currency.code }} — {{ currency.name }}</option>
                    </Select>
                </div>
                <div>
                    <FieldLabel required>{{ t('regions.field_language') }}</FieldLabel>
                    <Select v-model="form.language_id">
                        <option v-for="language in languages" :key="language.id" :value="language.id">{{ language.code }} — {{ language.name }}</option>
                    </Select>
                </div>
                <div>
                    <FieldLabel>{{ t('regions.field_tax_zone') }}</FieldLabel>
                    <Select v-model="form.tax_zone_id">
                        <option :value="null">{{ t('regions.no_tax_zone') }}</option>
                        <option v-for="taxZone in taxZones" :key="taxZone.id" :value="taxZone.id">{{ taxZone.name }}</option>
                    </Select>
                </div>
                <div>
                    <FieldLabel :hint="t('regions.price_display_hint')">{{ t('regions.field_price_display') }}</FieldLabel>
                    <Select v-model="form.prices_inc_tax">
                        <option value="">{{ t('regions.price_display_inherit') }}</option>
                        <option value="true">{{ t('regions.price_display_inc') }}</option>
                        <option value="false">{{ t('regions.price_display_exc') }}</option>
                    </Select>
                </div>
            </div>
        </Section>

        <Section :title="t('regions.section_countries', { count: form.countries.length })">
            <template #desc>{{ t('regions.countries_desc') }}</template>

            <div class="flex flex-wrap gap-1.5 mb-3">
                <span
                    v-for="id in form.countries"
                    :key="id"
                    class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-surface-2 border border-line text-xs text-ink-900"
                >
                    <Flag v-if="countryFor(id)?.iso2" :code="countryFor(id)!.iso2" class="text-[13px]" />
                    {{ countryFor(id)?.name ?? id }}
                    <button
                        type="button"
                        class="w-4 h-4 inline-flex items-center justify-center rounded-full text-ink-500 hover:text-danger"
                        :aria-label="t('regions.remove_country')"
                        @click="form.countries = form.countries.filter((c) => c !== id)"
                    >
                        ×
                    </button>
                </span>
                <span v-if="!form.countries.length" class="text-xs text-ink-500">{{ t('regions.no_countries') }}</span>
            </div>

            <div class="max-w-[340px]">
                <Combobox :options="availableCountries" :placeholder="t('regions.add_country_placeholder')" @change="addCountry" />
            </div>
        </Section>

        <Section :title="t('regions.section_state')">
            <label class="flex items-center gap-3" :class="region.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                <Toggle :on="form.default" :disabled="region.default" @toggle="form.default = !form.default" />
                <div>
                    <div class="text-[12.5px] text-ink-900 font-medium">{{ t('regions.default_region') }}</div>
                    <div class="text-[11px] text-ink-500">
                        {{ region.default ? t('regions.default_locked_hint') : t('regions.default_region_hint') }}
                    </div>
                </div>
            </label>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('regions.confirm_delete_title')"
        :description="t('regions.confirm_delete_body', { name: region.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
