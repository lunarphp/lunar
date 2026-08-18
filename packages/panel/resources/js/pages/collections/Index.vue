<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import CollectionGroupDialog, { type CollectionGroupOption } from '../../components/CollectionGroupDialog.vue';
import CollectionTreeRow, { type CollectionTreeNode } from '../../components/CollectionTreeRow.vue';
import FilterDropdown, { type FilterOption } from '../../components/FilterDropdown.vue';
import Icon from '../../components/Icon.vue';
import PageEmpty from '../../components/PageEmpty.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import { type RowAction } from '../../components/RowActions.vue';
import TextInput from '../../components/TextInput.vue';
import Tooltip from '../../components/Tooltip.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';

interface CollectionGroupRow extends CollectionGroupOption {
    tree: CollectionTreeNode[];
    urls: { update: string; destroy: string; create_collection: string };
}

const props = defineProps<{
    groups: CollectionGroupRow[];
    tableActions: RowAction[];
    filtering: boolean;
    matchedCount: number;
    totalCount: number;
    filters: { q?: string; status?: string };
    urls: { index: string; create: string; groupsStore: string };
}>();

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    { label: t('nav.catalog') },
    { label: t('nav.collections'), current: true },
];

// Server-side filtering: the payload already holds matches plus their
// ancestors; reloads ride Inertia with preserved state.
const q = ref(props.filters.q ?? '');
const statusFilter = ref<string>(props.filters.status ?? 'all');

const statusOptions: FilterOption[] = [
    { value: 'all', label: t('collections.filter_all_statuses') },
    { value: 'published', label: t('collections.status_published') },
    { value: 'draft', label: t('collections.status_draft') },
    { value: 'archived', label: t('collections.status_archived') },
];

