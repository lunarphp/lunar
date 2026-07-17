<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import type { EditDraftForm } from '../composables/useEditDraft';

// The standard home for a draft-backed form's save cluster: drop into the
// sticky Breadcrumbs #actions slot. Shopify-style contextual bar — rendered
// only while there is something to save or throw away, so a clean page shows
// nothing.
const props = defineProps<{
    form: EditDraftForm<Record<string, unknown>>;
}>();

const { t } = useI18n();

const active = computed(() => props.form.isDirty.value || props.form.hasDraft.value);

const status = computed(() => {
    if (props.form.saving.value) {
        return t('drafts.saving');
    }

    return props.form.savedAt.value ? t('drafts.saved') : null;
});

const save = (): void => {
    void props.form.commit();
};

const discard = (): void => {
    void props.form.discard();
};
</script>

<template>
    <template v-if="active">
        <span v-if="status" role="status" class="hidden sm:inline text-[11px] text-ink-500 mr-1">{{ status }}</span>
        <Button :disabled="form.committing.value" @click="discard">{{ t('drafts.discard') }}</Button>
        <Button variant="primary" :disabled="form.committing.value" @click="save">{{ t('common.save_changes') }}</Button>
    </template>
</template>
