<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Checkbox from './Checkbox.vue';
import FieldLabel from './FieldLabel.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import TextInput from './TextInput.vue';

const props = defineProps<{
    values: Record<string, unknown>;
    fieldPrefix?: string;
    measurements: { length: string[]; weight: string[] };
    fulfilmentMethods?: { key: string; label: string; physical: boolean }[];
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

type MethodOption = { key: string; label: string; physical: boolean };

const methodOptions = computed<MethodOption[]>(() => props.fulfilmentMethods ?? []);

const physicalKeys = computed(() => methodOptions.value.filter((method) => method.physical).map((method) => method.key));

const otherKeys = computed(() => methodOptions.value.filter((method) => !method.physical).map((method) => method.key));

// A variant that ships uses the physical methods, and one that doesn't uses the others or none.
// Null on a shippable variant allows every physical method, so it shows them all ticked.
const tickedMethods = computed<string[]>(() => {
    const stored = read('fulfilment_methods') as string[] | null;

    if (shippable.value) {
        return stored ? physicalKeys.value.filter((key) => stored.includes(key)) : physicalKeys.value;
    }

    return stored ? otherKeys.value.filter((key) => stored.includes(key)) : [];
});

// An item either ships or it doesn't, so ticking a method of the other kind switches the variant
// over. Shippable follows the ticked methods, and ticking every physical method stores null again,
// which keeps a method registered later available.
const toggleMethod = (method: MethodOption, ticked: boolean): void => {
    const sameKind = method.physical === shippable.value;
    const keys = ticked
        ? sameKind ? [...tickedMethods.value, method.key] : [method.key]
        : tickedMethods.value.filter((key) => key !== method.key);

    const ships = keys.some((key) => physicalKeys.value.includes(key));
    const pool = ships ? physicalKeys.value : otherKeys.value;
    const methods = pool.filter((key) => keys.includes(key));

    write('shippable', ships);
    write('fulfilment_methods', methods.length === 0 || (ships && methods.length === pool.length) ? null : methods);
};

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
    <Section :title="t('products.section_fulfilment')">
        <template #desc>{{ t('products.section_fulfilment_description') }}</template>

        <div v-if="methodOptions.length" class="mb-4">
            <FieldLabel :hint="t('products.field_fulfilment_methods_hint')">{{ t('products.field_fulfilment_methods') }}</FieldLabel>
            <div class="flex flex-wrap gap-x-4 gap-y-2">
                <label
                    v-for="method in methodOptions"
                    :key="method.key"
                    class="inline-flex items-center gap-2 text-[12.5px] text-ink-700 select-none cursor-pointer"
                >
                    <Checkbox
                        :model-value="tickedMethods.includes(method.key)"
                        @update:model-value="(ticked: boolean) => toggleMethod(method, ticked)"
                    />
                    {{ method.label }}
                </label>
            </div>
            <div v-if="(errors ?? {})[key('fulfilment_methods')]" class="mt-1 text-[11px] text-danger">{{ (errors ?? {})[key('fulfilment_methods')] }}</div>
        </div>

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
        <div v-else-if="!tickedMethods.length" class="text-[11.5px] text-ink-500">{{ t('products.field_fulfilment_none_hint') }}</div>
    </Section>
</template>
