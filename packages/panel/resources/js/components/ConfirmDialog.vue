<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Dialog from './Dialog.vue';
import Button from './Button.vue';

withDefaults(
    defineProps<{
        open?: boolean;
        title?: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        tone?: 'default' | 'danger';
    }>(),
    { open: false, description: '', tone: 'default' },
);

const { t } = useI18n();

const emit = defineEmits<{ 'update:open': [value: boolean]; confirm: []; cancel: [] }>();

const onConfirm = () => {
    emit('confirm');
    emit('update:open', false);
};
const onCancel = () => {
    emit('cancel');
    emit('update:open', false);
};
</script>

<template>
    <Dialog
        :open="open"
        :title="title ?? t('common.are_you_sure')"
        :description="description"
        size="sm"
        @update:open="$emit('update:open', $event)"
    >
        <slot />
        <template #footer>
            <Button variant="ghost" @click="onCancel">{{ cancelLabel ?? t('common.cancel') }}</Button>
            <Button
                :variant="tone === 'danger' ? 'primary' : 'primary'"
                :class="tone === 'danger' ? '!bg-danger hover:!bg-danger/90 text-paper' : ''"
                @click="onConfirm"
            >{{ confirmLabel ?? t('common.confirm') }}</Button>
        </template>
    </Dialog>
</template>
