<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDragSort } from '../composables/useDragSort';
import { useFormSlice } from '../composables/useFormSlice';
import Button from './Button.vue';
import Icon from './Icon.vue';
import ProductPickerDialog, { type ProductOption } from './ProductPickerDialog.vue';
import Section from './Section.vue';

export interface AssociationProduct {
    id: number;
    name: string | null;
    price: string | null;
    variants_count: number | null;
    thumbnail: string | null;
    status: string;
}

/**
 * The product's linked products, one list per association type. Each list is
 * the `association:{type}` draft value: the ordered target ids. Linking,
 * reordering and unlinking dirty the page form and land with Save. Display
 * data comes from the server for the ids it knows and from the picker for
 * ids picked since.
 */
const props = defineProps<{
    types: { type: string; label: string }[];
    products: Record<number, AssociationProduct>;
    productId: number;
    searchUrl: string;
}>();

const { t } = useI18n();

const slice = useFormSlice<Record<string, number[]>>('association');

// Curated label/hint for the built-in types, keyed by type value. The type
// list itself is server-driven, so a registered custom type still renders -
// it just falls back to the enum label the server sends and shows no hint.
const ASSOCIATION_META: Record<string, { label: string; hint: string }> = {
    'alternate': { label: t('products.assoc_related'), hint: t('products.assoc_related_hint') },
    'cross-sell': { label: t('products.assoc_cross_sell'), hint: t('products.assoc_cross_sell_hint') },
    'up-sell': { label: t('products.assoc_up_sell'), hint: t('products.assoc_up_sell_hint') },
};

const knownProducts = ref<Record<number, AssociationProduct>>({});

watch(
    () => props.products,
    (products) => {
        knownProducts.value = { ...knownProducts.value, ...products };
    },
    { immediate: true },
);

const idsFor = (type: string): number[] => (slice.values[type] as number[] | undefined) ?? [];

const setIds = (type: string, ids: number[]): void => {
    slice.values[type] = ids;
};

const entriesFor = (type: string): AssociationProduct[] =>
    idsFor(type).map((id) => knownProducts.value[id] ?? {
        id,
        name: `#${id}`,
        price: null,
        variants_count: null,
        thumbnail: null,
        status: 'draft',
    });

const associationError = (type: string): string | undefined =>
    slice.errors.value[type]
        ?? Object.entries(slice.errors.value).find(([field]) => field.startsWith(`${type}.`))?.[1];

const associationGroups = computed(() =>
    props.types.map((group) => ({
        type: group.type,
        label: ASSOCIATION_META[group.type]?.label ?? group.label,
        hint: ASSOCIATION_META[group.type]?.hint ?? '',
        entries: entriesFor(group.type),
        error: associationError(group.type),
    })),
);

// Collapsed accordion: each relationship type expands independently to reveal
// its linked products.
const expandedGroups = ref<Record<string, boolean>>({});

const toggleGroup = (type: string): void => {
    expandedGroups.value = { ...expandedGroups.value, [type]: !expandedGroups.value[type] };
};

// Animated drag-to-reorder, one list per association type (keyed by type).
// Committing the previewed order in the same tick the transforms clear leaves
// the settled DOM exactly where the preview was - no snap.
const entrySort = useDragSort({
    onCommit: (type, from, to) => {
        const ids = [...idsFor(type)];
        ids.splice(to, 0, ...ids.splice(from, 1));
        setIds(type, ids);
    },
});

// Associations: one picker dialog, opened per relationship type.
const picking = ref<string | null>(null);

const pickerOpen = computed({
    get: () => picking.value !== null,
    set: (value: boolean) => {
        if (!value) {
            picking.value = null;
        }
    },
});

const existingAssociationIds = computed(() =>
    picking.value ? [props.productId, ...idsFor(picking.value)] : [props.productId]);

const addAssociations = (ids: number[], options: ProductOption[]): void => {
    if (!picking.value || !ids.length) {
        return;
    }

    for (const option of options) {
        knownProducts.value[option.id] ??= {
            id: option.id,
            name: option.name,
            price: null,
            variants_count: option.variants_count ?? null,
            thumbnail: option.thumbnail,
            status: option.status,
        };
    }

    const type = picking.value;
    const current = idsFor(type);

    setIds(type, [...current, ...ids.filter((id) => id !== props.productId && !current.includes(id))]);
    expandedGroups.value = { ...expandedGroups.value, [type]: true };
    picking.value = null;
};

