<script setup lang="ts">
import { computed } from 'vue';
import { fromLocalInput, toLocalInput } from '../lib/datetime';
import TextInput from './TextInput.vue';

/**
 * A native `datetime-local` input whose v-model is an instant (an ISO 8601
 * string with an offset) rather than a zoneless value. Staff enter and see
 * their browser's local time; the model holds the matching UTC instant, or an
 * empty string when the input is cleared.
 */
const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        id?: string;
        invalid?: boolean;
    }>(),
    { modelValue: '' },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const local = computed({
    get: () => toLocalInput(props.modelValue),
    set: (value: string) => emit('update:modelValue', fromLocalInput(value)),
});
</script>

<template>
    <TextInput :id="id" v-model="local" type="datetime-local" :invalid="invalid" />
</template>
