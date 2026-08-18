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

export interface PageAction {
    key: string;
    label: string;
    icon?: string | null;
    url?: string | null;
    method: string;
    primary: boolean;
    confirmation?: string | null;
}

const props = defineProps<{ actions: PageAction[] }>();

const { t } = useI18n();

const primary = computed(() => props.actions.filter((a) => a.primary));
const overflow = computed(() => props.actions.filter((a) => !a.primary));

const pending = ref<PageAction | null>(null);

const run = (action: PageAction): void => {
    if (action.confirmation) {
        pending.value = action;

        return;
    }

    dispatch(action);
};

const dispatch = (action: PageAction): void => {
    if (!action.url) {
        return;
    }

    const method = action.method.toLowerCase();

    if (method === 'get') {
        router.visit(action.url);

        return;
    }

    router[method as 'post' | 'put' | 'patch' | 'delete'](action.url);
};

const confirm = (): void => {
    if (pending.value) {
        dispatch(pending.value);
        pending.value = null;
    }
};
</script>

<template>
    <div v-if="actions.length" class="flex items-center gap-2">
        <Button
            v-for="action in primary"
            :key="action.key"
            variant="primary"
            size="sm"
            :icon="action.icon || undefined"
            @click="run(action)"
        >{{ action.label }}</Button>

        <DropdownMenuRoot v-if="overflow.length">
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="sm" icon="more" :aria-label="t('common.more_actions')" class="!w-[30px]" />
            </DropdownMenuTrigger>
            <DropdownMenuPortal>
                <DropdownMenuContent
                    :side-offset="4"
                    align="end"
                    class="z-50 min-w-[180px] rounded-md border border-line bg-surface p-1 shadow-md"
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
