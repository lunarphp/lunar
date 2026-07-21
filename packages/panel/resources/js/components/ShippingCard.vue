<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FieldLabel from './FieldLabel.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import TextInput from './TextInput.vue';
import Toggle from './Toggle.vue';

const props = defineProps<{
    values: Record<string, unknown>;
    fieldPrefix?: string;
    measurements: { length: string[]; weight: string[] };
    errors?: Record<string, string>;
}>();

const { t } = useI18n();

const key = (field: string): string => `${props.fieldPrefix ?? ''}${field}`;

const read = (field: string): unknown => props.values[key(field)];

const write = (field: string, value: unknown): void => {
    // eslint-disable-next-line vue/no-mutating-props
    props.values[key(field)] = value;
};

const shippable = computed(() => Boolean(read('shippable')));

const toggleShippable = (): void => write('shippable', !shippable.value);

// Length, width and height share a single unit so their product is a coherent
// volume; the select mirrors the chosen unit onto all three stored fields.
const lengthUnits = computed(() => props.measurements.length);
const weightUnits = computed(() => props.measurements.weight);

const dimensionUnit = computed<string>(() =>
    String(read('length_unit') ?? lengthUnits.value[0] ?? 'mm'),
);

const setDimensionUnit = (unit: string | number | null): void => {
    const value = String(unit ?? '');

    write('length_unit', value);
    write('width_unit', value);
    write('height_unit', value);
};

const dimensions = ['length', 'width', 'height'] as const;

const numeric = (field: string): number => {
    const value = Number(read(`${field}_value`));

    return Number.isFinite(value) ? value : 0;
};

const readValue = (field: string): string => {
    const value = read(`${field}_value`);

    return value === null || value === undefined ? '' : String(value);
};

const writeValue = (field: string, value: string): void =>
    write(`${field}_value`, value === '' ? null : Number(value));

const dimensionLabel = (field: string): string => t(`products.field_${field}`);

// Auto-calculated for display only; shown as 0 until all three are present.
const volume = computed(() => numeric('length') * numeric('width') * numeric('height'));

const volumeDisplay = computed(() => String(Math.round(volume.value * 100) / 100));
</script>

<template>
    <Section :title="t('products.section_shipping')">
        <template #desc>{{ t('products.section_shipping_description') }}</template>

        <label class="flex items-center gap-3 p-3.5 border border-line rounded-lg bg-surface cursor-pointer mb-4">
            <Toggle :on="shippable" @toggle="toggleShippable" />
            <span class="flex flex-col">
                <span class="text-[13px] font-medium text-ink-900">{{ t('products.field_shippable') }}</span>
                <span class="text-[11.5px] text-ink-500">{{ t('products.field_shippable_hint') }}</span>
            </span>
        </label>

        <template v-if="shippable">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-3.5 gap-y-3 mb-3">
                <div>
                    <FieldLabel>{{ t('products.field_weight') }}</FieldLabel>
                    <div class="flex gap-1.5">
                        <TextInput
                            :model-value="readValue('weight')"
                            type="number"
                            min="0"
                            step="any"
                            :invalid="!!(errors ?? {})[key('weight_value')]"
                            :aria-label="t('products.field_weight')"
                            @update:model-value="(value) => writeValue('weight', value)"
                        />
                        <div class="w-[76px] shrink-0">
                            <Select
                                :model-value="String(read('weight_unit') ?? weightUnits[0] ?? 'kg')"
                                :aria-label="`${t('products.field_weight')} ${t('products.field_unit')}`"
                                @update:model-value="(value) => write('weight_unit', value)"
                            >
                                <option v-for="unit in weightUnits" :key="unit" :value="unit">{{ unit }}</option>
                            </Select>
                        </div>
                    </div>
                </div>
                <div>
                    <FieldLabel :hint="t('products.field_dimension_unit_hint')">{{ t('products.field_dimension_unit') }}</FieldLabel>
                    <Select
                        :model-value="dimensionUnit"
                        :aria-label="t('products.field_dimension_unit')"
                        @update:model-value="setDimensionUnit"
                    >
                        <option v-for="unit in lengthUnits" :key="unit" :value="unit">{{ unit }}</option>
                    </Select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-3.5 gap-y-3">
                <div v-for="field in dimensions" :key="field">
                    <FieldLabel>{{ dimensionLabel(field) }}</FieldLabel>
                    <TextInput
                        :model-value="readValue(field)"
                        type="number"
                        min="0"
                        step="any"
                        :invalid="!!(errors ?? {})[key(`${field}_value`)]"
                        :aria-label="dimensionLabel(field)"
                        @update:model-value="(value) => writeValue(field, value)"
                    >
                        <template #suffix>{{ dimensionUnit }}</template>
                    </TextInput>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between gap-3 px-3 py-2.5 border border-line rounded-md bg-surface-2">
                <span class="flex items-baseline gap-2">
                    <span class="text-[12px] text-ink-500">{{ t('products.field_volume') }}</span>
                    <span class="text-[13px] font-semibold text-ink-900">{{ volumeDisplay }}</span>
                    <span class="text-[11px] font-mono text-ink-500">{{ t('products.field_volume_unit', { unit: dimensionUnit }) }}</span>
                </span>
                <span class="text-[11px] text-ink-400">{{ t('products.field_volume_hint') }}</span>
            </div>
        </template>
        <div v-else class="text-[11.5px] text-ink-500">{{ t('products.field_shippable_off_hint') }}</div>
    </Section>
</template>
