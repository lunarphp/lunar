<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import Icon from '../Icon.vue';
import PageEmpty from '../PageEmpty.vue';

export interface HiddenWidget {
    key: string;
    label: string;
    description?: string | null;
    icon?: string | null;
}

defineProps<{
    open: boolean;
    hidden: HiddenWidget[];
}>();

const emit = defineEmits<{ 'update:open': [value: boolean]; add: [key: string]; reset: [] }>();

const { t } = useI18n();
</script>

<template>
    <Dialog
        :open="open"
        :title="t('dashboard.customise_title')"
        :description="t('dashboard.customise_description')"
        size="sm"
        @update:open="emit('update:open', $event)"
    >
        <PageEmpty v-if="hidden.length === 0" :title="t('dashboard.customise_all_visible_title')">
            {{ t('dashboard.customise_all_visible_description') }}
        </PageEmpty>
        <div v-else class="flex flex-col gap-1.5 max-h-[60vh] overflow-y-auto -mx-1 px-1">
            <div
                v-for="widget in hidden"
                :key="widget.key"
                class="flex items-center gap-3 p-2.5 border border-line rounded-md hover:bg-surface-2 transition-colors"
            >
                <div class="w-8 h-8 rounded-md bg-surface-2 border border-line grid place-items-center text-ink-700 shrink-0">
                    <Icon :name="widget.icon || 'dashboard'" cls="sm" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-[13px] font-medium text-ink-900 truncate">{{ widget.label }}</div>
                    <div v-if="widget.description" class="text-[11.5px] text-ink-500 truncate">{{ widget.description }}</div>
                </div>
                <Button size="sm" icon="plus" @click="emit('add', widget.key)">{{ t('dashboard.add') }}</Button>
            </div>
        </div>

        <div class="flex items-center justify-between mt-5">
            <button
                type="button"
                class="text-[11.5px] text-ink-500 hover:text-ink-900 underline decoration-dotted underline-offset-[3px]"
                @click="emit('reset')"
            >
                {{ t('dashboard.reset') }}
            </button>
            <Button @click="emit('update:open', false)">{{ t('dashboard.done') }}</Button>
        </div>
    </Dialog>
</template>
