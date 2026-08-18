<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import AttributeFields, { type AttributeGroup } from '../../components/AttributeFields.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftConflictDialog from '../../components/DraftConflictDialog.vue';
import Icon from '../../components/Icon.vue';
import IdentifiersCard from '../../components/IdentifiersCard.vue';
import InventoryCard, { type StockAggregate, type StockLevelRow } from '../../components/InventoryCard.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import PricingEditor, { type CurrencyOption, type PriceRow } from '../../components/PricingEditor.vue';
import ShippingCard from '../../components/ShippingCard.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import TaxCard from '../../components/TaxCard.vue';
import Toggle from '../../components/Toggle.vue';
import Tooltip from '../../components/Tooltip.vue';
import VariantMediaPicker, { type VariantMediaItem } from '../../components/VariantMediaPicker.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import { type LanguageOption } from '../../components/TranslatedInput.vue';
import { useEditDraft, type DraftState } from '../../composables/useEditDraft';

interface ActivityEntry {
    description: string;
    created_at: string;
    causer_name: string | null;
    avatar: string | null;
    changes: string[];
}

const props = defineProps<{
    product: { id: number; name: string; edit_url: string };
    variant: {
        id: number;
        label: string;
        sku: string | null;
        enabled: boolean;
        thumbnail: string | null;
        axes: { option: string; value: string }[];
        position: number;
        total: number;
        prev_url: string | null;
        next_url: string | null;
        prices: PriceRow[];
        stock: { aggregate: StockAggregate; levels: StockLevelRow[] };
    };
    variantValues: Record<string, unknown>;
    attributeGroups: AttributeGroup[];
    mediaPool: VariantMediaItem[];
    draft: DraftState | null;
    languages: LanguageOption[];
    currencies: CurrencyOption[];
    customerGroups: { id: number; name: string }[];
    taxClasses: { id: number; name: string }[];
    measurements: { length: string[]; weight: string[] };
    activities: ActivityEntry[];
    canDelete: boolean;
    deleteBlockedReason: 'order_history' | 'last_variant' | null;
    urls: {
        productEdit: string;
        activityLog: string;
        update: string;
        destroy: string;
        draft: string;
        draftCommit: string;
        pricesStore: string;
        stockAdjust: string;
        mediaSync: string;
    };
}>();

const { t, te } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.catalog') },
    { label: t('nav.products'), href: props.urls.productEdit },
    { label: props.product.name, href: props.urls.productEdit },
    { label: props.variant.label, current: true },
]);

// The variant form is draft-backed, exactly like the product page.
const draftForm = useEditDraft({
    initial: { ...props.variantValues },
    draft: props.draft,
    urls: { draft: props.urls.draft, commit: props.urls.draftCommit },
});

