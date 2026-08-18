<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import FieldLabel from './FieldLabel.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import TextInput from './TextInput.vue';

const props = defineProps<{
    values: Record<string, unknown>;
    fieldPrefix?: string;
    taxClasses: { id: number; name: string }[];
    errors?: Record<string, string>;
}>();

const { t } = useI18n();

const key = (field: string): string => `${props.fieldPrefix ?? ''}${field}`;

const write = (field: string, value: unknown): void => {
    // eslint-disable-next-line vue/no-mutating-props
    props.values[key(field)] = value;
};
</script>

<template>
    <Section :title="t('products.section_tax')">
        <template #desc>{{ t('products.section_tax_description') }}</template>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-3.5 gap-y-3">
            <div>
                <FieldLabel :for="key('tax_class_id')" required>{{ t('products.field_tax_class') }}</FieldLabel>
                <Select
                    :id="key('tax_class_id')"
                    :model-value="(values[key('tax_class_id')] as number | null) ?? ''"
                    :invalid="!!(errors ?? {})[key('tax_class_id')]"
                    @update:model-value="(value) => write('tax_class_id', value === '' ? null : Number(value))"
                >
                    <option v-for="taxClass in taxClasses" :key="taxClass.id" :value="taxClass.id">{{ taxClass.name }}</option>
                </Select>
            </div>
            <div>
                <FieldLabel :for="key('tax_ref')" :hint="t('products.field_tax_ref_hint')">{{ t('products.field_tax_ref') }}</FieldLabel>
                <TextInput
                    :id="key('tax_ref')"
                    :model-value="String(values[key('tax_ref')] ?? '')"
                    mono
                    @update:model-value="(value) => write('tax_ref', String(value) === '' ? null : String(value))"
                />
            </div>
        </div>
    </Section>
</template>
