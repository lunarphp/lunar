<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import AssociationsCard, { type AssociationProduct } from '../../components/AssociationsCard.vue';
import AttributeFields, { type AttributeGroup } from '../../components/AttributeFields.vue';
import AvailabilityCard, { type AvailabilityRow } from '../../components/AvailabilityCard.vue';
import IdentifiersCard from '../../components/IdentifiersCard.vue';
import InventoryCard, { type StockAggregate, type StockLevelRow } from '../../components/InventoryCard.vue';
import PricingEditor, { type CurrencyOption } from '../../components/PricingEditor.vue';
import ProductOptionsBuilder, { type AttachedOption, type VariantRow } from '../../components/ProductOptionsBuilder.vue';
import VariantsTable from '../../components/VariantsTable.vue';
import ShippingCard from '../../components/ShippingCard.vue';
import TaxCard from '../../components/TaxCard.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import CollectionPicker, { type CollectionOption } from '../../components/CollectionPicker.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftRestoredBanner from '../../components/DraftRestoredBanner.vue';
import DraftConflictDialog, { type ConflictField } from '../../components/DraftConflictDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import MediaGroups from '../../components/MediaGroups.vue';
import { type MediaGroup } from '../../components/media';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import Section from '../../components/Section.vue';
import Select from '../../components/Select.vue';
import SideCard from '../../components/SideCard.vue';
import StickySidebar from '../../components/StickySidebar.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import StatusSegmentedControl from '../../components/StatusSegmentedControl.vue';
import TagsInput from '../../components/TagsInput.vue';
import TranslatedInput, { type LanguageOption } from '../../components/TranslatedInput.vue';
import UrlSlugs from '../../components/UrlSlugs.vue';
import { priceConflictField } from '../../components/pricing';
import PanelLayout from '../../layouts/PanelLayout.vue';
import { useEditDraft, type DraftState } from '../../composables/useEditDraft';

