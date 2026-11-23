<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Dialog from './Dialog.vue';
import Select from './Select.vue';
import TextInput from './TextInput.vue';
import type { DraftConflict } from '../lib/http';

/**
 * How the page's editor presents one draft key, so the dialog shows and
 * merges its values in the same form: `options` for a select or enum field
 * (each side shows its label and the manual merge is a select of them),
 * `format` for a structured value the dialog cannot render itself (such a
 * field stays a two-way choice).
 */
export interface ConflictField {
    options?: { value: string | number | null; label: string }[];
    format?: (value: unknown) => string;
}

const props = withDefaults(
    defineProps<{
        open?: boolean;
        conflicts?: DraftConflict[];
        busy?: boolean;
        /** The presentation of a conflicting key; keys without one render generically. */
        field?: (key: string) => ConflictField | undefined;
        /** The form's commit errors, so a refused resolution says why. */
        errors?: Record<string, string>;
    }>(),
    { open: false, conflicts: () => [], busy: false, field: () => undefined, errors: () => ({}) },
);

const emit = defineEmits<{
    'update:open': [value: boolean];
    resolve: [resolutions: Record<string, unknown>, rebase: Record<string, unknown>];
}>();

const { t } = useI18n();

interface Resolution {
    choice: 'mine' | 'theirs';
    value: unknown;
}

const resolutions = reactive<Record<string, Resolution>>({});

watch(
    () => props.conflicts,
    (conflicts) => {
        for (const key of Object.keys(resolutions)) {
            delete resolutions[key];
        }

        for (const conflict of conflicts) {
            resolutions[conflict.key] = { choice: 'mine', value: conflict.mine };
        }
    },
    { immediate: true, deep: true },
);

const choose = (conflict: DraftConflict, choice: 'mine' | 'theirs'): void => {
    resolutions[conflict.key] = {
        choice,
        value: choice === 'mine' ? conflict.mine : conflict.theirs,
    };
};

// Manual merging edits the chosen value in place; only scalar values get a
// text input, structured values (id sets) stay a two-way choice.
const isEditable = (conflict: DraftConflict): boolean =>
    !props.field(conflict.key)?.format
    && [conflict.mine, conflict.theirs].every(
        (value) => value === null || ['string', 'number', 'boolean'].includes(typeof value),
    );

const display = (conflict: DraftConflict, value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return t('drafts.empty');
    }

    const field = props.field(conflict.key);
    const option = field?.options?.find((candidate) => candidate.value === value);

    if (option) {
        return option.label;
    }

    if (field?.format) {
        return field.format(value) || t('drafts.empty');
    }

    if (Array.isArray(value)) {
        return value.every((item) => item === null || typeof item !== 'object') ? value.join(', ') : JSON.stringify(value);
    }

    if (typeof value === 'object') {
        const entries = Object.entries(value as Record<string, unknown>).filter(([, text]) => text !== null && text !== '');

        // A translated field: its text per language, the language shown only
        // when there is more than one.
        if (entries.every(([, text]) => typeof text === 'string')) {
            return entries.length === 0
                ? t('drafts.empty')
                : entries.length === 1
                    ? String(entries[0][1])
                    : entries.map(([language, text]) => `${language}: ${text}`).join(' · ');
        }

        return JSON.stringify(value);
    }

    return String(value);
};

const editableValue = (key: string): string => {
    const value = resolutions[key]?.value;

    return value === null || value === undefined ? '' : String(value);
};

// The input is text; a merged value keeps the type the field's values have,
// so a number or boolean field is not committed as a string.
const typedValue = (conflict: DraftConflict, text: string): unknown => {
    const sample = [conflict.mine, conflict.theirs, conflict.base].find((value) => value !== null && value !== undefined);

    if (typeof sample === 'number') {
        const parsed = Number(text);

        return text.trim() === '' ? null : Number.isFinite(parsed) ? parsed : text;
    }

    if (typeof sample === 'boolean') {
        return text.trim().toLowerCase() === 'true' ? true : text.trim().toLowerCase() === 'false' ? false : text;
    }

    return text;
};

const onManualEdit = (conflict: DraftConflict, value: string | number): void => {
    if (resolutions[conflict.key]) {
        resolutions[conflict.key].value = typedValue(conflict, String(value));
    }
};

