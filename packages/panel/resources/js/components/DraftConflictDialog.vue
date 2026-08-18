<script setup lang="ts">
import { reactive, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Dialog from './Dialog.vue';
import TextInput from './TextInput.vue';
import type { DraftConflict } from '../lib/http';

const props = withDefaults(
    defineProps<{
        open?: boolean;
        conflicts?: DraftConflict[];
        busy?: boolean;
    }>(),
    { open: false, conflicts: () => [], busy: false },
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
    [conflict.mine, conflict.theirs].every(
        (value) => value === null || ['string', 'number', 'boolean'].includes(typeof value),
    );

const display = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return t('drafts.empty');
    }

    return Array.isArray(value) ? value.join(', ') : String(value);
};

const editableValue = (key: string): string => {
    const value = resolutions[key]?.value;

    return value === null || value === undefined ? '' : String(value);
};

const onManualEdit = (key: string, value: string | number): void => {
    if (resolutions[key]) {
        resolutions[key].value = String(value);
    }
};

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
                            <span class="block text-[12.5px] text-ink-900 break-words" :class="{ italic: display(conflict.mine) === t('drafts.empty') }">{{ display(conflict.mine) }}</span>
                        </button>
                        <button
                            type="button"
                            class="text-left rounded-md border px-3 py-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                            :class="resolutions[conflict.key]?.choice === 'theirs' ? 'border-sage bg-sage/5' : 'border-line hover:border-line-strong'"
                            :aria-pressed="resolutions[conflict.key]?.choice === 'theirs'"
                            @click="choose(conflict, 'theirs')"
                        >
                            <span class="block text-[11px] uppercase tracking-[0.06em] text-ink-500 font-medium mb-0.5">{{ t('drafts.current_value') }}</span>
                            <span class="block text-[12.5px] text-ink-900 break-words" :class="{ italic: display(conflict.theirs) === t('drafts.empty') }">{{ display(conflict.theirs) }}</span>
                        </button>
                    </div>
                    <TextInput
                        v-if="isEditable(conflict)"
                        :model-value="editableValue(conflict.key)"
                        :aria-label="conflict.label"
                        @update:model-value="onManualEdit(conflict.key, $event)"
                    />
                    <p class="m-0 text-[11px] text-ink-500">{{ t('drafts.base_note', { value: display(conflict.base) }) }}</p>
                </div>
            </div>
        </div>
        <template #footer>
            <Button type="button" @click="$emit('update:open', false)">{{ t('common.cancel') }}</Button>
            <Button type="button" variant="primary" :disabled="busy" @click="confirm">{{ t('drafts.apply') }}</Button>
        </template>
    </Dialog>
</template>