interface ActivityEntry {
    description: string;
    created_at: string;
    causer_name: string | null;
    avatar: string | null;
    changes: string[];
}

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
        stock: { aggregate: StockAggregate; levels: StockLevelRow[] };
        urls: { stockAdjust: string; stockIncoming: string | null };
    } | null;
    variantAttributeGroups: AttributeGroup[];
    currencies: CurrencyOption[];
    customerGroups: { id: number; name: string }[];
    taxClasses: { id: number; name: string }[];
    measurements: { length: string[]; weight: string[] };
    fulfilmentMethods: { key: string; label: string; physical: boolean; available: boolean }[];
    draft: DraftState | null;
    languages: LanguageOption[];
    mediaGroups: MediaGroup[];
    attributeGroups: AttributeGroup[];
    availability: { channels: AvailabilityRow[]; customer_groups: AvailabilityRow[] };
    brandOptions: { value: number; label: string }[];
    typeOptions: { value: number; label: string }[];
    typeAttributeGroups: Record<number, AttributeGroup[]>;
    collections: CollectionOption[];
    associationTypes: { type: string; label: string }[];
    associationProducts: Record<number, AssociationProduct>;
    storefrontUrl: string | null;
    activities: ActivityEntry[];
    urls: {
        index: string;
        activityLog: string;
        destroy: string;
        draft: string;
        draftCommit: string;
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
        // linked products under association:{type}; on the simple shape the
        // sole variant's fields under variant:{field}. All are form slices,
        // seeded from the shared formSliceValues prop by useEditDraft.
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

// The conflict dialog shows and merges these keys the way this page edits
// them: selects by their option labels, price lists as money.
const conflictField = (key: string): ConflictField | undefined => {
    const selects: Record<string, ConflictField['options']> = {
        status: statusOptions.value,
        product_type_id: props.typeOptions,
        brand_id: [{ value: null, label: t('products.field_brand_none') }, ...props.brandOptions],
        'variant:tax_class_id': props.taxClasses.map((taxClass) => ({ value: taxClass.id, label: taxClass.name })),
    };

    return key in selects
        ? { options: selects[key] }
        : priceConflictField(key, 'variant:price:', props.currencies, props.customerGroups, t);
};

// The attribute fields follow the selected type, not the saved one, so a
// type change shows its fields right away. Every product attribute's value
// is seeded, and a field shared by both types keeps what was entered.
const selectedAttributeGroups = computed<AttributeGroup[]>(() =>
    props.typeAttributeGroups[Number(details.product_type_id)] ?? props.attributeGroups);

// Availability rows have no per-row error slot; their commit errors list
// under the card so a refused save is never silent.
const availabilityErrors = computed(() =>
    Object.entries(detailsErrors.value)
        .filter(([key]) => key.startsWith('channel:') || key.startsWith('customer_group:'))
        .map(([, message]) => message));

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

// Generating variants from the simple shape removes the sole variant's
// fields from the page, and their unsaved edits with them.
const confirmDropVariantFields = (): boolean =>
    props.shape !== 'simple'
    || !draftForm.dirtyKeys.value.some((key) => key.startsWith('variant:'))
    || window.confirm(t('drafts.variant_fields_drop_confirm'));

// Rows the builder's pending selection would remove; dimmed in the table.
const staleIds = ref<number[]>([]);

// Once variants are generated, the option editor collapses to a summary on the
// variants table; "Edit options" re-expands it. Generating reloads the page,
// which resets this back to collapsed.
const hasGeneratedVariants = computed(() => props.variants.some((variant) => variant.value_ids.length > 0));
const editingOptions = ref(false);
const showOptionsBuilder = computed(() => !hasGeneratedVariants.value || editingOptions.value);
const optionsSummary = computed(() => props.attachedOptions.map((option) => option.name).join(' × '));

const toggleOptions = (): void => {
    editingOptions.value = !editingOptions.value;

    if (editingOptions.value) {
        nextTick(() => {
            document.getElementById('product-options')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
};

// Deep links (e.g. a variant page's "Manage variant options") land on the
// matching section rather than the top of the page.
onMounted(() => {
    const hash = window.location.hash;

    if (!hash) {
        return;
    }

    nextTick(() => {
        document.querySelector(hash)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

const confirmDestroy = (): void => {
    confirmOpen.value = false;
    router.delete(props.urls.destroy);
};

const activityLabel = (description: string): string => {
    const key = `products.activity_${description.replaceAll('-', '_')}`;

    return te(key) ? t(key) : description;
};

const timelineEvents = computed(() =>
    props.activities.map((activity) => ({
        label: activityLabel(activity.description),
        when: activity.created_at,
        actor: activity.causer_name ?? '',
        avatar: activity.avatar,
        changes: activity.changes,
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
                <DraftRestoredBanner :form="draftForm" />
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

                        <MediaGroups :groups="mediaGroups" />

                        <AttributeFields
                            :groups="selectedAttributeGroups"
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
                            <div v-show="showOptionsBuilder" id="product-options" class="scroll-mt-[70px]">
                                <ProductOptionsBuilder
                                    :attached-options="attachedOptions"
                                    :variants="variants"
                                    :search-url="urls.productOptionsSearch"
                                    :generate-url="urls.optionsGenerate"
                                    :confirm-generate="confirmDropVariantFields"
                                    @update:stale-ids="staleIds = $event"
                                    @generated="editingOptions = false"
                                />
                            </div>
                            <VariantsTable
                                v-if="shape === 'multi'"
                                :variants="variants"
                                :currencies="currencies"
                                :bulk-url="urls.variantsBulk"
                                :stale-ids="staleIds"
                                :options-summary="hasGeneratedVariants ? optionsSummary : undefined"
                                :editing-options="editingOptions"
                                @toggle-options="toggleOptions"
                            />
                        </template>

                        <!-- Simple shape: the sole variant's fields edit inline,
                             riding the product draft under variant:{field} keys. -->
                        <template v-if="!wantsMulti && shape === 'simple' && variant">
                            <PricingEditor
                                :currencies="currencies"
                                :customer-groups="customerGroups"
                                namespace="variant"
                                field-prefix="price:"
                            />
                            <InventoryCard
                                :values="details"
                                field-prefix="variant:"
                                :stock="variant.stock"
                                :adjust-url="variant.urls.stockAdjust"
                                :incoming-url="variant.urls.stockIncoming"
                                :errors="detailsErrors"
                            />
                            <ShippingCard
                                :values="details"
                                field-prefix="variant:"
                                :measurements="measurements"
                                :fulfilment-methods="fulfilmentMethods"
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

                        <AssociationsCard
                            :types="associationTypes"
                            :products="associationProducts"
                            :product-id="product.id"
                            :search-url="urls.productsSearch"
                        />

                        <UrlSlugs
                            :languages="languages"
                            path-prefix="/products/"
                            :storefront-url="storefrontUrl"
                        />

                        <PageZone region="main" position="after" :product="product" />
                    </div>

                    <!-- Sidebar -->
                    <aside>
                        <StickySidebar class="flex flex-col gap-4">
                            <PageZone region="sidebar" position="before" :product="product" />

                            <SideCard :title="t('products.side_status')">
                                <StatusSegmentedControl v-model="details.status" :options="statusOptions" />
                                <div v-if="detailsErrors.status" class="mt-1 text-[11px] text-danger" role="alert">{{ detailsErrors.status }}</div>
                                <div class="text-[11.5px] text-ink-500 mt-2.5">{{ statusHelp }}</div>
                            </SideCard>

                            <AvailabilityCard
                                :channels="availability.channels"
                                :customer-groups="availability.customer_groups"
                                :values="details"
                                with-purchasable
                            />
                            <div v-if="availabilityErrors.length" class="-mt-2 text-[11px] text-danger" role="alert">
                                <div v-for="(message, index) in availabilityErrors" :key="index">{{ message }}</div>
                            </div>

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
                                    <div v-if="detailsErrors.collection_ids" class="mt-1 text-[11px] text-danger">{{ detailsErrors.collection_ids }}</div>
                                </div>
                                <div>
                                    <FieldLabel>{{ t('products.field_tags') }}</FieldLabel>
                                    <TagsInput v-model="details.tags" :invalid="!!detailsErrors.tags" />
                                    <div v-if="detailsErrors.tags" class="mt-1 text-[11px] text-danger">{{ detailsErrors.tags }}</div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('products.side_activity')">
                                <template #actions>
                                    <a :href="urls.activityLog" class="text-[11.5px] font-medium text-ink-500 hover:text-ink-900">{{ t('products.side_activity_see_all') }}</a>
                                </template>
                                <ActivityTimeline v-if="activities.length" :events="timelineEvents" :reverse="false" />
                                <div v-else class="text-[11.5px] text-ink-500">{{ t('products.side_activity_empty') }}</div>
                            </SideCard>

                            <PageZone region="sidebar" position="after" :product="product" />
                        </StickySidebar>
                    </aside>
                </div>
            </div>


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
                :field="conflictField"
                :errors="detailsErrors"
                @resolve="onResolveConflicts"
            />
        </div>
    </PanelLayout>
</template>
