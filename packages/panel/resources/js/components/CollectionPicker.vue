<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { http } from '../lib/http';
import Button from './Button.vue';
import Dialog from './Dialog.vue';
import Checkbox from './Checkbox.vue';
import Icon from './Icon.vue';
import TextInput from './TextInput.vue';

export interface CollectionOption {
    id: number;
    name: string | null;
    breadcrumb: string;
}

const props = defineProps<{
    modelValue: number[];
    // Metadata for already-selected ids; search results merge in as staff browse.
    known: CollectionOption[];
    searchUrl: string;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: number[]] }>();

const { t } = useI18n();

const knownById = ref(new Map<number, CollectionOption>(props.known.map((option) => [option.id, option])));

watch(
    () => props.known,
    (known) => {
        known.forEach((option) => knownById.value.set(option.id, option));
    },
);

const selected = computed(() =>
    props.modelValue.map((id) => knownById.value.get(id) ?? { id, name: `#${id}`, breadcrumb: '' }));

const remove = (id: number): void => {
    emit('update:modelValue', props.modelValue.filter((current) => current !== id));
};

// Picker dialog: debounced search against the shared catalog endpoint.
const dialogOpen = ref(false);
const query = ref('');
const results = ref<CollectionOption[]>([]);
const searching = ref(false);
const picked = ref(new Set<number>());

const search = async (): Promise<void> => {
    searching.value = true;

    try {
        const response = await http.get<{ data: CollectionOption[] }>(
            `${props.searchUrl}?q=${encodeURIComponent(query.value)}`,
        );
        results.value = response.data;
        response.data.forEach((option) => knownById.value.set(option.id, option));
    } finally {
        searching.value = false;
    }
};

let timer: ReturnType<typeof setTimeout> | undefined;

watch(query, () => {
    clearTimeout(timer);
    timer = setTimeout(() => void search(), 250);
});

watch(dialogOpen, (open) => {
    if (open) {
        query.value = '';
        picked.value = new Set();
        void search();
    }
});

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
    const next = new Set(props.modelValue);
    picked.value.forEach((id) => next.add(id));
    emit('update:modelValue', Array.from(next));
    dialogOpen.value = false;
};
</script>

<template>
    <div class="flex flex-wrap gap-1.5 px-1.5 py-1.5 min-h-[34px] border border-line-strong rounded-md bg-surface items-center">
        <span
            v-for="option in selected"
            :key="option.id"
            class="inline-flex items-center gap-1.5 h-[22px] pl-2 pr-1 border border-sage-border bg-sage-soft rounded-full text-[11.5px] text-sage-ink"
            :title="option.breadcrumb || undefined"
        >
            <Icon name="folder" cls="sm" />
            {{ option.name }}
            <button
                type="button"
                class="w-4 h-4 rounded-full grid place-items-center text-ink-400 cursor-pointer hover:bg-line-strong hover:text-ink-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                :aria-label="t('collections.remove', { name: option.name ?? option.id })"
                @click="remove(option.id)"
            ><Icon name="x" cls="sm" /></button>
        </span>

        <button
            type="button"
            class="inline-flex items-center gap-1 h-[22px] px-2 rounded-full border border-dashed border-line-strong text-[11.5px] text-ink-700 hover:bg-surface-2 hover:border-ink-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
            @click="dialogOpen = true"
        >
            <Icon name="plus" cls="sm" />
            {{ selected.length ? t('collections.add_more') : t('collections.add') }}
        </button>
    </div>

    <Dialog
        :open="dialogOpen"
        :title="t('collections.dialog_title')"
        :description="t('collections.dialog_description')"
        @update:open="dialogOpen = $event"
    >
        <TextInput v-model="query" :placeholder="t('collections.search_placeholder')" autofocus>
            <template #prefix><Icon name="search" cls="sm" /></template>
        </TextInput>

        <div class="mt-3 max-h-[280px] overflow-y-auto border border-line rounded-md divide-y divide-line" :class="searching ? 'opacity-60' : ''">
            <label
                v-for="option in results"
                :key="option.id"
                :class="[
                    'flex items-center gap-2.5 px-3 py-2 text-[12.5px]',
                    modelValue.includes(option.id) ? 'opacity-50' : 'cursor-pointer hover:bg-surface-2',
                ]"
            >
                <Checkbox
                    :model-value="modelValue.includes(option.id) || picked.has(option.id)"
                    :disabled="modelValue.includes(option.id)"
                    :aria-label="option.name ?? String(option.id)"
                    @update:model-value="togglePick(option.id)"
                />
                <span class="min-w-0">
                    <span class="block text-ink-900 truncate">{{ option.name }}</span>
                    <span v-if="option.breadcrumb" class="block text-[11px] text-ink-500 truncate">{{ option.breadcrumb }}</span>
                </span>
            </label>
            <div v-if="!results.length && !searching" class="px-3 py-4 text-center text-[12px] text-ink-500">
                {{ t('collections.no_results') }}
            </div>
        </div>

        <template #footer>
            <Button variant="ghost" @click="dialogOpen = false">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!picked.size" @click="addPicked">
                {{ t('collections.add_selected', { count: picked.size }) }}
            </Button>
        </template>
    </Dialog>
</template>
