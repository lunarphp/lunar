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

type TaxZone = {
    id: number;
    name: string;
    zone_type: string;
    active: boolean;
    default: boolean;
};

type ZonePostcode = { country_id: number; postcode: string };

type ZoneRate = {
    id: number | null;
    name: string;
    priority: number;
    amounts: Record<number, number>;
};

type CountryOption = { id: number; name: string; iso2: string | null };
type StateOption = { id: number; name: string; code: string; country: string | null };
type NamedOption = { id: number; name: string };

const props = defineProps<{
    taxZone: TaxZone;
    coverage: {
        countries: number[];
        states: number[];
        postcodes: ZonePostcode[];
        customerGroups: number[];
    };
    rates: ZoneRate[];
    taxClasses: NamedOption[];
    countries: CountryOption[];
    states: StateOption[];
    customerGroups: NamedOption[];
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('tax_zones.title'), href: props.urls.index },
    { label: props.taxZone.name, current: true },
]);

const form = useForm({
    name: props.taxZone.name,
    zone_type: props.taxZone.zone_type,
    active: props.taxZone.active,
    default: props.taxZone.default,
    countries: [...props.coverage.countries],
    states: [...props.coverage.states],
    postcodes: props.coverage.postcodes.map((row) => ({ ...row })),
    customer_groups: [...props.coverage.customerGroups],
    rates: props.rates.map((rate) => ({ ...rate, amounts: { ...rate.amounts } })),
});

const submit = (): void => {
    form.put(props.urls.update, { preserveScroll: true });
};

// --- Coverage pickers -------------------------------------------------------

const countryFor = (id: number): CountryOption | undefined => props.countries.find((c) => c.id === id);

const stateName = (id: number): string => {
    const state = props.states.find((s) => s.id === id);
    return state ? `${state.name}${state.country ? ` (${state.country})` : ''}` : String(id);
};

const groupName = (id: number): string => props.customerGroups.find((g) => g.id === id)?.name ?? String(id);

const availableCountries = computed(() =>
    props.countries.filter((c) => !form.countries.includes(c.id)).map((c) => ({ value: c.id, label: c.name, flag: c.iso2 })));

const availableStates = computed(() =>
    props.states.filter((s) => !form.states.includes(s.id)).map((s) => ({ value: s.id, label: `${s.name}${s.country ? ` (${s.country})` : ''}` })));

const availableGroups = computed(() =>
    props.customerGroups.filter((g) => !form.customer_groups.includes(g.id)).map((g) => ({ value: g.id, label: g.name })));

const addCountry = (id: string | number): void => {
    form.countries.push(Number(id));
};

const addState = (id: string | number): void => {
    form.states.push(Number(id));
};

const addGroup = (id: string | number): void => {
    form.customer_groups.push(Number(id));
};

const newPostcode = ref<{ country_id: number | null; postcode: string }>({ country_id: null, postcode: '' });

const countryOptions = computed(() => props.countries.map((c) => ({ value: c.id, label: c.name, flag: c.iso2 })));

const addPostcode = (): void => {
    if (!newPostcode.value.country_id || !newPostcode.value.postcode.trim()) return;

    form.postcodes.push({
        country_id: newPostcode.value.country_id,
        postcode: newPostcode.value.postcode.trim(),
    });
    newPostcode.value.postcode = '';
};

// --- Rates ------------------------------------------------------------------

const addRate = (): void => {
    const nextPriority = form.rates.reduce((max, rate) => Math.max(max, rate.priority), 0) + 1;
    const amounts: Record<number, number> = {};
    props.taxClasses.forEach((taxClass) => {
        amounts[taxClass.id] = 0;
    });

    form.rates.push({ id: null, name: t('tax_zones.new_rate_name'), priority: Math.min(nextPriority, 255), amounts });
};

const removeRate = (index: number): void => {
    form.rates.splice(index, 1);
};

const ratesGridStyle = computed(() => ({
    gridTemplateColumns: `minmax(0, 1.2fr) 90px repeat(${props.taxClasses.length}, 110px) 36px`,
}));