const {
    values: details,
    errors: detailsErrors,
    conflicts: draftConflicts,
    committing: draftCommitting,
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

const enabled = computed(() => Boolean(details.enabled));

const toggleEnabled = (): void => {
    details.enabled = !enabled.value;
};

const confirmOpen = ref(false);

const deleteBlockedText = computed(() =>
    props.deleteBlockedReason ? t(`products.variant_delete_blocked_${props.deleteBlockedReason}`) : '',
);

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
        <div data-screen-label="Variant edit" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <DraftActions :form="draftForm" />
                </template>
            </Breadcrumbs>

            <PageHeader :title="variant.label">
                <template #icon>
                    <div class="w-11 h-11 rounded-md overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700">
                        <img v-if="variant.thumbnail" :src="variant.thumbnail" :alt="variant.label" class="w-full h-full object-cover" />
                        <Icon v-else name="box" />
                    </div>
                </template>
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <StatusBadge :tone="enabled ? 'sage' : 'warn'" dot>
                            {{ enabled ? t('products.variants_state_enabled') : t('products.variants_state_disabled') }}
                        </StatusBadge>
                        <template v-if="variant.sku">
                            <span class="text-ink-500">·</span>
                            <span class="font-mono">{{ variant.sku }}</span>
                        </template>
                        <span class="text-ink-500">·</span>
                        <Link :href="urls.productEdit" class="text-ink-700 hover:text-ink-900">{{ product.name }}</Link>
                    </div>
                </template>
                <template #actions>
                    <div class="inline-flex items-center h-8 border border-line-strong rounded-md bg-surface overflow-hidden">
                        <Link
                            v-if="variant.prev_url"
                            :href="variant.prev_url"
                            :aria-label="t('products.variant_prev')"
                            class="flex items-center justify-center h-full w-9 text-ink-700 hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-sage/35"
                        ><Icon name="chevronLeft" cls="sm" /></Link>
                        <span v-else class="flex items-center justify-center h-full w-9 text-ink-300" :aria-label="t('products.variant_prev')"><Icon name="chevronLeft" cls="sm" /></span>

                        <span class="px-3.5 text-[12.5px] text-ink-900 border-x border-line whitespace-nowrap self-stretch flex items-center">
                            {{ t('products.variant_position', { position: variant.position, total: variant.total }) }}
                        </span>

                        <Link
                            v-if="variant.next_url"
                            :href="variant.next_url"
                            :aria-label="t('products.variant_next')"
                            class="flex items-center justify-center h-full w-9 text-ink-700 hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-sage/35"
                        ><Icon name="chevronRight" cls="sm" /></Link>
                        <span v-else class="flex items-center justify-center h-full w-9 text-ink-300" :aria-label="t('products.variant_next')"><Icon name="chevronRight" cls="sm" /></span>
                    </div>
                    <Button v-if="canDelete" icon="trash" class="!text-danger" @click="confirmOpen = true">
                        {{ t('common.delete') }}
                    </Button>
                    <Tooltip v-else :text="deleteBlockedText">
                        <span class="inline-flex cursor-not-allowed">
                            <Button icon="trash" class="!text-danger pointer-events-none" disabled>
                                {{ t('common.delete') }}
                            </Button>
                        </span>
                    </Tooltip>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" :variant="variant" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <PricingEditor
                            :prices="variant.prices"
                            :currencies="currencies"
                            :customer-groups="customerGroups"
                            :store-url="urls.pricesStore"
                        />

                        <VariantMediaPicker
                            :pool="mediaPool"
                            :sync-url="urls.mediaSync"
                        />

                        <InventoryCard
                            :values="details"
                            :stock="variant.stock"
                            :adjust-url="urls.stockAdjust"
                            :errors="detailsErrors"
                        />

                        <ShippingCard
                            :values="details"
                            :measurements="measurements"
                            :errors="detailsErrors"
                        />

                        <IdentifiersCard
                            :values="details"
                            :errors="detailsErrors"
                        />

                        <TaxCard
                            :values="details"
                            :tax-classes="taxClasses"
                            :errors="detailsErrors"
                        />

                        <AttributeFields
                            v-if="attributeGroups.length"
                            :groups="attributeGroups"
                            :values="details"
                            :errors="detailsErrors"
                            :languages="languages"
                            :description="t('products.variant_attributes_description')"
                        />

                        <PageZone region="main" position="after" :variant="variant" />
                    </div>

                    <!-- Sidebar -->
                    <aside>
                        <div class="lg:sticky lg:top-[60px] flex flex-col gap-4">
                            <SideCard :title="t('products.side_variant_status')">
                                <div class="flex items-center gap-2.5">
                                    <Toggle :on="enabled" @toggle="toggleEnabled" />
                                    <span class="text-[12.5px] text-ink-900">
                                        {{ enabled ? t('products.variants_state_enabled') : t('products.variants_state_disabled') }}
                                    </span>
                                </div>
                                <div class="text-[11.5px] text-ink-500 mt-2">{{ t('products.side_variant_status_hint') }}</div>
                            </SideCard>

                            <SideCard :title="t('products.side_variant_axes')">
                                <div v-if="!variant.axes.length" class="text-[11.5px] text-ink-500">
                                    {{ t('products.side_variant_axes_empty') }}
                                </div>
                                <div v-else class="flex flex-col gap-2">
                                    <div v-for="axis in variant.axes" :key="axis.option" class="flex items-center gap-2 text-xs">
                                        <span class="text-ink-500 min-w-[68px]">{{ axis.option }}</span>
                                        <span class="inline-flex items-center px-2 h-[22px] rounded-full bg-surface-2 border border-line text-ink-900 text-[11.5px] font-medium whitespace-nowrap">
                                            {{ axis.value }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-line">
                                    <Link :href="`${urls.productEdit}#product-options`" class="inline-flex items-center gap-1 text-[11.5px] text-sage-ink hover:text-ink-900">
                                        <Icon name="settings" cls="sm" />
                                        {{ t('products.side_variant_axes_manage') }}
                                    </Link>
                                </div>
                            </SideCard>

                            <SideCard :title="t('products.side_activity')">
                                <template #actions>
                                    <a :href="urls.activityLog" class="text-[11.5px] font-medium text-ink-500 hover:text-ink-900">{{ t('products.side_activity_see_all') }}</a>
                                </template>
                                <ActivityTimeline v-if="activities.length" :events="timelineEvents" :reverse="false" />
                                <div v-else class="text-[11.5px] text-ink-500">{{ t('products.side_activity_empty') }}</div>
                            </SideCard>

                            <PageZone region="sidebar" position="after" :variant="variant" />
                        </div>
                    </aside>
                </div>
            </div>

            <ConfirmDialog
                v-model:open="confirmOpen"
                :title="t('products.variant_delete_title')"
                :description="t('products.variant_delete_confirm')"
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
