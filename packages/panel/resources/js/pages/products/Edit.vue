<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import AttributeFields, { type AttributeGroup } from '../../components/AttributeFields.vue';
import AvailabilityCard, { type AvailabilityRow } from '../../components/AvailabilityCard.vue';
import IdentifiersCard from '../../components/IdentifiersCard.vue';
import InventoryCard, { type StockAggregate, type StockLevelRow } from '../../components/InventoryCard.vue';
import PricingEditor, { type CurrencyOption, type PriceRow } from '../../components/PricingEditor.vue';
import ProductOptionsBuilder, { type AttachedOption, type VariantRow } from '../../components/ProductOptionsBuilder.vue';
import VariantsTable from '../../components/VariantsTable.vue';
import ShippingCard from '../../components/ShippingCard.vue';
import TaxCard from '../../components/TaxCard.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import CollectionPicker, { type CollectionOption } from '../../components/CollectionPicker.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftConflictDialog from '../../components/DraftConflictDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import MediaManager from '../../components/MediaManager.vue';
import { type MediaItem } from '../../components/MediaEditDialog.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import ProductPickerDialog, { type ProductOption } from '../../components/ProductPickerDialog.vue';
import Section from '../../components/Section.vue';
import Select from '../../components/Select.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import StatusSegmentedControl from '../../components/StatusSegmentedControl.vue';
import TagsInput from '../../components/TagsInput.vue';
import TranslatedInput, { type LanguageOption } from '../../components/TranslatedInput.vue';
import UrlSlugs, { type UrlRow } from '../../components/UrlSlugs.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import { useEditDraft, type DraftState } from '../../composables/useEditDraft';

interface ActivityEntry {
    description: string;
    created_at: string;
    causer_name: string | null;
}

interface AssociationEntry {
    id: number;
    product_id: number;
    name: string | null;
    sku: string | null;
    variants_count: number;
    thumbnail: string | null;
    status: string;
    destroy_url: string;
}

type AssociationType = 'alternate' | 'cross-sell' | 'up-sell';

const props = defineProps<{
    product: {
        id: number;
        name: Record<string, string>;
        display_name: string;
        status: 'published' | 'draft' | 'archived';
        status_label: string;
        product_type_id: number;
        product_type_name: string;
        brand_id: number | null;
        short_description: Record<string, string>;
        description: Record<string, string>;
        thumbnail: string | null;
        sku: string | null;
        variants_count: number;
        has_order_history: boolean;
        tags: string[];
        created_at: string;
        updated_at: string;
    };
    shape: 'simple' | 'multi';
    attachedOptions: AttachedOption[];
    variants: VariantRow[];
    variant: {
        id: number;
        prices: PriceRow[];
        stock: { aggregate: StockAggregate; levels: StockLevelRow[] };
        urls: { pricesStore: string; stockAdjust: string };
    } | null;
    variantValues: Record<string, unknown>;
    variantAttributeGroups: AttributeGroup[];
    currencies: CurrencyOption[];
    customerGroups: { id: number; name: string }[];
    taxClasses: { id: number; name: string }[];
    measurements: { length: string[]; weight: string[] };
    draft: DraftState | null;
    languages: LanguageOption[];
    media: MediaItem[];
    productUrls: UrlRow[];
    attributeGroups: AttributeGroup[];
    attributeValues: Record<string, unknown>;
    availability: { channels: AvailabilityRow[]; customer_groups: AvailabilityRow[] };
    availabilityValues: Record<string, unknown>;
    brandOptions: { value: number; label: string }[];
    typeOptions: { value: number; label: string }[];
    collections: CollectionOption[];
    associations: Record<AssociationType, AssociationEntry[]>;
    storefrontUrl: string | null;
    activities: ActivityEntry[];
    urls: {
        index: string;
        update: string;
        destroy: string;
        draft: string;
        draftCommit: string;
        urlsStore: string;
        mediaStore: string;
        mediaReorder: string;
        associationsStore: string;
        collectionsSearch: string;
        productsSearch: string;
        productOptionsSearch: string;
        optionsGenerate: string;
        variantsBulk: string;
    };
}>();

const { t, te } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.catalog') },
    { label: t('nav.products'), href: props.urls.index },
    { label: props.product.display_name, current: true },
]);

