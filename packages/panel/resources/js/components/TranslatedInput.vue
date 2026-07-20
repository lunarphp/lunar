<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TextInput from './TextInput.vue';
import Textarea from './Textarea.vue';
import RichTextEditor from './RichTextEditor.vue';

export interface LanguageOption {
    id: number;
    code: string;
    name: string | null;
    default: boolean;
}

const props = withDefaults(
    defineProps<{
        modelValue: Record<string, string>;
        languages: LanguageOption[];
        kind?: 'text' | 'textarea' | 'richtext';
        id?: string;
        invalid?: boolean;
        placeholder?: string;
    }>(),
    { kind: 'text' },
);

const emit = defineEmits<{ 'update:modelValue': [value: Record<string, string>] }>();

const { t } = useI18n();

const activeCode = ref(props.languages.find((language) => language.default)?.code ?? props.languages[0]?.code ?? 'en');

const currentValue = computed(() => props.modelValue[activeCode.value] ?? '');

// Empty values are dropped from the map rather than stored as '' so the map
// stays normalised and draft dirty-tracking compares like with like.
const update = (value: string): void => {
    const next = { ...props.modelValue };

    if (value === '') {
        delete next[activeCode.value];
    } else {
        next[activeCode.value] = value;
    }

    emit('update:modelValue', next);
};

// The language picker is a compact select so it scales past a handful of locales.
const showPicker = computed(() => props.languages.length > 1);
</script>

<template>
    <div>
        <!-- Text inputs carry the picker as an input-group prefix so the box
             stays a single row and top-aligns with sibling fields. -->
        <TextInput
            v-if="kind === 'text'"
            :id="id"
            :model-value="currentValue"
            :invalid="invalid"
            :placeholder="placeholder"
            @update:model-value="update"
        >
            <template v-if="showPicker" #prefix>
                <select
                    v-model="activeCode"
                    class="bg-transparent text-[10.5px] font-mono uppercase tracking-wide text-ink-700 cursor-pointer focus:outline-none"
                    :aria-label="t('common.language')"
                >
                    <option v-for="language in languages" :key="language.code" :value="language.code" :title="language.name ?? language.code">
                        {{ language.code.toUpperCase() }}
                    </option>
                </select>
            </template>
        </TextInput>

        <template v-else>
            <div v-if="showPicker" class="flex mb-1.5">
                <select
                    v-model="activeCode"
                    class="h-[22px] px-1 border border-line-strong rounded-md bg-surface-2 text-[10.5px] font-mono uppercase tracking-wide text-ink-700 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                    :aria-label="t('common.language')"
                >
                    <option v-for="language in languages" :key="language.code" :value="language.code" :title="language.name ?? language.code">
                        {{ language.code.toUpperCase() }}
                    </option>
                </select>
            </div>

            <Textarea
                v-if="kind === 'textarea'"
                :id="id"
                :model-value="currentValue"
                :invalid="invalid"
                :placeholder="placeholder"
                :rows="2"
                @update:model-value="update"
            />
            <RichTextEditor
                v-else
                :model-value="currentValue"
                :invalid="invalid"
                @update:model-value="update"
            />
        </template>
    </div>
</template>
