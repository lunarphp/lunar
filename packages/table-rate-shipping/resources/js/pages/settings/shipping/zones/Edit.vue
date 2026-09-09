<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Button, Combobox, ConfirmDialog, FieldLabel, Section, Select, SettingsShell, StatusBadge, TextInput } from '@lunarphp/panel';
import ChipList, { type Chip } from '../../../../components/ChipList.vue';
import PostcodeListInput from '../../../../components/PostcodeListInput.vue';
import RateSlideout, { type MethodOption, type RateRow } from '../../../../components/RateSlideout.vue';
import { noLayout } from '../../../../lib/layout';
import type { CurrencyOption, NamedOption } from '../../../../lib/types';

defineOptions({ layout: noLayout });

type CountryOption = { id: number; name: string; iso2: string | null };
type StateOption = { id: number; name: string; code: string; country_id: number | null };

const props = defineProps<{
    zone: { id: number; name: string; type: string };
    coverage: { countries: number[]; states: number[]; postcodes: string[] };
    exclusionLists: number[];
    rates: RateRow[];
    methods: MethodOption[];
    currencies: CurrencyOption[];
    customerGroups: NamedOption[];
    allExclusionLists: NamedOption[];
    countries: CountryOption[];
    states: StateOption[];
    pricesIncludeTax: boolean;
    urls: { update: string; destroy: string; index: string; ratesStore: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed(() => [
    { label: t('nav.settings') },
    { label: t('shipping::zones.title'), href: props.urls.index },
    { label: props.zone.name, current: true },
]);

const form = useForm({
    name: props.zone.name,
    type: props.zone.type,
    countries: [...props.coverage.countries],
    states: [...props.coverage.states],
    postcodes: [...props.coverage.postcodes],
    exclusion_lists: [...props.exclusionLists],
});

const submit = (): void => {
    form.put(props.urls.update, { preserveScroll: true });
};

// --- Coverage ---------------------------------------------------------------

const countryFor = (id: number): CountryOption | undefined => props.countries.find((country) => country.id === id);

const countryOptions = computed(() => props.countries.map((country) => ({ value: country.id, label: country.name, flag: country.iso2 })));

const availableCountries = computed(() => countryOptions.value.filter((option) => !form.countries.includes(Number(option.value))));

const countryChips = computed<Chip[]>(() => form.countries.map((id) => ({ id, label: countryFor(id)?.name ?? String(id), flag: countryFor(id)?.iso2 })));

// State and postcode zones carry one parent country under `countries`.
const parentCountry = computed<number | null>({
    get: () => form.countries[0] ?? null,
    set: (value) => {
        form.countries = value ? [Number(value)] : [];
        form.states = [];
    },
});

const stateName = (id: number): string => props.states.find((state) => state.id === id)?.name ?? String(id);

const availableStates = computed(() =>
    props.states
        .filter((state) => state.country_id === parentCountry.value && !form.states.includes(state.id))
        .map((state) => ({ value: state.id, label: state.name })));

const stateChips = computed<Chip[]>(() => form.states.map((id) => ({ id, label: stateName(id) })));

// --- Exclusion lists --------------------------------------------------------

const listName = (id: number): string => props.allExclusionLists.find((list) => list.id === id)?.name ?? String(id);

const availableLists = computed(() =>
    props.allExclusionLists.filter((list) => !form.exclusion_lists.includes(list.id)).map((list) => ({ value: list.id, label: list.name })));

const listChips = computed<Chip[]>(() => form.exclusion_lists.map((id) => ({ id, label: listName(id) })));

// --- Rates ------------------------------------------------------------------

const rateOpen = ref(false);
const editingRate = ref<RateRow | null>(null);

const openRate = (rate: RateRow | null): void => {
    editingRate.value = rate;
    rateOpen.value = true;
};

const toggleRate = (rate: RateRow): void => {
    // Same payload the slideout sends, with only the flag flipped. Tiers are
    // plain objects but not typed with an index signature, hence the cast.
    router.put(rate.urls.update, {
        shipping_method_id: rate.shipping_method_id,
        enabled: !rate.enabled,
        base_prices: rate.base_prices,
        tiers: rate.tiers as unknown as Record<string, string | number | null>[],
    }, { preserveScroll: true });
};

const removingRate = ref<RateRow | null>(null);

const confirmRemoveRate = (): void => {
    if (removingRate.value) {
        router.delete(removingRate.value.urls.destroy, { preserveScroll: true });
    }
    removingRate.value = null;
};

const ratesGridStyle = { gridTemplateColumns: 'minmax(0, 1.4fr) 110px 120px 80px 200px' };

// --- Delete -----------------------------------------------------------------

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('shipping::zones.edit_title', { name: zone.name })" :breadcrumbs="breadcrumbs" wide>
        <template #actions>
            <Button variant="ghost" icon="trash" @click="deleting = true">{{ t('common.delete') }}</Button>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <div data-screen-label="Edit shipping zone">
            <Section :title="t('shipping::zones.section_details')">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <FieldLabel required>{{ t('shipping::zones.field_name') }}</FieldLabel>
                        <TextInput v-model="form.name" :invalid="!!form.errors.name" />
                        <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <FieldLabel>{{ t('shipping::zones.field_type') }}</FieldLabel>
                        <Select v-model="form.type">
                            <option value="unrestricted">{{ t('shipping::zones.type_unrestricted') }}</option>
                            <option value="countries">{{ t('shipping::zones.type_countries') }}</option>
                            <option value="states">{{ t('shipping::zones.type_states') }}</option>
                            <option value="postcodes">{{ t('shipping::zones.type_postcodes') }}</option>
                        </Select>
                    </div>
                </div>
                <div v-if="form.type === 'unrestricted'" class="mt-3 px-3 py-2 rounded-md bg-surface-2 border border-line text-xs text-ink-700">
                    {{ t('shipping::zones.unrestricted_hint') }}
                </div>
            </Section>

            <Section v-if="form.type === 'countries'" :title="t('shipping::zones.section_countries', { count: form.countries.length })">
                <template #desc>{{ t('shipping::zones.countries_desc') }}</template>
                <div class="mb-3">
                    <ChipList :chips="countryChips" :remove-label="t('shipping::zones.remove_country')" :empty-text="t('shipping::zones.no_countries')" @remove="form.countries = form.countries.filter((c) => c !== $event)" />
                </div>
                <div class="max-w-[340px]">
                    <Combobox :options="availableCountries" :placeholder="t('shipping::zones.add_country_placeholder')" @change="form.countries.push(Number($event))" />
                </div>
            </Section>

            <Section v-else-if="form.type === 'states'" :title="t('shipping::zones.section_states', { count: form.states.length })">
                <template #desc>{{ t('shipping::zones.states_desc') }}</template>
                <div class="max-w-[340px] mb-3">
                    <FieldLabel>{{ t('shipping::zones.field_country') }}</FieldLabel>
                    <Combobox v-model="parentCountry" :options="countryOptions" :placeholder="t('shipping::zones.add_country_placeholder')" />
                </div>
                <template v-if="parentCountry">
                    <div class="mb-3">
                        <ChipList :chips="stateChips" :remove-label="t('shipping::zones.remove_state')" :empty-text="t('shipping::zones.no_states')" @remove="form.states = form.states.filter((s) => s !== $event)" />
                    </div>
                    <div class="max-w-[340px]">
                        <Combobox :options="availableStates" :placeholder="t('shipping::zones.add_state_placeholder')" @change="form.states.push(Number($event))" />
                    </div>
                </template>
                <div v-else class="text-xs text-ink-500">{{ t('shipping::zones.choose_country_first') }}</div>
            </Section>

            <Section v-else-if="form.type === 'postcodes'" :title="t('shipping::zones.section_postcodes', { count: form.postcodes.filter((p) => p !== '').length })">
                <template #desc>{{ t('shipping::zones.postcodes_desc') }}</template>
                <div class="max-w-[340px] mb-3">
                    <FieldLabel>{{ t('shipping::zones.field_country') }}</FieldLabel>
                    <Combobox v-model="parentCountry" :options="countryOptions" :placeholder="t('shipping::zones.add_country_placeholder')" />
                </div>
                <div class="max-w-[340px]">
                    <PostcodeListInput v-model="form.postcodes" :invalid="!!form.errors.postcodes" />
                    <div v-if="form.errors.postcodes" class="mt-1 text-[11px] text-danger">{{ form.errors.postcodes }}</div>
                </div>
            </Section>

            <Section :title="t('shipping::zones.section_exclusion_lists', { count: form.exclusion_lists.length })">
                <template #desc>{{ t('shipping::zones.exclusion_lists_desc') }}</template>
                <div class="mb-3">
                    <ChipList :chips="listChips" :remove-label="t('shipping::zones.remove_exclusion_list')" :empty-text="t('shipping::zones.no_exclusion_lists')" @remove="form.exclusion_lists = form.exclusion_lists.filter((l) => l !== $event)" />
                </div>
                <div class="max-w-[340px]">
                    <Combobox :options="availableLists" :placeholder="t('shipping::zones.add_exclusion_list_placeholder')" @change="form.exclusion_lists.push(Number($event))" />
                </div>
            </Section>

            <Section :title="t('shipping::zones.section_rates', { count: rates.length })">
                <template #desc>{{ t('shipping::zones.rates_desc') }}</template>
                <template #actions>
                    <Button variant="primary" size="sm" icon="plus" :disabled="!methods.length" @click="openRate(null)">{{ t('shipping::zones.add_rate') }}</Button>
                </template>

                <div v-if="!methods.length" class="mb-3 text-xs text-warn">{{ t('shipping::zones.no_methods_hint') }}</div>

                <div v-if="!rates.length" class="px-6 py-10 text-center text-xs text-ink-500 border border-dashed border-line rounded-md">
                    {{ t('shipping::zones.no_rates') }}
                </div>

                <div v-else class="bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
                    <div class="grid items-center gap-3 px-3.5 py-2.5 bg-surface-2 border-b border-line text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium min-w-max" :style="ratesGridStyle">
                        <div>{{ t('shipping::zones.column_method') }}</div>
                        <div>{{ t('shipping::zones.column_status') }}</div>
                        <div class="text-right">{{ t('shipping::zones.column_base_price') }}</div>
                        <div class="text-right">{{ t('shipping::zones.column_tiers') }}</div>
                        <div />
                    </div>
                    <div v-for="rate in rates" :key="rate.id" class="grid items-center gap-3 px-3.5 py-2 border-b border-line last:border-b-0 min-w-max" :style="ratesGridStyle">
                        <button type="button" class="text-left text-[12.5px] text-ink-900 font-medium hover:underline underline-offset-2" @click="openRate(rate)">
                            {{ rate.method_name }}
                        </button>
                        <StatusBadge :tone="rate.enabled ? 'sage' : 'archived'" size="sm" dot>
                            {{ rate.enabled ? t('shipping::zones.rate_enabled') : t('shipping::zones.rate_disabled') }}
                        </StatusBadge>
                        <div class="text-right text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ rate.base_price ?? '-' }}</div>
                        <div class="text-right text-xs text-ink-700 [font-variant-numeric:tabular-nums]">{{ rate.tiers_count }}</div>
                        <div class="flex justify-end gap-1">
                            <Button size="sm" variant="ghost" @click="toggleRate(rate)">
                                {{ rate.enabled ? t('shipping::zones.disable_rate') : t('shipping::zones.enable_rate') }}
                            </Button>
                            <Button size="sm" variant="ghost" icon="edit" :aria-label="t('shipping::zones.edit_rate')" @click="openRate(rate)" />
                            <Button size="sm" variant="ghost" icon="trash" :aria-label="t('shipping::zones.remove_rate')" class="text-ink-700 hover:text-danger" @click="removingRate = rate" />
                        </div>
                    </div>
                </div>
            </Section>
        </div>
    </SettingsShell>

    <RateSlideout
        v-model:open="rateOpen"
        :rate="editingRate"
        :store-url="urls.ratesStore"
        :methods="methods"
        :currencies="currencies"
        :customer-groups="customerGroups"
        :prices-include-tax="pricesIncludeTax"
    />

    <ConfirmDialog
        :open="removingRate !== null"
        :title="t('shipping::zones.confirm_delete_rate_title')"
        :description="t('shipping::zones.confirm_delete_rate_body', { method: removingRate?.method_name ?? '' })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @update:open="removingRate = $event ? removingRate : null"
        @confirm="confirmRemoveRate"
    />

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('shipping::zones.confirm_delete_title')"
        :description="t('shipping::zones.confirm_delete_body', { name: zone.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
