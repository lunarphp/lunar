<script setup lang="ts">
import { Icon, TextInput } from '@lunarphp/panel';
import { ref } from 'vue';

const props = defineProps<{
    modelValue: string[];
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string[]];
}>();

const draft = ref('');

function add(): void {
    const value = draft.value.trim();

    if (value !== '' && !props.modelValue.includes(value)) {
        emit('update:modelValue', [...props.modelValue, value]);
    }

    draft.value = '';
}

function remove(token: string): void {
    emit(
        'update:modelValue',
        props.modelValue.filter((t) => t !== token),
    );
}
</script>

<template>
    <div>
        <div v-if="modelValue.length" class="mb-2 flex flex-wrap gap-1">
            <span
                v-for="token in modelValue"
                :key="token"
                class="bg-line text-ink-900 inline-flex items-center gap-1 rounded px-2 py-0.5 text-[13px]"
            >
                {{ token }}
                <button
                    type="button"
                    class="text-ink-500 hover:text-ink-900"
                    @click="remove(token)"
                >
                    <Icon name="x" class="h-3 w-3" />
                </button>
            </span>
        </div>
        <TextInput
            v-model="draft"
            :placeholder="placeholder ?? 'Type and press Enter'"
            @keydown.enter.prevent="add"
            @keydown.,.prevent="add"
            @blur="add"
        />
    </div>
</template>
