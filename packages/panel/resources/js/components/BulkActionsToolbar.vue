<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Button from './Button.vue';
import ConfirmDialog from './ConfirmDialog.vue';

export interface BulkAction {
    key: string;
    label: string;
    icon?: string | null;
    url?: string | null;
    method: string;
    confirmation?: string | null;
}

const props = defineProps<{
    actions: BulkAction[];
    selected: (string | number)[];
    idField?: string;
}>();

const emit = defineEmits<{ clear: []; done: [] }>();

const pending = ref<BulkAction | null>(null);

const run = (action: BulkAction): void => {
    if (action.confirmation) {
        pending.value = action;

        return;
    }

    dispatch(action);
};

const dispatch = (action: BulkAction): void => {
    if (!action.url) {
        return;
    }

    const method = action.method.toLowerCase() as 'post' | 'put' | 'patch' | 'delete';

    router[method](
        action.url,
        { [props.idField ?? 'ids']: props.selected },
        { preserveScroll: true, onSuccess: () => emit('done') },
    );
};

const confirm = (): void => {
    if (pending.value) {
        dispatch(pending.value);
        pending.value = null;
    }
};
</script>

<template>
    <!-- Inline row so it can replace the filters in the same toolbar slot, keeping
         the table in place rather than pushing it down with an extra bar. -->
    <div v-if="selected.length" class="flex flex-wrap items-center gap-2 w-full">
        <span class="text-xs text-ink-700 pl-1 pr-1.5 whitespace-nowrap">
            <span class="font-semibold text-ink-900 [font-variant-numeric:tabular-nums]">{{ selected.length }}</span>
            selected
        </span>

        <span class="w-px h-5 bg-line" />

        <Button
            v-for="action in actions"
            :key="action.key"
            :icon="action.icon || undefined"
            :class="action.method.toLowerCase() === 'delete' ? 'text-danger' : ''"
            @click="run(action)"
        >{{ action.label }}</Button>

        <div class="flex-1" />

        <Button variant="ghost" @click="emit('clear')">Clear</Button>

        <ConfirmDialog
            :open="!!pending"
            :title="pending?.label"
            :description="pending?.confirmation || ''"
            :confirm-label="pending?.label"
            tone="danger"
            @update:open="(v: boolean) => !v && (pending = null)"
            @confirm="confirm"
        />
    </div>
</template>