const reload = (): void => {
    router.get(
        props.urls.index,
        {
            q: q.value || undefined,
            status: statusFilter.value === 'all' ? undefined : statusFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

watch(statusFilter, reload);

let searchTimer: ReturnType<typeof setTimeout> | undefined;
watch(q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(reload, 300);
});

const hasActiveFilters = computed(() => !!q.value.trim() || statusFilter.value !== 'all');

const clearFilters = (): void => {
    q.value = '';
    statusFilter.value = 'all';
    reload();
};

// Per-group open state persists across visits; per-row expansion is in-memory
// and collapsed by default, fetching each subtree on first open.
const groupOpenKey = (id: number): string => `lunar_collgrp_${id}`;

const initialGroupOpen = (id: number): boolean => {
    try {
        return localStorage.getItem(groupOpenKey(id)) !== '0';
    } catch {
        return true;
    }
};

const groupOpen = reactive<Record<number, boolean>>(
    Object.fromEntries(props.groups.map((group) => [group.id, initialGroupOpen(group.id)])),
);

watch(
    () => props.groups.map((group) => group.id).join(','),
    () => {
        props.groups.forEach((group) => {
            if (!(group.id in groupOpen)) {
                groupOpen[group.id] = initialGroupOpen(group.id);
            }
        });
    },
);

const toggleGroup = (id: number): void => {
    if (props.filtering) {
        return;
    }

    groupOpen[id] = !groupOpen[id];

    try {
        localStorage.setItem(groupOpenKey(id), groupOpen[id] ? '1' : '0');
    } catch {
        // Persistence is best-effort.
    }
};

const isGroupOpen = (id: number): boolean => props.filtering || groupOpen[id] !== false;

// Browse mode starts fully collapsed: only roots ship in the payload and each
// subtree is fetched the first time its row opens, then cached on the node.
const expandedIds = reactive(new Set<number>());
const loadingIds = reactive(new Set<number>());

const loadChildren = async (node: CollectionTreeNode): Promise<void> => {
    loadingIds.add(node.id);

    try {
        const response = await fetch(node.children_url, { headers: { Accept: 'application/json' } });
        const payload = (await response.json()) as { data: CollectionTreeNode[] };
        node.children = payload.data;
    } catch {
        // Leave the node collapsed on failure so the next open retries.
        expandedIds.delete(node.id);
    } finally {
        loadingIds.delete(node.id);
    }
};

const toggleRow = (node: CollectionTreeNode): void => {
    if (expandedIds.has(node.id)) {
        expandedIds.delete(node.id);

        return;
    }

    expandedIds.add(node.id);

    if (node.children.length === 0 && node.descendants_count > 0) {
        void loadChildren(node);
    }
};

// Group dialog (create or rename/delete).
const dialogOpen = ref(false);
const dialogGroup = ref<CollectionGroupOption | null>(null);

const openNewGroup = (): void => {
    dialogGroup.value = null;
    dialogOpen.value = true;
};

const openEditGroup = (group: CollectionGroupRow): void => {
    dialogGroup.value = group;
    dialogOpen.value = true;
};

const groupHasVisibleRows = (group: CollectionGroupRow): boolean => group.tree.length > 0;
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Collections" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader
                :title="t('collections.title')"
                :description="t('collections.description')"
                icon="folder"
            >
                <template #actions>
                    <Link :href="urls.create">
                        <Button variant="primary" icon="plus">{{ t('collections.new_collection') }}</Button>
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />

                <!-- Toolbar -->
                <div class="flex flex-wrap items-center gap-2 mb-4 min-h-[34px]">
                    <div class="flex-1 max-w-[280px] min-w-[180px]">
                        <TextInput v-model="q" clearable :placeholder="t('collections.search_placeholder')">
                            <template #prefix><Icon name="search" cls="sm" /></template>
                        </TextInput>
                    </div>
                    <FilterDropdown v-model="statusFilter" :label="t('collections.filter_status')" :options="statusOptions" default-value="all" />
                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="text-[12px] text-ink-500 underline underline-offset-2 whitespace-nowrap rounded-sm hover:text-ink-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                        @click="clearFilters"
                    >{{ t('collections.clear_filters') }}</button>
                    <div class="flex-1" />
                    <span class="text-[11.5px] text-ink-500 whitespace-nowrap [font-variant-numeric:tabular-nums]">
                        {{ t('collections.count_of', { shown: matchedCount, total: totalCount }) }}
                    </span>
                    <Button icon="plus" size="sm" @click="openNewGroup">
                        <span class="hidden sm:inline">{{ t('collections.new_group') }}</span>
                    </Button>
                    <Link :href="urls.create" class="sm:hidden">
                        <Button variant="primary" icon="plus">{{ t('common.new') }}</Button>
                    </Link>
                </div>

                <!-- Empty state when nothing matches across all groups -->
                <PageEmpty v-if="filtering && matchedCount === 0" :title="t('collections.empty_title')">
                    {{ t('collections.empty_description') }}
                    <div class="mt-3">
                        <Button @click="clearFilters">{{ t('collections.clear_filters') }}</Button>
                    </div>
                </PageEmpty>

                <!-- Group tree -->
                <div v-else class="bg-surface border border-line rounded-xl shadow-sm overflow-hidden">
                    <template v-for="(group, index) in groups" :key="group.id">
                        <button
                            type="button"
                            :class="[
                                'w-full flex items-center gap-2 px-3 py-2.5 bg-surface-2 text-left cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35 focus-visible:ring-inset',
                                index > 0 ? 'border-t border-line' : '',
                                'border-b border-line',
                            ]"
                            :disabled="filtering"
                            :aria-label="isGroupOpen(group.id) ? t('collections.group_collapse') : t('collections.group_expand')"
                            :aria-expanded="isGroupOpen(group.id)"
                            @click="toggleGroup(group.id)"
                        >
                            <span
                                class="inline-flex items-center justify-center w-3.5 h-3.5 text-ink-500 shrink-0 transition-transform duration-150"
                                :class="isGroupOpen(group.id) ? 'rotate-90' : ''"
                            >
                                <Icon name="chevRight" cls="sm" />
                            </span>
                            <span class="text-[10.5px] font-semibold uppercase tracking-[0.08em] text-ink-700">{{ group.name }}</span>
                            <span class="text-[11px] text-ink-500 [font-variant-numeric:tabular-nums]">{{ group.collections_count }}</span>
                            <span class="flex-1" />
                            <span class="flex items-center gap-0.5" @click.stop>
                                <Tooltip :text="t('collections.group_add_collection')">
                                    <Link :href="group.urls.create_collection">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            icon="plus"
                                            :aria-label="t('collections.group_add_collection')"
                                            class="!w-[26px] !h-[26px]"
                                        />
                                    </Link>
                                </Tooltip>
                                <Tooltip :text="t('collections.group_rename')">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        icon="edit"
                                        :aria-label="t('collections.group_rename')"
                                        class="!w-[26px] !h-[26px]"
                                        @click="openEditGroup(group)"
                                    />
                                </Tooltip>
                            </span>
                        </button>

                        <div v-if="isGroupOpen(group.id)">
                            <!-- Group is empty -->
                            <div
                                v-if="group.collections_count === 0"
                                class="px-4 py-5 text-xs text-ink-500 text-center border-b border-line"
                            >
                                {{ t('collections.group_empty') }}
                                <Link
                                    :href="group.urls.create_collection"
                                    class="text-sage-ink border-b border-dashed border-sage-border hover:border-sage-ink ml-1"
                                >{{ t('collections.group_empty_add') }}</Link>
                            </div>

                            <!-- Group is filtered to nothing -->
                            <div
                                v-else-if="filtering && !groupHasVisibleRows(group)"
                                class="px-4 py-4 text-xs text-ink-500 text-center border-b border-line"
                            >
                                {{ t('collections.group_no_matches') }}
                            </div>

                            <!-- Roots -->
                            <template v-else>
                                <CollectionTreeRow
                                    v-for="root in group.tree"
                                    :key="root.id"
                                    :collection="root"
                                    :depth="0"
                                    :expanded-ids="expandedIds"
                                    :force-expanded="filtering"
                                    :loading-ids="loadingIds"
                                    :actions="tableActions"
                                    @toggle="toggleRow"
                                />
                            </template>
                        </div>
                    </template>
                </div>

                <PageZone region="main" position="after" />
            </div>

            <CollectionGroupDialog
                v-model:open="dialogOpen"
                :group="dialogGroup"
                :store-url="urls.groupsStore"
            />
        </div>
    </PanelLayout>
</template>
