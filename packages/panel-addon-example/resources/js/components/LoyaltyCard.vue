<script setup lang="ts">
import { FieldLabel, Select, useFormSlice } from '@lunarphp/panel';
import { useI18n } from 'vue-i18n';

// Binds to the LoyaltyTierSlice registered in ExampleSection::formExtensions().
// The key given here is the slice's key(); the panel resolves it to the
// `addon:example-addon:` namespace on the customer page's draft, so `tier`
// autosaves, restores, conflicts and commits alongside the customer's own
// fields without this component ever touching them.
const { t } = useI18n();

const slice = useFormSlice<{ tier: string | null }>('example-addon');

const tiers = ['bronze', 'silver', 'gold'] as const;
</script>

<template>
    <div class="rounded-lg border border-line bg-surface p-4 mt-6">
        <h2 class="text-sm font-semibold text-ink-900">{{ t('example-addon::example.loyalty_title') }}</h2>
        <p class="text-xs text-ink-500 mt-1 mb-3">{{ t('example-addon::example.loyalty_description') }}</p>

        <FieldLabel for="example-addon-loyalty-tier">{{ t('example-addon::example.loyalty_tier') }}</FieldLabel>
        <Select id="example-addon-loyalty-tier" v-model="slice.values.tier" :invalid="!!slice.errors.value.tier">
            <option :value="null">{{ t('example-addon::example.loyalty_tier_none') }}</option>
            <option v-for="tier in tiers" :key="tier" :value="tier">
                {{ t(`example-addon::example.loyalty_tier_${tier}`) }}
            </option>
        </Select>
        <p v-if="slice.errors.value.tier" class="text-xs text-danger mt-1">{{ slice.errors.value.tier }}</p>
        <p v-else-if="slice.isDirty.value" class="text-xs text-ink-400 mt-1">{{ t('example-addon::example.loyalty_unsaved') }}</p>
    </div>
</template>
