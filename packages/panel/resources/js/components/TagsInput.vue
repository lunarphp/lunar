<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';

// Chip editor over a plain string array: Enter or comma adds, click or
// Backspace removes. Values are uppercased to match how core's Tag model
// stores them, so the draft's dirty comparison stays honest.
const props = defineProps<{
    modelValue: string[];
    invalid?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string[]] }>();

const { t } = useI18n();

const input = ref('');

const commit = (): void => {
    const value = input.value.trim().replace(/,+$/, '').toUpperCase();

    input.value = '';

    if (!value || props.modelValue.includes(value)) {
        return;
    }

    emit('update:modelValue', [...props.modelValue, value].sort());
};

const remove = (tag: string): void => {
    emit('update:modelValue', props.modelValue.filter((value) => value !== tag));
};

const onKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        commit();

        return;
    }

    if (event.key === 'Backspace' && input.value === '' && props.modelValue.length) {
        remove(props.modelValue[props.modelValue.length - 1]);
    }
};
</script>

<template>
    <div>
        <div
            :class="[
                'flex flex-wrap gap-1.5 px-1.5 py-1.5 min-h-[34px] border rounded-md bg-surface items-center focus-within:border-sage focus-within:ring-3 focus-within:ring-sage/35',
                invalid ? 'border-danger' : 'border-line-strong',
            ]"
        >
            <span
                v-for="tag in modelValue"
                :key="tag"
                class="inline-flex items-center gap-1.5 h-[22px] pl-2 pr-1 border border-line bg-surface-2 rounded-full text-[11.5px] text-ink-900"
            >
                {{ tag }}
                <button
                    type="button"
                    class="w-4 h-4 rounded-full grid place-items-center text-ink-400 cursor-pointer hover:bg-line-strong hover:text-ink-700"
                    :aria-label="t('products.remove_tag', { tag })"
                    @click="remove(tag)"
                ><Icon name="x" cls="sm" /></button>
            </span>
            <input
                v-model="input"
                class="border-0 outline-none bg-transparent text-xs px-1 py-0.5 min-w-[60px] flex-1 text-ink-900 placeholder:text-ink-400"
                :placeholder="t('products.tags_placeholder')"
                @keydown="onKeydown"
                @blur="commit"
            >
        </div>
        <div class="text-[11.5px] text-ink-500 mt-1">{{ t('products.tags_hint') }}</div>
    </div>
</template>