const removeAssociation = (type: string, entry: AssociationProduct): void => {
    setIds(type, idsFor(type).filter((id) => id !== entry.id));
};
</script>

<template>
    <Section :title="t('products.section_associations')">
        <template #desc>{{ t('products.section_associations_description') }}</template>
        <div class="flex flex-col gap-2.5">
            <div
                v-for="group in associationGroups"
                :key="group.type"
                class="border border-line rounded-lg bg-surface overflow-hidden"
            >
                <div class="flex items-center gap-3 pl-2.5 pr-3 py-2.5">
                    <button
                        type="button"
                        class="min-w-0 flex-1 flex items-center gap-2 text-left rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                        :aria-expanded="!!expandedGroups[group.type]"
                        @click="toggleGroup(group.type)"
                    >
                        <Icon
                            :name="expandedGroups[group.type] ? 'chevDown' : 'chevRight'"
                            cls="sm"
                            class="shrink-0 text-ink-400"
                        />
                        <span class="min-w-0 flex items-baseline gap-1.5">
                            <span class="text-[12.5px] font-semibold text-ink-900">{{ group.label }}</span>
                            <span v-if="group.hint" class="text-[11.5px] text-ink-500 truncate">· {{ group.hint }}</span>
                        </span>
                    </button>
                    <span class="shrink-0 text-[11.5px] font-mono text-ink-500 tabular-nums">{{ t('products.assoc_count', group.entries.length) }}</span>
                    <Button size="sm" variant="ghost" icon="plus" @click="picking = group.type">{{ t('products.assoc_add') }}</Button>
                </div>
                <div v-if="group.error" class="px-3.5 pb-2 -mt-1 text-[11px] text-danger" data-testid="association-error">{{ group.error }}</div>
                <div
                    v-if="expandedGroups[group.type]"
                    class="border-t border-line"
                    @dragover.prevent="entrySort.over($event, group.type)"
                    @drop.prevent
                >
                    <template v-if="group.entries.length">
                        <div
                            v-for="(entry, index) in group.entries"
                            :key="entry.id"
                            class="group/row flex items-center gap-3 pl-2 pr-3 py-2 border-b border-line last:border-b-0 bg-surface"
                            :class="entrySort.isDragging(group.type, index) ? 'opacity-60 relative z-10' : ''"
                            :style="entrySort.style(group.type, index)"
                            draggable="true"
                            @dragstart="entrySort.start($event, group.type, index)"
                            @dragend="entrySort.end()"
                        >
                            <Icon
                                name="grip"
                                cls="sm"
                                class="shrink-0 text-ink-300 group-hover/row:text-ink-500 cursor-grab active:cursor-grabbing"
                            />
                            <div class="w-9 h-9 rounded-md shrink-0 border border-line overflow-hidden bg-surface-2 grid place-items-center text-ink-700">
                                <img v-if="entry.thumbnail" :src="entry.thumbnail" :alt="entry.name ?? ''" class="w-full h-full object-cover block" />
                                <Icon v-else name="box" cls="sm" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-[12.5px] font-medium text-ink-900 truncate">{{ entry.name }}</div>
                                <div class="text-[11.5px] text-ink-500 truncate">
                                    <template v-if="entry.price">{{ t('products.assoc_from_price', { price: entry.price }) }}<template v-if="entry.variants_count !== null"> · </template></template><template v-if="entry.variants_count !== null">{{ t('products.assoc_variants', entry.variants_count) }}</template>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="h-7 w-7 grid place-items-center rounded-md text-ink-500 hover:text-danger hover:bg-danger-soft transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/25"
                                :aria-label="t('products.assoc_remove')"
                                @click="removeAssociation(group.type, entry)"
                            ><Icon name="x" cls="sm" /></button>
                        </div>
                    </template>
                    <div v-else class="px-3.5 py-3 text-[11.5px] text-ink-500">
                        {{ t('products.assoc_empty') }}
                    </div>
                </div>
            </div>
        </div>
    </Section>

    <ProductPickerDialog
        :open="pickerOpen"
        :search-url="searchUrl"
        :existing-ids="existingAssociationIds"
        @update:open="pickerOpen = $event"
        @add="addAssociations"
    />
</template>
