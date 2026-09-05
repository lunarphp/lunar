<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FieldLabel from '../FieldLabel.vue';
import TextInput from '../TextInput.vue';

const { t } = useI18n();

const props = defineProps<{
    modelValue: Record<string, unknown>;
    currencies: { id: number; code: string; decimal_places: number; default: boolean }[];
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [Record<string, unknown>] }>();

// Merged rather than replaced: `data` also carries the shared conditions the
// Conditions block edits, and this form must not drop them.
const percentage = computed({
    get: () => props.modelValue.percentage as number | string,
    set: (value) => emit('update:modelValue', { ...props.modelValue, percentage: value }),
});
</script>

<template>
    <div class="max-w-[220px]">
        <FieldLabel for="discount-percentage" required>{{ t('discounts.field_percentage') }}</FieldLabel>
        <TextInput
            id="discount-percentage"
            v-model="percentage"
            type="number"
            min="0"
            max="100"
            step="0.01"
            :invalid="!!errors?.['data.percentage']"
        >
            <template #suffix>%</template>
        </TextInput>
        <div v-if="errors?.['data.percentage']" class="mt-1 text-[11px] text-danger">{{ errors['data.percentage'] }}</div>
        <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_percentage_hint') }}</div>
    </div>
</template>
