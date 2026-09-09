<script setup lang="ts">
// Postcodes edited as one per line; the list the form holds is the split,
// trimmed set. The server strips inner spaces and deduplicates on save.
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Textarea } from '@lunarphp/panel';

const props = defineProps<{
    modelValue: string[];
    invalid?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string[]] }>();

const { t } = useI18n();

const text = computed({
    get: () => props.modelValue.join('\n'),
    set: (value: string) => {
        emit('update:modelValue', value.split('\n').map((line) => line.trim()).filter((line, index, all) => line !== '' || index === all.length - 1));
    },
});
</script>

<template>
    <Textarea v-model="text" :rows="8" mono :invalid="invalid" :placeholder="t('shipping::zones.postcodes_placeholder')" />
</template>
