<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Button,
    DataTable,
    FilterDropdown,
    Icon,
    PageEmpty,
    PageHeader,
    PageZone,
    Pagination,
    StatusBadge,
    TextInput,
} from '@lunarphp/panel';
import { ref, watch } from 'vue';

interface ArticleRow {
    id: number;
    title: string;
    status: 'published' | 'draft';
    published: string | null;
    _actions: Record<string, string>;
}

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    articles: Paginator<ArticleRow>;
    filters: { q: string; status: string };
    create_url: string;
}>();

const q = ref(props.filters.q);
const status = ref(props.filters.status || 'all');

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status', width: '160px' },
    { key: 'published', label: 'Published', width: '220px' },
];

const rowActions = [
    { key: 'edit', label: 'Edit', method: 'get' },
    {
        key: 'delete',
        label: 'Delete',
        method: 'delete',
        confirmation: 'Delete this article? This cannot be undone.',
    },
];

const statusOptions = [
    { value: 'all', label: 'All statuses' },
    { value: 'published', label: 'Published' },
    { value: 'draft', label: 'Drafts' },
];

function reload(): void {
    router.get(
        '',
        { q: q.value, status: status.value },
        {
            only: ['articles', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

// The status dropdown reloads immediately; the search box debounces.
let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});
watch(status, reload);

const hasFilters = () => q.value !== '' || status.value !== 'all';

function clearFilters(): void {
    q.value = '';
    status.value = 'all';
}
</script>

<template>
    <div data-screen-label="Articles" class="contents">
        <PageHeader title="Articles">
            <template #actions>
                <Link :href="create_url">
                    <Button variant="primary" icon="plus">New article</Button>
                </Link>
            </template>
        </PageHeader>

        <div
            data-page-content
            class="mx-auto w-full max-w-[1400px] px-4 pt-5 pb-7 sm:px-5 lg:px-7"
        >
            <PageZone region="main" position="before" />

            <div class="mb-4 flex flex-wrap items-center gap-2">
                <div class="max-w-[320px] min-w-0 flex-1">
                    <TextInput
                        v-model="q"
                        clearable
                        placeholder="Search articles"
                    >
                        <template #prefix
                            ><Icon name="search" cls="sm"
                        /></template>
                    </TextInput>
                </div>
                <FilterDropdown
                    v-model="status"
                    label="Status"
                    :options="statusOptions"
                    default-value="all"
                />
            </div>

            <DataTable
                :columns="columns"
                :rows="articles.data"
                :row-to="(row) => (row._actions as Record<string, string>).edit"
                :row-actions="rowActions"
            >
                <template #empty>
                    <PageEmpty
                        :title="
                            hasFilters()
                                ? 'No matching articles'
                                : 'No articles yet'
                        "
                    >
                        {{
                            hasFilters()
                                ? 'Try a different search or filter.'
                                : 'Blog articles you create will appear here.'
                        }}
                        <div v-if="hasFilters()" class="mt-3">
                            <Button @click="clearFilters">Clear filters</Button>
                        </div>
                    </PageEmpty>
                </template>

                <template #cell-status="{ row }">
                    <StatusBadge
                        :tone="row.status === 'published' ? 'sage' : 'warn'"
                        >{{ row.status }}</StatusBadge
                    >
                </template>

                <template #cell-published="{ row }">
                    <span
                        :class="row.published ? 'text-ink-700' : 'text-ink-400'"
                        >{{ row.published ?? 'Draft' }}</span
                    >
                </template>
            </DataTable>

            <Pagination
                v-if="articles.last_page > 1"
                :meta="articles"
                class="mt-4"
            />

            <PageZone region="main" position="after" />
        </div>
    </div>
</template>
