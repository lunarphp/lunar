<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { http } from '../lib/http';
import Button from './Button.vue';
import Checkbox from './Checkbox.vue';
import Dialog from './Dialog.vue';
import Icon from './Icon.vue';
import StatusBadge from './StatusBadge.vue';
import TextInput from './TextInput.vue';

export interface ProductOption {
    id: number;
    name: string | null;
    sku: string | null;
    variants_count?: number;
    thumbnail: string | null;
    brand: string | null;
    status: string;
}

const props = defineProps<{
    open: boolean;
    searchUrl: string;
    // Already-attached ids render disabled so staff can't double-add.
    existingIds: number[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    add: [ids: number[]];
}>();

const { t } = useI18n();

const query = ref('');
const results = ref<ProductOption[]>([]);
const searching = ref(false);
const picked = ref(new Set<number>());

const search = async (): Promise<void> => {
    searching.value = true;

    try {
        const response = await http.get<{ data: ProductOption[] }>(
            `${props.searchUrl}?q=${encodeURIComponent(query.value)}`,
        );
        results.value = response.data;
    } finally {
        searching.value = false;
    }
};

let timer: ReturnType<typeof setTimeout> | undefined;

watch(query, () => {
    clearTimeout(timer);
    timer = setTimeout(() => void search(), 250);
});

watch(
    () => props.open,
    (open) => {
        if (open) {
            query.value = '';
            picked.value = new Set();
            void search();
        }
    },
    // The dialog may mount with open already true.
    { immediate: true },
);

const togglePick = (id: number): void => {
    const next = new Set(picked.value);

    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }

    picked.value = next;
};

const addPicked = (): void => {
    emit('add', Array.from(picked.value));
    emit('update:open', false);
};

const statusTone = (status: string): 'sage' | 'warn' | 'archived' =>
    status === 'published' ? 'sage' : status === 'draft' ? 'warn' : 'archived';

const initials = (name: string | null): string => name?.trim().slice(0, 1).toUpperCase() || '?';
</script>

<template>
    <Dialog
        :open="open"
        size="lg"
        :title="t('products.dialog_title')"
        :description="t('products.dialog_description')"
        @update:open="emit('update:open', $event)"
    >
        <TextInput v-model="query" :placeholder="t('products.search_placeholder')" autofocus>
            <template #prefix><Icon name="search" cls="sm" /></template>
        </TextInput>

        <div class="mt-3 max-h-[320px] overflow-y-auto border border-line rounded-md divide-y divide-line" :class="searching ? 'opacity-60' : ''">
            <label
                v-for="option in results"
                :key="option.id"
                :class="[
                    'flex items-center gap-2.5 px-3 py-2 text-[12.5px]',
                    existingIds.includes(option.id) ? 'opacity-50' : 'cursor-pointer hover:bg-surface-2',
                ]"
            >
                <Checkbox
                    :model-value="existingIds.includes(option.id) || picked.has(option.id)"
                    :disabled="existingIds.includes(option.id)"
                    :aria-label="option.name ?? String(option.id)"
                    @update:model-value="togglePick(option.id)"
                />
                <span class="w-8 h-8 rounded-md overflow-hidden shrink-0 border border-line grid place-items-center bg-surface-2">
                    <img v-if="option.thumbnail" :src="option.thumbnail" :alt="option.name ?? ''" class="w-full h-full object-cover" />
                    <span v-else class="text-[10.5px] font-semibold text-ink-700">{{ initials(option.name) }}</span>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-ink-900 truncate">{{ option.name }}</span>
                    <span v-if="option.sku" class="block text-[11px] font-mono text-ink-500 truncate">{{ option.sku }}</span>
                </span>
                <span v-if="option.brand" class="text-xs text-ink-700 truncate max-w-[120px]">{{ option.brand }}</span>
                <span v-if="existingIds.includes(option.id)" class="text-[11px] text-ink-500 whitespace-nowrap">{{ t('products.already_added') }}</span>
                <StatusBadge v-else :tone="statusTone(option.status)" size="sm" dot>{{ option.status }}</StatusBadge>
            </label>
            <div v-if="!results.length && !searching" class="px-3 py-4 text-center text-[12px] text-ink-500">
                {{ t('products.no_results') }}
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" @click="emit('update:open', false)">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!picked.size" @click="addPicked">
                {{ t('products.add_selected', { count: picked.size }) }}
            </Button>
        </template>
    </Dialog>
</template>
