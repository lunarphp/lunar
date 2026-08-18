<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Tooltip from './Tooltip.vue';

const props = withDefaults(
    defineProps<{
        editTo?: string | null;
        onEdit?: (() => void) | null;
        onDelete?: (() => void) | null;
        locked?: boolean;
        lockReason?: string;
    }>(),
    { editTo: null, onEdit: null, onDelete: null, locked: false },
);

const { t } = useI18n();

const handleEdit = (): void => {
    if (props.onEdit) {
        props.onEdit();

        return;
    }

    if (props.editTo) {
        router.visit(props.editTo);
    }
};
</script>

<template>
    <div class="flex justify-end items-center gap-1" @click.stop>
        <Button
            v-if="editTo || onEdit"
            variant="ghost"
            size="sm"
            icon="edit"
            :aria-label="t('common.edit')"
            class="!w-[26px] !h-[26px]"
            @click="handleEdit"
        />
        <Tooltip v-if="locked" :text="lockReason ?? t('common.cannot_be_deleted')">
            <Button
                variant="ghost"
                size="sm"
                icon="lock"
                :aria-label="t('common.locked')"
                class="!w-[26px] !h-[26px] text-ink-400 cursor-not-allowed"
                disabled
            />
        </Tooltip>
        <Button
            v-else-if="onDelete"
            variant="ghost"
            size="sm"
            icon="trash"
            :aria-label="t('common.delete')"
            class="!w-[26px] !h-[26px] text-ink-700 hover:text-danger"
            @click="onDelete"
        />
    </div>
</template>
