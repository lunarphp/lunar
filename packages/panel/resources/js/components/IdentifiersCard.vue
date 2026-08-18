<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FieldLabel from './FieldLabel.vue';
import Section from './Section.vue';
import TextInput from './TextInput.vue';

const props = defineProps<{
    values: Record<string, unknown>;
    fieldPrefix?: string;
    errors?: Record<string, string>;
}>();

const { t } = useI18n();

const key = (field: string): string => `${props.fieldPrefix ?? ''}${field}`;

interface IdentifierField {
    name: string;
    label: string;
    hint: string;
}

const fields = computed<IdentifierField[]>(() => [
    { name: 'sku', label: t('products.field_sku'), hint: t('products.field_sku_hint') },
    { name: 'gtin', label: t('products.field_gtin'), hint: t('products.field_gtin_hint') },
    { name: 'mpn', label: t('products.field_mpn'), hint: t('products.field_mpn_hint') },
    { name: 'ean', label: t('products.field_ean'), hint: t('products.field_ean_hint') },
]);

const write = (field: string, value: string): void => {
    // eslint-disable-next-line vue/no-mutating-props
    props.values[key(field)] = value === '' ? null : value;
};
</script>

<template>
    <Section :title="t('products.section_identifiers')">
        <template #desc>{{ t('products.section_identifiers_description') }}</template>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-3.5 gap-y-3">
            <div v-for="field in fields" :key="field.name">
                <FieldLabel :hint="field.hint">{{ field.label }}</FieldLabel>
                <TextInput
                    :model-value="String(values[key(field.name)] ?? '')"
                    mono
                    :invalid="!!(errors ?? {})[key(field.name)]"
                    @update:model-value="(value) => write(field.name, String(value))"
                />
                <div v-if="(errors ?? {})[key(field.name)]" class="mt-1 text-[11px] text-danger">{{ (errors ?? {})[key(field.name)] }}</div>
            </div>
        </div>
    </Section>
</template>
