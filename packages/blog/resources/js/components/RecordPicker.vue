<script setup lang="ts">
import { Icon, http } from '@lunarphp/panel';
import { computed, ref, watch } from 'vue';

interface RecordOption {
    id: number;
    label: string;
    meta: string | null;
    thumbnail: string | null;
}

const props = defineProps<{
    // The selected ids the form stores.
    modelValue: number[];
    // Descriptors for the current selection, so chips render before any search.
    selected: RecordOption[];
    type: 'product' | 'article';
    searchUrl: string;
    // For the article picker: the current article id to exclude from results.
    exclude?: number | null;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number[]];
    'update:selected': [value: RecordOption[]];
}>();

const term = ref('');
const results = ref<RecordOption[]>([]);
const loading = ref(false);
const open = ref(false);
const active = ref(-1);
let debounce: ReturnType<typeof setTimeout> | undefined;

// Results already chosen are hidden from the list so they can't be added twice.
const available = computed(() =>
    results.value.filter((r) => !props.modelValue.includes(r.id)),
);

watch(term, (value) => {
    clearTimeout(debounce);
    active.value = -1;

    if (value.trim() === '') {
        results.value = [];
        open.value = false;

        return;
    }

    loading.value = true;
    open.value = true;
    debounce = setTimeout(runSearch, 250);
});

async function runSearch(): Promise<void> {
    try {
        const params = new URLSearchParams({ type: props.type, q: term.value });

        if (props.exclude) {
            params.set('exclude', String(props.exclude));
        }

        const response = await http.get<{ results?: RecordOption[] }>(
            `${props.searchUrl}?${params.toString()}`,
        );
        results.value = response?.results ?? [];
    } finally {
        loading.value = false;
    }
}

function choose(option: RecordOption): void {
    if (!props.modelValue.includes(option.id)) {
        emit('update:modelValue', [...props.modelValue, option.id]);
        emit('update:selected', [...props.selected, option]);
    }

    term.value = '';
    results.value = [];
    open.value = false;
    active.value = -1;
}

function remove(id: number): void {
    emit(
        'update:modelValue',
        props.modelValue.filter((v) => v !== id),
    );
    emit(
        'update:selected',
        props.selected.filter((s) => s.id !== id),
    );
}

function move(delta: number): void {
    const count = available.value.length;

    if (count === 0) {
        return;
    }

    open.value = true;
    active.value = (active.value + delta + count) % count;
}

function chooseActive(): void {
    const option = available.value[active.value] ?? available.value[0];

    if (option) {
        choose(option);
    }
}

function onBlur(): void {
    // Delay so a result click (which blurs the input) still registers.
    window.setTimeout(() => {
        open.value = false;
    }, 120);
}

function onFocus(): void {
    if (term.value.trim() !== '' && available.value.length) {
        open.value = true;
    }
}

const inputClass =
    'w-full h-8 px-2.5 border rounded-md bg-surface text-[13px] text-ink-900 transition-[border-color,box-shadow] duration-100 hover:border-ink-300 focus:outline-none focus:ring-3 focus:ring-sage/35 focus:border-sage border-line-strong';

// Reset the highlighted row whenever the list content changes.
watch(available, () => {
    if (active.value >= available.value.length) {
        active.value = available.value.length ? 0 : -1;
    }
});
</script>

<template>
    <div>
        <ul v-if="selected.length" class="mb-2 space-y-1">
            <li
                v-for="option in selected"
                :key="option.id"
                class="border-line flex items-center gap-2 rounded border px-2 py-1 text-[13px]"
            >
                <img
                    v-if="option.thumbnail"
                    :src="option.thumbnail"
                    alt=""
                    class="h-8 w-8 rounded object-cover"
                />
                <span class="font-medium">{{ option.label }}</span>
                <span v-if="option.meta" class="text-ink-500">{{
                    option.meta
                }}</span>
                <button
                    type="button"
                    class="text-ink-500 hover:text-ink-900 ml-auto"
                    @click="remove(option.id)"
                >
                    <Icon name="x" class="h-4 w-4" />
                </button>
            </li>
        </ul>

        <div class="relative">
            <input
                v-model="term"
                type="text"
                :class="inputClass"
                :placeholder="placeholder ?? 'Type to search'"
                autocomplete="off"
                role="combobox"
                :aria-expanded="open"
                @focus="onFocus"
                @blur="onBlur"
                @keydown.down.prevent="move(1)"
                @keydown.up.prevent="move(-1)"
                @keydown.enter.prevent="chooseActive"
                @keydown.esc="open = false"
            />

            <div
                v-if="open"
                class="border-line bg-surface absolute right-0 left-0 z-30 mt-1 rounded-md border shadow-lg"
            >
                <p v-if="loading" class="text-ink-500 px-3 py-2 text-[13px]">
                    Searching…
                </p>
                <ul
                    v-else-if="available.length"
                    class="max-h-64 overflow-y-auto py-1"
                >
                    <li v-for="(option, index) in available" :key="option.id">
                        <button
                            type="button"
                            :class="[
                                'flex w-full items-center gap-2 px-3 py-1.5 text-left text-[13px]',
                                index === active ? 'bg-line' : 'hover:bg-line',
                            ]"
                            @mousedown.prevent="choose(option)"
                            @mouseenter="active = index"
                        >
                            <img
                                v-if="option.thumbnail"
                                :src="option.thumbnail"
                                alt=""
                                class="h-8 w-8 rounded object-cover"
                            />
                            <span class="text-ink-900 font-medium">{{
                                option.label
                            }}</span>
                            <span v-if="option.meta" class="text-ink-500">{{
                                option.meta
                            }}</span>
                        </button>
                    </li>
                </ul>
                <p
                    v-else-if="term.trim() !== ''"
                    class="text-ink-500 px-3 py-2 text-[13px]"
                >
                    No matches.
                </p>
            </div>
        </div>
    </div>
</template>
