<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { DialogContent, DialogDescription, DialogOverlay, DialogPortal, DialogRoot, DialogTitle, VisuallyHidden } from 'reka-ui';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import { http } from '../lib/http';
import { useCommandPalette } from '../composables/useCommandPalette';
import { useRecentRecords } from '../composables/useRecentRecords';
import type { SearchCommand, SearchKind, SearchResult } from '../types/search';

/** A row the palette can act on: a matched record, or a static command. */
interface PaletteItem {
    key: string;
    label: string;
    hint: string | null;
    icon: string;
    url: string;
    kind: string | null;
    id: string | number | null;
}

interface PaletteGroup {
    key: string;
    label: string;
    items: PaletteItem[];
}

const { open, closePalette } = useCommandPalette();
const { t } = useI18n();
const page = usePage();

const searchUrl = computed(() => `/${(page.props.panel as { path: string }).path}/search`);
const commands = computed<SearchCommand[]>(() => (page.props.searchCommands as SearchCommand[] | undefined) ?? []);
const kinds = computed<SearchKind[]>(() => (page.props.searchSources as SearchKind[] | undefined) ?? []);
const userId = computed(() => (page.props.auth as { user: { id: string | number } | null }).user?.id ?? 'guest');

const { records: recent, forget } = useRecentRecords(userId.value);

const query = ref('');
const kindFilter = ref<string | null>(null);
const results = ref<SearchResult[]>([]);
const searching = ref(false);
const highlighted = ref(0);
const input = ref<HTMLInputElement | null>(null);
const listbox = ref<HTMLElement | null>(null);

const term = computed(() => query.value.trim());

const recordItem = (row: SearchResult): PaletteItem => ({
    key: `${row.kind}:${row.id}`,
    label: row.label,
    hint: row.hint,
    icon: row.icon,
    url: row.url,
    kind: row.kind,
    id: row.id,
});

const commandItem = (command: SearchCommand): PaletteItem => ({
    key: `command:${command.key}`,
    label: command.label,
    hint: null,
    icon: command.icon,
    url: command.url,
    kind: null,
    id: null,
});

const matchingCommands = computed<SearchCommand[]>(() => {
    if (term.value === '') {
        return commands.value;
    }

    const needle = term.value.toLowerCase();

    return commands.value.filter((command) => command.label.toLowerCase().includes(needle));
});

const groups = computed<PaletteGroup[]>(() => {
    const groups: PaletteGroup[] = [];

    if (term.value === '') {
        if (recent.value.length) {
            groups.push({
                key: 'recent',
                label: t('search.recent_heading'),
                items: recent.value.map(recordItem),
            });
        }
    } else {
        // Server order, so a source's position decides where its group sits.
        for (const kind of kinds.value) {
            const rows = results.value.filter((row) => row.kind === kind.key);

            if (rows.length) {
                groups.push({ key: kind.key, label: kind.label, items: rows.map(recordItem) });
            }
        }
    }

    if (matchingCommands.value.length) {
        groups.push({
            key: 'commands',
            label: t('search.commands_heading'),
            items: matchingCommands.value.map(commandItem),
        });
    }

    return groups;
});

const flattened = computed<PaletteItem[]>(() => groups.value.flatMap((group) => group.items));

const search = async (): Promise<void> => {
    if (term.value === '') {
        results.value = [];

        return;
    }

    searching.value = true;

    try {
        const params = new URLSearchParams({ q: term.value });

        (kindFilter.value ? [kindFilter.value] : []).forEach((kind) => params.append('kinds[]', kind));

        const response = await http.get<{ data: SearchResult[] }>(`${searchUrl.value}?${params.toString()}`);
        results.value = response.data;
    } catch {
        results.value = [];
    } finally {
        searching.value = false;
    }
};

let timer: ReturnType<typeof setTimeout> | undefined;

watch([query, kindFilter], () => {
    clearTimeout(timer);
    timer = setTimeout(() => void search(), 250);
});

watch(flattened, () => {
    highlighted.value = 0;
});

watch(open, (isOpen) => {
    if (!isOpen) {
        return;
    }

    query.value = '';
    kindFilter.value = null;
    results.value = [];
    highlighted.value = 0;

    void nextTick(() => input.value?.focus());
});

function select(item: PaletteItem): void {
    closePalette();

    router.visit(item.url, {
        // A recent record may have been deleted since it was visited; drop it
        // rather than keep offering a dead row.
        onHttpException: (response) => {
            if (response.status === 404 && item.kind && item.id !== null) {
                forget(item.kind, item.id);
            }
        },
    });
}