// The whole form is draft-backed: dirty fields autosave server-side and
// commit with field-level conflict detection instead of a last-write-wins
// PUT. The save cluster lives in the sticky breadcrumb bar.
const draftForm = useEditDraft({
    initial: {
        name: { ...props.product.name },
        status: props.product.status as string,
        product_type_id: props.product.product_type_id,
        brand_id: props.product.brand_id,
        short_description: { ...props.product.short_description },
        description: { ...props.product.description },
        tags: [...props.product.tags],
        collection_ids: props.collections.map((collection) => collection.id),
        // Mapped attribute values ride the same draft under attribute:{handle}
        // keys; availability rows under channel:{id} / customer_group:{id};
        // on the simple shape the sole variant's fields under variant:{field}.
        ...props.attributeValues,
        ...props.availabilityValues,
        ...props.variantValues,
    },
    draft: props.draft,
    urls: { draft: props.urls.draft, commit: props.urls.draftCommit },
});

const {
    values: details,
    errors: detailsErrors,
    conflicts: draftConflicts,
    committing: draftCommitting,
    commit: commitDetails,
    resolve: resolveDraft,
} = draftForm;

const conflictOpen = computed({
    get: () => draftConflicts.value.length > 0,
    set: (value: boolean) => {
        if (!value) {
            draftConflicts.value = [];
        }
    },
});

const onResolveConflicts = (resolutions: Record<string, unknown>, rebase: Record<string, unknown>): void => {
    void resolveDraft(resolutions, rebase);
};

const submitDetails = (): void => {
    void commitDetails();
};

const statusOptions = computed(() => [
    { value: 'draft', label: t('products.status_draft'), tone: 'warn' as const },
    { value: 'published', label: t('products.status_published'), tone: 'sage' as const },
    { value: 'archived', label: t('products.status_archived'), tone: 'neutral' as const },
]);

const statusHelp = computed(() =>
    details.status === 'published'
        ? t('products.status_published_help')
        : details.status === 'archived'
            ? t('products.status_archived_help')
            : t('products.status_draft_help'));

const statusBadgeTone = computed(() =>
    props.product.status === 'published' ? ('sage' as const) : props.product.status === 'draft' ? ('warn' as const) : ('archived' as const));

// Delete confirmation; products with order history archive instead.
const confirmOpen = ref(false);

// -----------------------------------------------------------------------
// Variant shape. Derived server-side; switching to multi just reveals the
// options builder (nothing persists until Generate), switching back to
// simple collapses to one variant through the generate endpoint.
// -----------------------------------------------------------------------

const wantsMulti = ref(props.shape === 'multi');

watch(() => props.shape, (value) => {
    wantsMulti.value = value === 'multi';
});

const collapseLocked = computed(() =>
    props.variants.slice(1).some((variant) => variant.locked));

const confirmCollapse = ref(false);

const onShapeSelect = (next: 'simple' | 'multi'): void => {
    if (next === 'multi') {
        wantsMulti.value = true;

        return;
    }

    if (props.shape === 'simple') {
        wantsMulti.value = false;

        return;
    }

    if (collapseLocked.value) {
        return;
    }

    confirmCollapse.value = true;
};

const collapse = (): void => {
    confirmCollapse.value = false;
    router.post(props.urls.optionsGenerate, { selections: [] }, { preserveScroll: true });
};

// Rows the builder's pending selection would remove; dimmed in the table.
const staleIds = ref<number[]>([]);

const confirmDestroy = (): void => {
    confirmOpen.value = false;
    router.delete(props.urls.destroy);
};

// Associations: one picker dialog, opened per relationship type.
const ASSOCIATION_TYPES: { type: AssociationType; label: string; hint: string }[] = [
    { type: 'alternate', label: t('products.assoc_related'), hint: t('products.assoc_related_hint') },
    { type: 'cross-sell', label: t('products.assoc_cross_sell'), hint: t('products.assoc_cross_sell_hint') },
    { type: 'up-sell', label: t('products.assoc_up_sell'), hint: t('products.assoc_up_sell_hint') },
];

const picking = ref<AssociationType | null>(null);

const pickerOpen = computed({
    get: () => picking.value !== null,
    set: (value: boolean) => {
        if (!value) {
            picking.value = null;
        }
    },
});

const existingAssociationIds = computed(() => {
    if (!picking.value) {
        return [props.product.id];
    }

    return [
        props.product.id,
        ...props.associations[picking.value].map((entry) => entry.product_id),
    ];
});

const addAssociations = (ids: number[]): void => {
    if (!picking.value || !ids.length) {
        return;
    }

    router.post(
        props.urls.associationsStore,
        { type: picking.value, product_ids: ids },
        { preserveScroll: true },
    );

    picking.value = null;
};

const removeAssociation = (entry: AssociationEntry): void => {
    router.delete(entry.destroy_url, { preserveScroll: true });
};

