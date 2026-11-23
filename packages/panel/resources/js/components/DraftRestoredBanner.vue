<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Icon from './Icon.vue';
import type { EditDraftForm } from '../composables/useEditDraft';

// Shown when an edit page opens on the staff member's stored draft, so
// restored edits are never mistaken for the record's saved values. It goes
// away once the draft is saved or discarded.
const props = defineProps<{
    form: EditDraftForm<Record<string, unknown>>;
}>();

const { t } = useI18n();

const restoredAt = computed(() => {
    const raw = props.form.restoredFrom.value;

    if (!raw || !props.form.hasDraft.value) {
        return null;
    }

    const date = new Date(raw);

    return Number.isNaN(date.getTime()) ? raw : date.toLocaleString();
});

const discard = (): void => {
    void props.form.discard();
};
</script>

<template>
    <div
        v-if="restoredAt"
        role="status"
        class="flex items-center gap-3 rounded-md border border-line bg-surface-2 px-4 py-2.5 mb-5"
        data-testid="draft-restored-banner"
    >
        <Icon name="clock" cls="sm" class="text-ink-500 shrink-0" />
        <p class="m-0 flex-1 text-[12.5px] text-ink-900">{{ t('drafts.restored', { time: restoredAt }) }}</p>
        <Button size="sm" :disabled="form.committing.value" @click="discard">{{ t('drafts.discard') }}</Button>
    </div>
</template>
