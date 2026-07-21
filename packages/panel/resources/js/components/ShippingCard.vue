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

interface DimensionField {
    name: string;
    label: string;
    units: string[];
    fallback: string;
}

const dimensionFields = computed<DimensionField[]>(() => [
    { name: 'weight', label: t('products.field_weight'), units: props.measurements.weight, fallback: 'kg' },
    { name: 'length', label: t('products.field_length'), units: props.measurements.length, fallback: 'mm' },
    { name: 'width', label: t('products.field_width'), units: props.measurements.length, fallback: 'mm' },
    { name: 'height', label: t('products.field_height'), units: props.measurements.length, fallback: 'mm' },
]);
</script>

<template>
    <Section :title="t('products.section_shipping')">
        <template #desc>{{ t('products.section_shipping_description') }}</template>

        <div class="flex items-center gap-2.5 mb-4">
            <Toggle :on="shippable" @toggle="toggleShippable" />
            <span class="text-[12.5px] text-ink-900">{{ t('products.field_shippable') }}</span>
        </div>

        <div v-if="shippable" class="grid grid-cols-2 lg:grid-cols-4 gap-x-3.5 gap-y-3">
            <div v-for="dimension in dimensionFields" :key="dimension.name">
                <FieldLabel>{{ dimension.label }}</FieldLabel>
                <div class="flex gap-1.5">
                    <TextInput
                        :model-value="String(read(`${dimension.name}_value`) ?? '')"
                        type="number"
                        min="0"
                        step="any"
                        :invalid="!!(errors ?? {})[key(`${dimension.name}_value`)]"
                        :aria-label="dimension.label"
                        @update:model-value="(value) => write(`${dimension.name}_value`, value === '' ? null : Number(value))"
                    />
                    <div class="w-[76px] shrink-0">
                        <Select
                            :model-value="String(read(`${dimension.name}_unit`) ?? dimension.fallback)"
                            :aria-label="`${dimension.label} ${t('products.field_unit')}`"
                            @update:model-value="(value) => write(`${dimension.name}_unit`, value)"
                        >
                            <option v-for="unit in dimension.units" :key="unit" :value="unit">{{ unit }}</option>
                        </Select>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-[11.5px] text-ink-500">{{ t('products.field_shippable_off_hint') }}</div>
    </Section>
</template>
