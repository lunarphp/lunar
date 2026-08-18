<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import RowActions, { type RowAction } from './RowActions.vue';
import StatusBadge from './StatusBadge.vue';

export interface CollectionTreeNode {
    id: number;
    parent_id: number | null;
    group_id: number;
    name: string | null;
    handle: string;
    thumbnail: string | null;
    short_description: string | null;
    status: string;
    status_label: string;
    products_count: number;
    descendants_count: number;
    matched: boolean;
    edit_url: string;
    children_url: string;
    children: CollectionTreeNode[];
    // Extension-contributed row-action URLs land here under their action key.
    _actions?: Record<string, string>;
}

const props = defineProps<{
    collection: CollectionTreeNode;
    depth: number;
    expandedIds: Set<number>;
    // While filtering every node is force-expanded and the chevron disabled,
    // so matches are never hidden behind a collapsed parent.
    forceExpanded: boolean;
    // Nodes whose children are currently being fetched (browse mode).
    loadingIds: Set<number>;
    actions: RowAction[];
}>();

const emit = defineEmits<{ toggle: [node: CollectionTreeNode] }>();

const { t } = useI18n();

// Nested-set bounds tell us a node has children before they are fetched, so
// the chevron shows even while the subtree is still unloaded.
const hasChildren = computed(() => props.collection.descendants_count > 0 || props.collection.children.length > 0);
const isOpen = computed(() => props.forceExpanded || props.expandedIds.has(props.collection.id));
const isLoading = computed(() => props.loadingIds.has(props.collection.id));
const childPad = computed(() => ({ paddingLeft: `${12 + (props.depth + 1) * 22 + 20}px` }));

const statusTone = (status: string): 'sage' | 'warn' | 'archived' =>
    status === 'published' ? 'sage' : status === 'draft' ? 'warn' : 'archived';

const initials = (name: string | null): string => name?.trim().slice(0, 1).toUpperCase() || '?';

const rowPad = computed(() => ({ paddingLeft: `${12 + props.depth * 22}px` }));
</script>

<template>
    <div>
        <div class="group/row flex items-stretch border-b border-line last:border-b-0 hover:bg-surface-2 transition-[background-color] duration-100">
            <!-- Tree gutter: chevron or a spacer to keep alignment -->
            <div :style="rowPad" class="flex items-center pr-1 shrink-0">
                <button
                    v-if="hasChildren"
                    type="button"
                    :disabled="forceExpanded"
                    class="w-5 h-5 grid place-items-center rounded-sm text-ink-400 hover:text-ink-700 hover:bg-surface-2 disabled:opacity-50 disabled:cursor-not-allowed focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                    :aria-label="isOpen ? t('collections.row_collapse') : t('collections.row_expand')"
                    :aria-expanded="isOpen"
                    @click="emit('toggle', collection)"
                >
                    <span :class="['inline-flex transition-transform duration-150', isOpen ? 'rotate-90' : '']">
                        <Icon name="chevRight" cls="sm" />
                    </span>
                </button>
                <span v-else class="w-5 h-5" />
            </div>

            <Link
                :href="collection.edit_url"
                class="flex-1 min-w-0 flex items-center gap-2.5 py-2 pr-1"
            >
                <div class="w-7 h-7 rounded-md overflow-hidden shrink-0 border border-line grid place-items-center bg-surface-2">
                    <img
                        v-if="collection.thumbnail"
                        :src="collection.thumbnail"
                        :alt="collection.name ?? ''"
                        class="w-full h-full object-cover"
                    />
                    <span v-else class="text-[10.5px] font-semibold text-ink-700">{{ initials(collection.name) }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[13px] font-medium text-ink-900 truncate">{{ collection.name }}</div>
                    <div class="text-[11px] font-mono text-ink-500 truncate">{{ collection.handle }}</div>
                </div>
                <div class="hidden md:block text-xs text-ink-500 truncate min-w-0 max-w-[280px] mr-3">
                    {{ collection.short_description }}
                </div>
                <div class="hidden sm:flex items-center gap-3 text-[11px] text-ink-500 [font-variant-numeric:tabular-nums] mr-3 shrink-0">
                    <span
                        v-if="collection.descendants_count"
                        class="inline-flex items-center gap-1"
                        :title="t('collections.descendants_count', { count: collection.descendants_count })"
                    >
                        <Icon name="folder" cls="sm" class="opacity-60" />
                        {{ collection.descendants_count }}
                    </span>
                    <span
                        class="inline-flex items-center gap-1"
                        :title="t('collections.products_count', { count: collection.products_count })"
                    >
                        <Icon name="box" cls="sm" class="opacity-60" />
                        <span class="text-ink-900 font-medium">{{ collection.products_count }}</span>
                    </span>
                </div>
                <StatusBadge :tone="statusTone(collection.status)" size="sm" dot class="shrink-0">
                    {{ collection.status_label }}
                </StatusBadge>
            </Link>

            <div class="flex items-center pl-1 pr-2 shrink-0" @click.stop>
                <RowActions :actions="actions" :row="collection as unknown as Record<string, unknown>" />
            </div>
        </div>

        <template v-if="isOpen">
            <!-- Subtree loading: pulsing placeholder until children arrive -->
            <div
                v-if="isLoading && collection.children.length === 0"
                :style="childPad"
                class="flex items-center gap-2.5 py-2.5 pr-2 border-b border-line"
            >
                <div class="w-7 h-7 rounded-md bg-surface-2 animate-pulse shrink-0" />
                <div class="h-3 w-40 rounded bg-surface-2 animate-pulse" />
            </div>

            <CollectionTreeRow
                v-for="child in collection.children"
                :key="child.id"
                :collection="child"
                :depth="depth + 1"
                :expanded-ids="expandedIds"
                :force-expanded="forceExpanded"
                :loading-ids="loadingIds"
                :actions="actions"
                @toggle="(node) => emit('toggle', node)"
            />
        </template>
    </div>
</template>