const activityLabel = (description: string): string => {
    const key = `products.activity_${description.replaceAll('-', '_')}`;

    return te(key) ? t(key) : description;
};

const timelineEvents = computed(() =>
    props.activities.map((activity) => ({
        type: activity.description.replaceAll('-', '_'),
        label: activityLabel(activity.description),
        when: new Date(activity.created_at).toLocaleString(),
        actor: activity.causer_name ?? '',
    })));
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Product edit" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <DraftActions :form="draftForm" />
                </template>
            </Breadcrumbs>

            <PageHeader :title="product.display_name">
                <template #icon>
                    <div class="w-11 h-11 rounded-md overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700">
                        <img v-if="product.thumbnail" :src="product.thumbnail" :alt="product.display_name" class="w-full h-full object-cover" />
                        <Icon v-else name="box" />
                    </div>
                </template>
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <StatusBadge :tone="statusBadgeTone" dot>{{ product.status_label }}</StatusBadge>
                        <template v-if="product.sku">
                            <span class="text-ink-500">·</span>
                            <span class="font-mono">{{ product.sku }}</span>
                        </template>
                        <span class="text-ink-500">·</span>
                        <span>{{ product.product_type_name }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('products.header_variants', { count: product.variants_count }) }}</span>
                    </div>
                </template>
                <template #actions>
                    <Button icon="trash" class="!text-danger" :disabled="product.has_order_history" @click="confirmOpen = true">
                        {{ t('products.delete_product') }}
                    </Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" :product="product" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <form @submit.prevent="submitDetails">
                            <Section :title="t('products.section_basics')">
                                <template #desc>{{ t('products.section_basics_description') }}</template>
                                <div class="mb-3">
                                    <FieldLabel required>{{ t('products.field_name') }}</FieldLabel>
                                    <TranslatedInput
                                        v-model="details.name"
                                        :languages="languages"
                                        kind="text"
                                        :invalid="!!detailsErrors.name"
                                    />
                                    <div v-if="detailsErrors.name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.name }}</div>
                                </div>
                                <div class="mb-3">
                                    <FieldLabel>{{ t('products.field_short_description') }}</FieldLabel>
                                    <TranslatedInput
                                        v-model="details.short_description"
                                        :languages="languages"
                                        kind="text"
                                        :invalid="!!detailsErrors.short_description"
                                    />
                                    <div v-if="detailsErrors.short_description" class="mt-1 text-[11px] text-danger">{{ detailsErrors.short_description }}</div>
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('products.field_short_description_hint') }}</div>
                                </div>
                                <div>
                                    <FieldLabel>{{ t('products.field_description') }}</FieldLabel>
                                    <TranslatedInput
                                        v-model="details.description"
                                        :languages="languages"
                                        kind="richtext"
                                        :invalid="!!detailsErrors.description"
                                    />
                                    <div v-if="detailsErrors.description" class="mt-1 text-[11px] text-danger">{{ detailsErrors.description }}</div>
                                </div>
                            </Section>
                        </form>

                        <MediaManager
                            :items="media"
                            :store-url="urls.mediaStore"
                            :reorder-url="urls.mediaReorder"
                        />

                        <AttributeFields
                            :groups="attributeGroups"
                            :values="details"
                            :errors="detailsErrors"
                            :languages="languages"
                            :description="t('products.attributes_description')"
                        />

                        <!-- Content-adjacent injection point (e.g. an SEO card)
                             between the content cluster and the variants block. -->
                        <PageZone region="content" position="after" :product="product" />

                        <!-- Variant shape: simple products edit their sole variant
                             inline; multiple variants get the options builder and
                             the variants table. Derived state, not a column. -->
                        <div class="py-6 border-b border-line">
                            <h2 class="text-sm font-semibold tracking-[-0.01em] text-ink-900 mb-1">{{ t('products.section_shape') }}</h2>
                            <p class="text-xs text-ink-500 max-w-[640px] mb-3">{{ t('products.section_shape_description') }}</p>
                            <div class="inline-flex p-0.5 rounded-md border border-line bg-surface-2" role="tablist">
                                <button
                                    v-for="option in [
                                        { value: 'simple' as const, label: t('products.shape_simple'), disabled: collapseLocked && shape === 'multi' },
                                        { value: 'multi' as const, label: t('products.shape_multi'), disabled: false },
                                    ]"
                                    :key="option.value"
                                    type="button"
                                    role="tab"
                                    :aria-selected="(option.value === 'multi') === wantsMulti"
                                    :disabled="option.disabled"
                                    :class="[
                                        'inline-flex items-center gap-1.5 h-7 px-3 rounded-[5px] text-[12px] font-medium transition-[background-color,color,box-shadow] duration-100 focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-sage/35',
                                        (option.value === 'multi') === wantsMulti
                                            ? 'bg-ink-900 text-paper shadow-sm'
                                            : 'text-ink-500 hover:text-ink-900 hover:bg-paper/70',
                                        option.disabled ? 'opacity-50 cursor-not-allowed' : '',
                                    ]"
                                    @click="onShapeSelect(option.value)"
                                >
                                    <Icon v-if="option.disabled" name="lock" cls="sm" />
                                    {{ option.label }}
                                </button>
                            </div>
                            <div v-if="collapseLocked && shape === 'multi'" class="mt-1.5 text-[11.5px] text-ink-500">
                                {{ t('products.shape_locked_hint') }}
                            </div>
                        </div>

                        <template v-if="wantsMulti">
                            <ProductOptionsBuilder
                                :attached-options="attachedOptions"
                                :variants="variants"
                                :search-url="urls.productOptionsSearch"
                                :generate-url="urls.optionsGenerate"
                                @update:stale-ids="staleIds = $event"
                            />
                            <VariantsTable
                                v-if="shape === 'multi'"
                                :variants="variants"
                                :currencies="currencies"
                                :bulk-url="urls.variantsBulk"
                                :stale-ids="staleIds"
                            />
                        </template>

                        <!-- Simple shape: the sole variant's fields edit inline,
                             riding the product draft under variant:{field} keys. -->
                        <template v-if="!wantsMulti && shape === 'simple' && variant">
                            <PricingEditor
                                :prices="variant.prices"
                                :currencies="currencies"
                                :customer-groups="customerGroups"
                                :store-url="variant.urls.pricesStore"
                            />
                            <InventoryCard
                                :values="details"
                                field-prefix="variant:"
                                :stock="variant.stock"
                                :adjust-url="variant.urls.stockAdjust"
                                :errors="detailsErrors"
                            />
                            <ShippingCard
                                :values="details"
                                field-prefix="variant:"
                                :measurements="measurements"
                                :errors="detailsErrors"
                            />
                            <IdentifiersCard
                                :values="details"
                                field-prefix="variant:"
                                :errors="detailsErrors"
                            />
                            <TaxCard
                                :values="details"
                                field-prefix="variant:"
                                :tax-classes="taxClasses"
                                :errors="detailsErrors"
                            />
                            <AttributeFields
                                v-if="variantAttributeGroups.length"
                                :groups="variantAttributeGroups"
                                :values="details"
                                :errors="detailsErrors"
                                :languages="languages"
                                :description="t('products.variant_attributes_description')"
                            />
                        </template>

                        <PageZone region="variants" position="after" :product="product" />

                        <Section :title="t('products.section_associations')">
                            <template #desc>{{ t('products.section_associations_description') }}</template>
                            <div class="flex flex-col gap-5">
                                <div v-for="group in ASSOCIATION_TYPES" :key="group.type">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <div>
                                            <div class="text-[12.5px] font-semibold text-ink-900">{{ group.label }}</div>
                                            <div class="text-[11.5px] text-ink-500">{{ group.hint }}</div>
                                        </div>
                                        <Button size="sm" icon="link" @click="picking = group.type">{{ t('products.assoc_add') }}</Button>
                                    </div>
                                    <div v-if="associations[group.type].length" class="border border-line rounded-lg bg-surface overflow-hidden">
                                        <div
                                            v-for="entry in associations[group.type]"
                                            :key="entry.id"
                                            class="flex items-center gap-3 px-3 py-2 border-b border-line last:border-b-0"
                                        >
                                            <div class="w-8 h-8 rounded-md shrink-0 border border-line overflow-hidden bg-surface-2 grid place-items-center text-ink-700">
                                                <img v-if="entry.thumbnail" :src="entry.thumbnail" :alt="entry.name ?? ''" class="w-full h-full object-cover block" />
                                                <Icon v-else name="box" cls="sm" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[12.5px] font-medium text-ink-900 truncate">{{ entry.name }}</div>
                                                <div v-if="entry.sku" class="text-[11px] font-mono text-ink-500 truncate">{{ entry.sku }}</div>
                                            </div>
                                            <button
                                                type="button"
                                                class="h-7 w-7 grid place-items-center rounded-md text-ink-500 hover:text-danger hover:bg-danger-soft transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/25"
                                                :aria-label="t('products.assoc_remove')"
                                                @click="removeAssociation(entry)"
                                            ><Icon name="x" cls="sm" /></button>
                                        </div>
                                    </div>
                                    <div v-else class="border border-dashed border-line-strong rounded-lg bg-surface-2 px-4 py-3 text-[11.5px] text-ink-500">
                                        {{ t('products.assoc_empty') }}
                                    </div>
                                </div>
                            </div>
                        </Section>

                        <UrlSlugs
                            :urls="productUrls"
                            :languages="languages"
                            :store-url="urls.urlsStore"
                            path-prefix="/products/"
                            :storefront-url="storefrontUrl"
                        />

                        <PageZone region="main" position="after" :product="product" />
                    </div>

                    <!-- Sidebar -->
                    <aside>
                        <div class="lg:sticky lg:top-[60px] flex flex-col gap-4">
                            <PageZone region="sidebar" position="before" :product="product" />

                            <SideCard :title="t('products.side_status')">
                                <StatusSegmentedControl v-model="details.status" :options="statusOptions" />
                                <div class="text-[11.5px] text-ink-500 mt-2.5">{{ statusHelp }}</div>
                            </SideCard>

                            <AvailabilityCard
                                :channels="availability.channels"
                                :customer-groups="availability.customer_groups"
                                :values="details"
                                with-purchasable
                            />

                            <SideCard :title="t('products.side_type')">
                                <FieldLabel for="product-type">{{ t('products.field_product_type') }}</FieldLabel>
                                <Select id="product-type" v-model="details.product_type_id" :invalid="!!detailsErrors.product_type_id">
                                    <option v-for="option in typeOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                </Select>
                                <div v-if="detailsErrors.product_type_id" class="mt-1 text-[11px] text-danger">{{ detailsErrors.product_type_id }}</div>
                                <div class="text-[11.5px] text-ink-500 mt-1.5">{{ t('products.side_type_hint') }}</div>
                            </SideCard>

                            <SideCard :title="t('products.side_organization')">
                                <div class="mb-3">
                                    <FieldLabel for="product-brand">{{ t('products.field_brand') }}</FieldLabel>
                                    <Select id="product-brand" v-model="details.brand_id" :invalid="!!detailsErrors.brand_id">
                                        <option :value="null">{{ t('products.field_brand_none') }}</option>
                                        <option v-for="option in brandOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                                    </Select>
                                    <div v-if="detailsErrors.brand_id" class="mt-1 text-[11px] text-danger">{{ detailsErrors.brand_id }}</div>
                                </div>
                                <div class="mb-3">
                                    <FieldLabel>{{ t('products.side_collections') }}</FieldLabel>
                                    <CollectionPicker
                                        v-model="details.collection_ids"
                                        :known="collections"
                                        :search-url="urls.collectionsSearch"
                                    />
                                </div>
                                <div>
                                    <FieldLabel>{{ t('products.field_tags') }}</FieldLabel>
                                    <TagsInput v-model="details.tags" :invalid="!!detailsErrors.tags" />
                                </div>
                            </SideCard>

                            <SideCard :title="t('products.side_activity')">
                                <ActivityTimeline v-if="activities.length" :events="timelineEvents" :reverse="false" />
                                <div v-else class="text-[11.5px] text-ink-500">{{ t('products.side_activity_empty') }}</div>
                            </SideCard>

                            <PageZone region="sidebar" position="after" :product="product" />
                        </div>
                    </aside>
                </div>
            </div>

            <ProductPickerDialog
                :open="pickerOpen"
                :search-url="urls.productsSearch"
                :existing-ids="existingAssociationIds"
                @update:open="pickerOpen = $event"
                @add="addAssociations"
            />

            <ConfirmDialog
                v-model:open="confirmCollapse"
                :title="t('products.shape_collapse_title')"
                :description="t('products.shape_collapse_confirm', { count: Math.max(0, variants.length - 1) })"
                tone="danger"
                :confirm-label="t('products.shape_collapse_apply')"
                @confirm="collapse"
            />

            <ConfirmDialog
                v-model:open="confirmOpen"
                :title="t('products.confirm_delete_title')"
                :description="t('products.confirm_delete')"
                tone="danger"
                :confirm-label="t('common.delete')"
                @confirm="confirmDestroy"
            />

            <DraftConflictDialog
                v-model:open="conflictOpen"
                :conflicts="draftConflicts"
                :busy="draftCommitting"
                @resolve="onResolveConflicts"
            />
        </div>
    </PanelLayout>
</template>
