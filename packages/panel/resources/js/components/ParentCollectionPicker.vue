<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { http } from '../lib/http';
import Icon from './Icon.vue';
import TextInput from './TextInput.vue';

export interface ParentOption {
    id: number;
    name: string | null;
    breadcrumb: string;
}

const props = defineProps<{
    modelValue: ParentOption | null;
    searchUrl: string;
    // Parents live in one group; the collection itself (and its subtree) is
    // excluded server-side so a node can never nest under its own descendant.
    groupId: number | null;
    excludeId?: number | null;
    invalid?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: ParentOption | null] }>();

const { t } = useI18n();

const open = ref(false);
const query = ref('');
const results = ref<ParentOption[]>([]);
const searching = ref(false);
const root = ref<HTMLElement | null>(null);

const label = computed(() =>
    props.modelValue ? (props.modelValue.name ?? `#${props.modelValue.id}`) : t('collections.field_parent_none'));

const search = async (): Promise<void> => {
    if (props.groupId === null) {
        results.value = [];

        return;
    }

    searching.value = true;

    try {
        const params = new URLSearchParams({ q: query.value, group_id: String(props.groupId) });

        if (props.excludeId) {
            params.set('exclude', String(props.excludeId));
        }

        const response = await http.get<{ data: ParentOption[] }>(`${props.searchUrl}?${params.toString()}`);
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

watch(open, (isOpen) => {
    if (isOpen) {
        query.value = '';
        void search();
    }
});

const pick = (option: ParentOption | null): void => {
    emit('update:modelValue', option);
    open.value = false;
};

const onDocumentClick = (event: MouseEvent): void => {
    if (open.value && root.value && !root.value.contains(event.target as Node)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('mousedown', onDocumentClick));
onBeforeUnmount(() => document.removeEventListener('mousedown', onDocumentClick));
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            :class="[
                'w-full h-8 px-2.5 pr-7 border rounded-md bg-surface text-left text-[13px] transition-[border-color,box-shadow] duration-100 hover:border-ink-300 focus:outline-none focus:ring-3',
                invalid ? 'border-danger focus:border-danger focus:ring-danger/25' : 'border-line-strong focus:border-sage focus:ring-sage/35',
                modelValue ? 'text-ink-900' : 'text-ink-500',
            ]"
            :disabled="groupId === null"
            aria-haspopup="listbox"
            :aria-expanded="open"
            @click="open = !open"
        >
            <span class="block truncate">{{ label }}</span>
            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-ink-400"><Icon name="chevDown" cls="sm" /></span>
        </button>

        <div
            v-if="open"
            class="absolute z-30 mt-1 w-full bg-paper border border-line rounded-md shadow-lg p-2"
        >
            <TextInput v-model="query" :placeholder="t('collections.parent_search_placeholder')" autofocus>
                <template #prefix><Icon name="search" cls="sm" /></template>
            </TextInput>

            <div class="mt-2 max-h-[220px] overflow-y-auto divide-y divide-line" :class="searching ? 'opacity-60' : ''" role="listbox">
                <button
                    type="button"
                    class="w-full text-left px-2.5 py-2 text-[12.5px] text-ink-700 hover:bg-surface-2 rounded-sm"
                    role="option"
                    :aria-selected="modelValue === null"
                    @click="pick(null)"
                >{{ t('collections.field_parent_none') }}</button>
                <button
                    v-for="option in results"
                    :key="option.id"
                    type="button"
                    class="w-full text-left px-2.5 py-2 text-[12.5px] hover:bg-surface-2 rounded-sm"
                    role="option"
                    :aria-selected="modelValue?.id === option.id"
                    @click="pick(option)"
                >
                    <span class="block text-ink-900 truncate">{{ option.name }}</span>
                    <span v-if="option.breadcrumb" class="block text-[11px] text-ink-500 truncate">{{ option.breadcrumb }}</span>
                </button>
                <div v-if="!results.length && !searching" class="px-2.5 py-3 text-center text-[12px] text-ink-500">
                    {{ t('collections.no_results') }}
                </div>
            </div>
        </div>
    </div>
</template>