// The commit error for a conflicting key, or for one of its nested fields
// (`price:USD.0.price`).
const errorFor = (conflict: DraftConflict): string | undefined =>
    props.errors[conflict.key]
    ?? Object.entries(props.errors).find(([key]) => key.startsWith(`${conflict.key}.`))?.[1];

// Errors on keys the dialog does not list still refused the save, so they
// show here too rather than leaving Apply silently doing nothing.
const otherErrors = computed(() =>
    Object.entries(props.errors)
        .filter(([key]) => !props.conflicts.some((conflict) => key === conflict.key || key.startsWith(`${conflict.key}.`)))
        .map(([, message]) => message));

const confirm = (): void => {
    const resolved: Record<string, unknown> = {};
    const rebase: Record<string, unknown> = {};

    for (const conflict of props.conflicts) {
        resolved[conflict.key] = resolutions[conflict.key]?.value;
        rebase[conflict.key] = conflict.theirs;
    }

    emit('resolve', resolved, rebase);
};
</script>

<template>
    <Dialog :open="open" :title="t('drafts.conflict_title')" :description="t('drafts.conflict_description')" size="lg" @update:open="$emit('update:open', $event)">
        <div class="flex flex-col gap-4">
            <div v-if="otherErrors.length" role="alert" class="text-[11px] text-danger">
                <div v-for="(message, index) in otherErrors" :key="index">{{ message }}</div>
            </div>
            <div v-for="conflict in conflicts" :key="conflict.key" class="rounded-lg border border-line overflow-hidden">
                <div class="px-3.5 py-2 bg-surface-2 border-b border-line text-[12px] font-semibold text-ink-900">
                    {{ conflict.label }}
                </div>
                <div class="p-3.5 flex flex-col gap-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <button
                            type="button"
                            class="text-left rounded-md border px-3 py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                            :class="resolutions[conflict.key]?.choice === 'mine' ? 'border-sage bg-sage/5' : 'border-line hover:border-line-strong'"
                            :aria-pressed="resolutions[conflict.key]?.choice === 'mine'"
                            @click="choose(conflict, 'mine')"
                        >
                            <span class="block text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium mb-0.5">{{ t('drafts.your_value') }}</span>
                            <span class="block text-[12.5px] text-ink-900 break-words" :class="{ italic: display(conflict, conflict.mine) === t('drafts.empty') }">{{ display(conflict, conflict.mine) }}</span>
                        </button>
                        <button
                            type="button"
                            class="text-left rounded-md border px-3 py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                            :class="resolutions[conflict.key]?.choice === 'theirs' ? 'border-sage bg-sage/5' : 'border-line hover:border-line-strong'"
                            :aria-pressed="resolutions[conflict.key]?.choice === 'theirs'"
                            @click="choose(conflict, 'theirs')"
                        >
                            <span class="block text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium mb-0.5">{{ t('drafts.current_value') }}</span>
                            <span class="block text-[12.5px] text-ink-900 break-words" :class="{ italic: display(conflict, conflict.theirs) === t('drafts.empty') }">{{ display(conflict, conflict.theirs) }}</span>
                        </button>
                    </div>
                    <Select
                        v-if="field(conflict.key)?.options"
                        :model-value="resolutions[conflict.key]?.value as string | number | null"
                        :aria-label="conflict.label"
                        :invalid="!!errorFor(conflict)"
                        @update:model-value="resolutions[conflict.key].value = $event"
                    >
                        <option v-for="option in field(conflict.key)?.options" :key="String(option.value)" :value="option.value">{{ option.label }}</option>
                    </Select>
                    <TextInput
                        v-else-if="isEditable(conflict)"
                        :model-value="editableValue(conflict.key)"
                        :aria-label="conflict.label"
                        :invalid="!!errorFor(conflict)"
                        @update:model-value="onManualEdit(conflict, $event)"
                    />
                    <div v-if="errorFor(conflict)" role="alert" class="text-[11px] text-danger">{{ errorFor(conflict) }}</div>
                    <p class="m-0 text-[11px] text-ink-500">{{ t('drafts.base_note', { value: display(conflict, conflict.base) }) }}</p>
                </div>
            </div>
        </div>
        <template #footer>
            <Button type="button" @click="$emit('update:open', false)">{{ t('common.cancel') }}</Button>
            <Button type="button" variant="primary" :disabled="busy" @click="confirm">{{ t('drafts.apply') }}</Button>
        </template>
    </Dialog>
</template>
