<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Button from './Button.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import Icon from './Icon.vue';

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
    <div
        v-if="selected.length"
        class="flex items-center gap-3 rounded-lg border border-line bg-surface px-3.5 py-2 shadow-sm"
    >
        <span class="text-[12.5px] text-ink-700">{{ selected.length }} selected</span>

        <div class="flex items-center gap-1.5">
            <Button
                v-for="action in actions"
                :key="action.key"
                size="sm"
                :icon="action.icon || undefined"
                :class="action.method.toLowerCase() === 'delete' ? 'text-danger' : ''"
                @click="run(action)"
            >{{ action.label }}</Button>
        </div>

        <button
            type="button"
            class="ml-1 inline-flex items-center gap-1 text-[12px] text-ink-500 hover:text-ink-900 transition-colors"
            @click="emit('clear')"
        >
            <Icon name="x" cls="sm" />
            Clear
        </button>

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
