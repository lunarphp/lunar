<script setup lang="ts">
import { router } from '@inertiajs/vue3';
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
    { editTo: null, onEdit: null, onDelete: null, locked: false, lockReason: 'Cannot be deleted' },
);

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
            aria-label="Edit"
            class="!w-[26px] !h-[26px]"
            @click="handleEdit"
        />
        <Tooltip v-if="locked" :text="lockReason">
            <Button
                variant="ghost"
                size="sm"
                icon="lock"
                aria-label="Locked"
                class="!w-[26px] !h-[26px] text-ink-400 cursor-not-allowed"
                disabled
            />
        </Tooltip>
        <Button
            v-else-if="onDelete"
            variant="ghost"
            size="sm"
            icon="trash"
            aria-label="Delete"
            class="!w-[26px] !h-[26px] text-ink-700 hover:text-danger"
            @click="onDelete"
        />
    </div>
</template>
