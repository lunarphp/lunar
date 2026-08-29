<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    modelValue: Record<string, unknown>;
    currencies: { id: number; code: string; decimal_places: number; default: boolean }[];
}>();

const emit = defineEmits<{ 'update:modelValue': [Record<string, unknown>] }>();

const text = ref(JSON.stringify(props.modelValue ?? {}, null, 2));
const invalid = ref(false);

// Re-seed only when the incoming value differs from what this editor produced,
// so a draft round-trip does not reformat the JSON out from under the cursor.
watch(() => props.modelValue, (value) => {
    try {
        if (JSON.stringify(JSON.parse(text.value)) !== JSON.stringify(value ?? {})) {
            text.value = JSON.stringify(value ?? {}, null, 2);
        }
    } catch {
        // Mid-edit and unparsable: leave what is being typed alone.
    }
});

watch(text, (value) => {
    try {
        const parsed = JSON.parse(value || '{}');
        invalid.value = false;
        emit('update:modelValue', parsed as Record<string, unknown>);
    } catch {
        invalid.value = true;
    }
});

const rows = computed(() => Math.min(20, Math.max(6, text.value.split('\n').length + 1)));
</script>

<template>
    <div>
        <div class="text-xs text-ink-500 leading-normal mb-2">{{ t('discounts.raw_data_description') }}</div>
        <textarea
            v-model="text"
            :rows="rows"
            spellcheck="false"
            class="w-full rounded-lg border bg-surface px-3 py-2 text-[12px] font-mono text-ink-900 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35"
            :class="invalid ? 'border-danger' : 'border-line'"
            :aria-invalid="invalid"
        />
        <div v-if="invalid" class="mt-1 text-[11px] text-danger">{{ t('discounts.raw_data_invalid') }}</div>
    </div>
</template>