function move(delta: number): void {
    const total = flattened.value.length;

    if (!total) {
        return;
    }

    highlighted.value = (highlighted.value + delta + total) % total;

    void nextTick(() => {
        listbox.value
            ?.querySelector(`[data-index="${highlighted.value}"]`)
            ?.scrollIntoView({ block: 'nearest' });
    });
}

function onListKeydown(e: KeyboardEvent): void {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        move(1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        move(-1);
    } else if (e.key === 'Enter') {
        e.preventDefault();

        const item = flattened.value[highlighted.value];

        if (item) {
            select(item);
        }
    }
}

function onKeydown(e: KeyboardEvent): void {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        open.value = !open.value;
    }
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
    clearTimeout(timer);
});
</script>

<template>
    <DialogRoot :open="open" @update:open="open = $event">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 bg-ink-900/40 z-50 data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out" />
            <DialogContent
                class="fixed left-1/2 top-[12vh] -translate-x-1/2 w-[calc(100vw-2rem)] max-w-[560px] max-h-[70vh] z-50 flex flex-col bg-paper border border-line rounded-xl shadow-lg focus:outline-none data-[state=open]:animate-dialog-in data-[state=closed]:animate-dialog-out"
                @keydown="onListKeydown"
            >
                <VisuallyHidden>
                    <DialogTitle>{{ t('search.aria_label') }}</DialogTitle>
                    <DialogDescription>{{ t('search.placeholder') }}</DialogDescription>
                </VisuallyHidden>

                <div class="flex items-center gap-2.5 px-4 py-3 border-b border-line shrink-0">
                    <Icon name="search" cls="sm" />
                    <input
                        ref="input"
                        v-model="query"
                        type="text"
                        role="combobox"
                        aria-expanded="true"
                        aria-controls="command-palette-list"
                        :aria-activedescendant="flattened[highlighted] ? `command-palette-item-${highlighted}` : undefined"
                        :placeholder="t('search.placeholder')"
                        :aria-label="t('search.aria_label')"
                        class="flex-1 min-w-0 bg-transparent text-[13.5px] text-ink-900 placeholder:text-ink-400 focus:outline-none"
                    >
                    <span v-if="searching" class="text-[11px] text-ink-400">{{ t('search.searching') }}</span>
                </div>

                <div v-if="kinds.length > 1" class="flex flex-wrap gap-1.5 px-4 py-2.5 border-b border-line shrink-0">
                    <button
                        v-for="kind in [{ key: 'all', label: t('search.filter_all'), icon: 'search' }, ...kinds]"
                        :key="kind.key"
                        type="button"
                        class="rounded-full border px-2.5 py-1 text-[11.5px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                        :class="(kind.key === 'all' ? kindFilter === null : kindFilter === kind.key)
                            ? 'border-sage bg-sage/10 text-ink-900'
                            : 'border-line text-ink-500 hover:text-ink-900'"
                        :aria-pressed="kind.key === 'all' ? kindFilter === null : kindFilter === kind.key"
                        @click="kindFilter = kind.key === 'all' ? null : kind.key"
                    >
                        {{ kind.label }}
                    </button>
                </div>

                <div id="command-palette-list" ref="listbox" role="listbox" :aria-label="t('search.aria_label')" class="min-h-0 flex-1 overflow-y-auto py-1.5">
                    <template v-for="group in groups" :key="group.key">
                        <div class="text-[10px] uppercase tracking-[0.08em] text-ink-400 px-4 pt-2.5 pb-1 font-medium">
                            {{ group.label }}
                        </div>
                        <button
                            v-for="item in group.items"
                            :id="`command-palette-item-${flattened.indexOf(item)}`"
                            :key="item.key"
                            type="button"
                            role="option"
                            :data-index="flattened.indexOf(item)"
                            :aria-selected="flattened.indexOf(item) === highlighted"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-left text-[12.5px] cursor-pointer"
                            :class="flattened.indexOf(item) === highlighted ? 'bg-surface-2' : 'hover:bg-surface-2'"
                            @click="select(item)"
                            @mousemove="highlighted = flattened.indexOf(item)"
                        >
                            <Icon :name="item.icon" cls="sm" />
                            <span class="min-w-0 flex-1">
                                <span class="block text-ink-900 truncate">{{ item.label }}</span>
                                <span v-if="item.hint" class="block text-[11px] text-ink-500 truncate">{{ item.hint }}</span>
                            </span>
                        </button>
                    </template>

                    <div v-if="!groups.length" class="px-4 py-6 text-center text-[12px] text-ink-500">
                        {{ term === '' ? t('search.empty_hint') : t('search.no_results', { term }) }}
                    </div>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
