<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuTrigger,
} from 'reka-ui';
import Button from './Button.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import Icon from './Icon.vue';

export interface RowAction {
    key: string;
    label: string;
    icon?: string | null;
    method: string;
    primary: boolean;
    confirmation?: string | null;
}

const props = defineProps<{
    actions: RowAction[];
    row: Record<string, unknown>;
}>();

const { t } = useI18n();

// A row action only renders when the server resolved a URL for this row.
const urls = computed(() => (props.row._actions ?? {}) as Record<string, string>);
const available = computed(() => props.actions.filter((a) => urls.value[a.key]));
const primary = computed(() => available.value.filter((a) => a.primary));
const overflow = computed(() => available.value.filter((a) => !a.primary));

const pending = ref<RowAction | null>(null);

const run = (action: RowAction): void => {
    if (action.confirmation) {
        pending.value = action;

        return;
    }

    dispatch(action);
};

const dispatch = (action: RowAction): void => {
    const url = urls.value[action.key];
    const method = action.method.toLowerCase();

    if (method === 'get') {
        router.visit(url);

        return;
    }

    router[method as 'post' | 'put' | 'patch' | 'delete'](url);
};

const confirm = (): void => {
    if (pending.value) {
        dispatch(pending.value);
        pending.value = null;
    }
};
</script>

<template>
    <div v-if="available.length" class="flex justify-end items-center gap-1" @click.stop>
        <Button
            v-for="action in primary"
            :key="action.key"
            variant="ghost"
            size="sm"
            :icon="action.icon || undefined"
            :aria-label="action.label"
            class="!w-[26px] !h-[26px]"
            @click="run(action)"
        />

        <DropdownMenuRoot v-if="overflow.length">
            <DropdownMenuTrigger as-child>
                <Button
                    variant="ghost"
                    size="sm"
                    icon="more"
                    :aria-label="t('common.more_actions')"
                    class="!w-[26px] !h-[26px]"
                />
            </DropdownMenuTrigger>
            <DropdownMenuPortal>
                <DropdownMenuContent
                    :side-offset="4"
                    align="end"
                    class="z-50 min-w-[160px] rounded-md border border-line bg-surface p-1 shadow-md"
                >
                    <DropdownMenuItem
                        v-for="action in overflow"
                        :key="action.key"
                        class="flex items-center gap-2 px-2 py-1.5 text-[12.5px] text-ink-700 rounded outline-none cursor-pointer data-[highlighted]:bg-surface-2"
                        :class="action.method.toLowerCase() === 'delete' ? 'data-[highlighted]:text-danger' : ''"
                        @select="run(action)"
                    >
                        <Icon v-if="action.icon" :name="action.icon" cls="sm" />
                        {{ action.label }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenuPortal>
        </DropdownMenuRoot>

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