// The default zone cannot be deleted or un-defaulted; promote another zone instead.
const deleteBlockedReason = computed<string>(() =>
    (props.taxZone.default ? t('tax_zones.delete_blocked_default') : ''));

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('tax_zones.edit_title', { name: taxZone.name })" :breadcrumbs="breadcrumbs" wide>
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('tax_zones.section_details')">
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('tax_zones.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel>{{ t('tax_zones.field_type') }}</FieldLabel>
                    <Select v-model="form.zone_type">
                        <option value="country">{{ t('tax_zones.type_country') }}</option>
                        <option value="state">{{ t('tax_zones.type_state') }}</option>
                        <option value="postcode">{{ t('tax_zones.type_postcode') }}</option>
                    </Select>
                </div>
                <div class="sm:col-span-2 flex flex-col gap-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <Toggle :on="form.active" @toggle="form.active = !form.active" />
                        <div>
                            <div class="text-[12.5px] text-ink-900 font-medium">{{ t('common.active') }}</div>
                            <div class="text-[11px] text-ink-500">{{ t('tax_zones.active_hint') }}</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3" :class="taxZone.default ? 'cursor-not-allowed' : 'cursor-pointer'">
                        <Toggle :on="form.default" :disabled="taxZone.default" @toggle="form.default = !form.default" />
                        <div>
                            <div class="text-[12.5px] text-ink-900 font-medium">{{ t('tax_zones.default_zone') }}</div>
                            <div class="text-[11px] text-ink-500">
                                {{ taxZone.default ? t('tax_zones.default_locked_hint') : t('tax_zones.default_zone_hint') }}
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </Section>

        <!-- Coverage: which of the three lists is live follows the zone type. -->
        <Section v-if="form.zone_type === 'country'" :title="t('tax_zones.section_countries', { count: form.countries.length })">
            <template #desc>{{ t('tax_zones.countries_desc') }}</template>

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
                        :aria-label="t('tax_zones.remove_country')"
                        @click="form.countries = form.countries.filter((c) => c !== id)"
                    >
                        ×
                    </button>
                </span>
                <span v-if="!form.countries.length" class="text-xs text-ink-500">{{ t('tax_zones.no_countries') }}</span>
            </div>

            <div class="max-w-[340px]">
                <Combobox :options="availableCountries" :placeholder="t('tax_zones.add_country_placeholder')" @change="addCountry" />
            </div>
        </Section>

        <Section v-else-if="form.zone_type === 'state'" :title="t('tax_zones.section_states', { count: form.states.length })">
            <template #desc>{{ t('tax_zones.states_desc') }}</template>

            <div class="flex flex-wrap gap-1.5 mb-3">
                <span
                    v-for="id in form.states"
                    :key="id"
                    class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-surface-2 border border-line text-xs text-ink-900"
                >
                    {{ stateName(id) }}
                    <button
                        type="button"
                        class="w-4 h-4 inline-flex items-center justify-center rounded-full text-ink-500 hover:text-danger"
                        :aria-label="t('tax_zones.remove_state')"
                        @click="form.states = form.states.filter((s) => s !== id)"
                    >
                        ×
                    </button>
                </span>
                <span v-if="!form.states.length" class="text-xs text-ink-500">{{ t('tax_zones.no_states') }}</span>
            </div>

            <div class="max-w-[340px]">
                <Combobox :options="availableStates" :placeholder="t('tax_zones.add_state_placeholder')" @change="addState" />
            </div>
        </Section>

        <Section v-else :title="t('tax_zones.section_postcodes', { count: form.postcodes.length })">
            <template #desc>{{ t('tax_zones.postcodes_desc') }}</template>

            <div v-if="form.postcodes.length" class="flex flex-col gap-1.5 mb-3">
                <div
                    v-for="(row, index) in form.postcodes"
                    :key="`${row.country_id}-${row.postcode}`"
                    class="flex items-center gap-3 px-3 py-1.5 rounded-md border border-line bg-surface text-xs"
                >
                    <span class="inline-flex items-center gap-1.5 text-ink-900">
                        <Flag v-if="countryFor(row.country_id)?.iso2" :code="countryFor(row.country_id)!.iso2" class="text-[13px]" />
                        {{ countryFor(row.country_id)?.name ?? row.country_id }}
                    </span>
                    <span class="font-mono text-ink-700">{{ row.postcode }}</span>
                    <button
                        type="button"
                        class="ml-auto w-5 h-5 inline-flex items-center justify-center rounded-full text-ink-500 hover:text-danger"
                        :aria-label="t('tax_zones.remove_postcode')"
                        @click="form.postcodes.splice(index, 1)"
                    >
                        ×
                    </button>
                </div>
            </div>
            <div v-else class="mb-3 text-xs text-ink-500">{{ t('tax_zones.no_postcodes') }}</div>

            <div class="grid sm:grid-cols-[280px_180px_auto] gap-2">
                <Combobox v-model="newPostcode.country_id" :options="countryOptions" :placeholder="t('tax_zones.add_country_placeholder')" />
                <TextInput v-model="newPostcode.postcode" mono :placeholder="t('tax_zones.postcode_placeholder')" @keyup.enter="addPostcode" />
                <Button icon="plus" @click="addPostcode">{{ t('tax_zones.add_postcode') }}</Button>
            </div>
        </Section>

        <Section :title="t('tax_zones.section_customer_groups', { count: form.customer_groups.length })">
            <template #desc>{{ t('tax_zones.customer_groups_desc') }}</template>

            <div class="flex flex-wrap gap-1.5 mb-3">
                <span
                    v-for="id in form.customer_groups"
                    :key="id"
                    class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full bg-surface-2 border border-line text-xs text-ink-900"
                >
                    {{ groupName(id) }}
                    <button
                        type="button"
                        class="w-4 h-4 inline-flex items-center justify-center rounded-full text-ink-500 hover:text-danger"
                        :aria-label="t('tax_zones.remove_customer_group')"
                        @click="form.customer_groups = form.customer_groups.filter((g) => g !== id)"
                    >
                        ×
                    </button>
                </span>
                <span v-if="!form.customer_groups.length" class="text-xs text-ink-500">{{ t('tax_zones.all_customer_groups') }}</span>
            </div>

            <div class="max-w-[340px]">
                <Combobox :options="availableGroups" :placeholder="t('tax_zones.add_customer_group_placeholder')" @change="addGroup" />
            </div>
        </Section>

        <Section :title="t('tax_zones.section_rates', { count: form.rates.length })">
            <template #desc>{{ t('tax_zones.rates_desc') }}</template>
            <template #actions>
                <Button variant="primary" size="sm" icon="plus" @click="addRate">{{ t('tax_zones.add_rate') }}</Button>
            </template>

            <div v-if="!form.rates.length" class="px-6 py-10 text-center text-xs text-ink-500 border border-dashed border-line rounded-md">
                {{ t('tax_zones.no_rates') }}
            </div>

            <div v-else class="bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
                <div
                    class="grid items-center gap-3 px-3.5 py-2.5 bg-surface-2 border-b border-line text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium min-w-max"
                    :style="ratesGridStyle"
                >
                    <div>{{ t('tax_zones.column_rate_name') }}</div>
                    <div class="text-right">{{ t('tax_zones.column_priority') }}</div>
                    <div v-for="taxClass in taxClasses" :key="taxClass.id" class="text-right truncate">{{ taxClass.name }}</div>
                    <div />
                </div>
                <div
                    v-for="(rate, index) in form.rates"
                    :key="rate.id ?? `new-${index}`"
                    class="grid items-center gap-3 px-3.5 py-2 border-b border-line last:border-b-0 min-w-max"
                    :style="ratesGridStyle"
                >
                    <TextInput v-model="rate.name" />
                    <TextInput v-model.number="rate.priority" type="number" min="1" max="255" />
                    <div v-for="taxClass in taxClasses" :key="taxClass.id">
                        <TextInput v-model.number="rate.amounts[taxClass.id]" type="number" step="0.001" min="0" max="100" />
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        icon="trash"
                        :aria-label="t('tax_zones.remove_rate')"
                        class="!w-[26px] !h-[26px] text-ink-700 hover:text-danger"
                        @click="removeRate(index)"
                    />
                </div>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('tax_zones.confirm_delete_title')"
        :description="t('tax_zones.confirm_delete_body', { name: taxZone.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
