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

export interface TargetOption {
    kind: string;
    id: number;
    label: string;
    hint: string | null;
}

const props = defineProps<{
    open: boolean;
    searchUrl: string;
    /** Which bucket is being filled; the server uses it to scope the kinds. */
    bucket: string;
    /** The kinds this bucket can target, offered as chips to narrow the search. */
    kinds: string[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    add: [targets: TargetOption[]];
}>();

const { t } = useI18n();

const query = ref('');
const kindFilter = ref<string | null>(null);
const results = ref<TargetOption[]>([]);
const searching = ref(false);
const picked = ref<TargetOption[]>([]);

const key = (option: { kind: string; id: number }): string => `${option.kind}:${option.id}`;

const isPicked = (option: TargetOption): boolean => picked.value.some((row) => key(row) === key(option));

const search = async (): Promise<void> => {
    searching.value = true;

    try {
        const params = new URLSearchParams({ q: query.value, bucket: props.bucket });

        // One search across every kind the bucket allows, narrowed by a chip
        // rather than split into a tab per kind.
        (kindFilter.value ? [kindFilter.value] : []).forEach((kind) => params.append('kinds[]', kind));

        const response = await http.get<{ data: TargetOption[] }>(`${props.searchUrl}?${params.toString()}`);
        results.value = response.data;
    } finally {
        searching.value = false;
    }
};

let timer: ReturnType<typeof setTimeout> | undefined;

watch([query, kindFilter], () => {
    clearTimeout(timer);
    timer = setTimeout(() => void search(), 250);
});

watch(
    () => props.open,
    (open) => {
        if (open) {
            query.value = '';
            kindFilter.value = null;
            picked.value = [];
            void search();
        }
    },
    { immediate: true },
);

const togglePick = (option: TargetOption): void => {
    picked.value = isPicked(option)
        ? picked.value.filter((row) => key(row) !== key(option))
        : [...picked.value, option];
};

const addPicked = (): void => {
    emit('add', picked.value);
    emit('update:open', false);
};
</script>

<template>
    <Dialog
        :open="open"
        size="lg"
        :title="t('discounts.target_dialog_title')"
        :description="t('discounts.target_dialog_description')"
        @update:open="emit('update:open', $event)"
    >
        <TextInput v-model="query" :placeholder="t('discounts.target_search_placeholder')" autofocus>
            <template #prefix><Icon name="search" cls="sm" /></template>
        </TextInput>

        <div v-if="kinds.length > 1" class="flex flex-wrap gap-1.5 mt-2">
            <button
                v-for="kind in ['all', ...kinds]"
                :key="kind"
                type="button"
                class="rounded-full border px-2.5 py-1 text-[11.5px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                :class="(kind === 'all' ? kindFilter === null : kindFilter === kind)
                    ? 'border-sage bg-sage/10 text-ink-900'
                    : 'border-line text-ink-500 hover:text-ink-900'"
                :aria-pressed="kind === 'all' ? kindFilter === null : kindFilter === kind"
                @click="kindFilter = kind === 'all' ? null : kind"
            >
                {{ kind === 'all' ? t('common.all') : t(`discounts.kind_${kind}`) }}
            </button>
        </div>

        <div
            class="mt-3 max-h-[320px] overflow-y-auto border border-line rounded-md divide-y divide-line"
            :class="searching ? 'opacity-60' : ''"
        >
            <label
                v-for="option in results"
                :key="`${option.kind}-${option.id}`"
                class="flex items-center gap-2.5 px-3 py-2 text-[12.5px] cursor-pointer hover:bg-surface-2"
            >
                <Checkbox
                    :model-value="isPicked(option)"
                    :aria-label="option.label"
                    @update:model-value="togglePick(option)"
                />
                <span class="min-w-0 flex-1" :title="option.hint ? `${option.label} — ${option.hint}` : option.label">
                    <span class="block text-ink-900 truncate">{{ option.label }}</span>
                    <!-- Where the target lives: a collection's group and ancestor path,
                         a variant's product, a product's SKU. Two rows can otherwise
                         look identical. -->
                    <span v-if="option.hint" class="block text-[11px] text-ink-500 truncate">{{ option.hint }}</span>
                </span>
                <StatusBadge tone="neutral" size="sm">{{ t(`discounts.kind_${option.kind}`) }}</StatusBadge>
            </label>
            <div v-if="!results.length && !searching" class="px-3 py-4 text-center text-[12px] text-ink-500">
                {{ t('discounts.target_no_results') }}
            </div>
        </div>

        <template #footer>
            <Button @click="emit('update:open', false)">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!picked.length" @click="addPicked">
                {{ t('discounts.target_add_selected', { count: picked.length }) }}
            </Button>
        </template>
    </Dialog>
</template>
