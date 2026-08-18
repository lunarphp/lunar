<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import AttributeFields, { type AttributeGroup } from '../../components/AttributeFields.vue';
import AttributePicker, { type PickerGroup } from '../../components/AttributePicker.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftConflictDialog from '../../components/DraftConflictDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import MediaGroups from '../../components/MediaGroups.vue';
import { type MediaGroup } from '../../components/media';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import Section from '../../components/Section.vue';
import Select from '../../components/Select.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import StatusSegmentedControl from '../../components/StatusSegmentedControl.vue';
import Textarea from '../../components/Textarea.vue';
import TextInput from '../../components/TextInput.vue';
import { type LanguageOption } from '../../components/TranslatedInput.vue';
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
    productType: {
        id: number;
        name: string;
        handle: string;
        status: 'active' | 'draft';
        description: string | null;
        default_tax_class_id: number | null;
        products_count: number;
        created_at: string;
        updated_at: string;
    };
    draft: DraftState | null;
    mediaGroups: MediaGroup[];
    languages: LanguageOption[];
    attributeGroups: AttributeGroup[];
    attributeValues: Record<string, unknown>;
    productAttributeGroups: PickerGroup[];
    variantAttributeGroups: PickerGroup[];
    productAttributeIds: number[];
    variantAttributeIds: number[];
    taxClasses: { id: number; name: string }[];
    activities: ActivityEntry[];
    urls: {
        index: string;
        activityLog: string;
        update: string;
        destroy: string;
        draft: string;
        draftCommit: string;
        manageAttributes: string;
    };
}>();

const { t, te } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.catalog') },
    { label: t('nav.product_types'), href: props.urls.index },
    { label: props.productType.name, current: true },
]);

// The whole form is draft-backed: dirty fields autosave server-side and
// commit with field-level conflict detection instead of a last-write-wins
// PUT. The save cluster lives in the sticky breadcrumb bar.
const draftForm = useEditDraft({
    initial: {
        name: props.productType.name,
        handle: props.productType.handle,
        status: props.productType.status as string,
        description: props.productType.description ?? '',
        default_tax_class_id: props.productType.default_tax_class_id,
        product_attribute_ids: [...props.productAttributeIds],
        variant_attribute_ids: [...props.variantAttributeIds],
        // The type's own field values ride the same draft under attribute:{handle} keys.
        ...props.attributeValues,
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
    { value: 'active', label: t('product-types.status_active'), tone: 'sage' as const },
    { value: 'draft', label: t('product-types.status_draft'), tone: 'warn' as const },
]);

const statusBadge = computed(() => ({
    tone: props.productType.status === 'active' ? ('sage' as const) : ('warn' as const),
    label: props.productType.status === 'active' ? t('product-types.status_active') : t('product-types.status_draft'),
}));

// Delete confirmation
const confirmOpen = ref(false);

const destroyProductType = (): void => {
    confirmOpen.value = true;
};

const confirmDestroy = (): void => {
    confirmOpen.value = false;
    router.delete(props.urls.destroy);
};

const activityLabel = (description: string): string => {
    const key = `product-types.activity_${description.replaceAll('-', '_')}`;

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
        <div data-screen-label="Product type edit" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <DraftActions :form="draftForm" />
                </template>
            </Breadcrumbs>

            <PageHeader :title="productType.name">
                <template #icon>
                    <div class="w-11 h-11 rounded-md shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700">
                        <Icon name="boxes" />
                    </div>
                </template>
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <StatusBadge :tone="statusBadge.tone" dot>{{ statusBadge.label }}</StatusBadge>
                        <span class="text-ink-500">·</span>
                        <span class="font-mono">{{ productType.handle }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('product-types.header_attributes', { product: productAttributeIds.length, variant: variantAttributeIds.length }) }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('product-types.header_products', { count: productType.products_count }) }}</span>
                    </div>
                </template>
                <template #actions>
                    <Button icon="trash" class="!text-danger" @click="destroyProductType">{{ t('product-types.delete_product_type') }}</Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" :product-type="productType" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <form @submit.prevent="submitDetails">
                            <Section :title="t('product-types.section_basics')">
                                <template #desc>{{ t('product-types.section_basics_description') }}</template>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <FieldLabel for="product-type-name" required>{{ t('product-types.field_name') }}</FieldLabel>
                                        <TextInput id="product-type-name" v-model="details.name" :invalid="!!detailsErrors.name" />
                                        <div v-if="detailsErrors.name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.name }}</div>
                                    </div>
                                    <div>
                                        <FieldLabel for="product-type-handle" :hint="t('product-types.field_handle_hint')">{{ t('product-types.field_handle') }}</FieldLabel>
                                        <TextInput id="product-type-handle" v-model="details.handle" mono :invalid="!!detailsErrors.handle" />
                                        <div v-if="detailsErrors.handle" class="mt-1 text-[11px] text-danger">{{ detailsErrors.handle }}</div>
                                    </div>
                                </div>
                                <div>
                                    <FieldLabel for="product-type-description">{{ t('product-types.field_description') }}</FieldLabel>
                                    <Textarea id="product-type-description" v-model="details.description" :rows="3" :invalid="!!detailsErrors.description" />
                                    <div v-if="detailsErrors.description" class="mt-1 text-[11px] text-danger">{{ detailsErrors.description }}</div>
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('product-types.field_description_help') }}</div>
                                </div>
                            </Section>
                        </form>

                        <MediaGroups :groups="mediaGroups" />

                        <AttributeFields
                            :groups="attributeGroups"
                            :values="details"
                            :errors="detailsErrors"
                            :languages="languages"
                            :description="t('product-types.attributes_description')"
                        />

                        <AttributePicker
                            v-model="details.product_attribute_ids"
                            :groups="productAttributeGroups"
                            :title="t('product-types.section_product_attributes')"
                            :description="t('product-types.section_product_attributes_description')"
                            :manage-url="urls.manageAttributes"
                        />

                        <AttributePicker
                            v-model="details.variant_attribute_ids"
                            :groups="variantAttributeGroups"
                            :title="t('product-types.section_variant_attributes')"
                            :description="t('product-types.section_variant_attributes_description')"
                            :manage-url="urls.manageAttributes"
                        />

                        <PageZone region="main" position="after" :product-type="productType" />
                    </div>

                    <!-- Sidebar -->
                    <aside>
                        <div class="lg:sticky lg:top-[60px] flex flex-col gap-4">
                            <SideCard :title="t('product-types.side_status')">
                                <StatusSegmentedControl v-model="details.status" :options="statusOptions" />
                                <div class="text-[11.5px] text-ink-500 mt-2.5">
                                    {{ details.status === 'active' ? t('product-types.status_active_help') : t('product-types.status_draft_help') }}
                                </div>
                            </SideCard>

                            <SideCard :title="t('product-types.side_usage')">
                                <div class="text-xs text-ink-700">
                                    <div class="flex items-baseline gap-1.5 mb-2">
                                        <span class="text-2xl font-semibold tracking-[-0.02em] text-ink-900 [font-variant-numeric:tabular-nums]">{{ productType.products_count }}</span>
                                        <span class="text-ink-500 text-[11.5px]">{{ t('product-types.side_usage_products') }}</span>
                                    </div>
                                    <div class="h-px bg-line my-3" />
                                    <div class="text-[11.5px] text-ink-500">
                                        {{ t('product-types.last_updated') }}: {{ new Date(productType.updated_at).toLocaleString() }}
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('product-types.side_defaults')">
                                <FieldLabel for="product-type-tax-class">{{ t('product-types.field_default_tax_class') }}</FieldLabel>
                                <Select id="product-type-tax-class" v-model="details.default_tax_class_id" :invalid="!!detailsErrors.default_tax_class_id">
                                    <option :value="null">{{ t('product-types.no_tax_class') }}</option>
                                    <option v-for="taxClass in taxClasses" :key="taxClass.id" :value="taxClass.id">{{ taxClass.name }}</option>
                                </Select>
                                <div v-if="detailsErrors.default_tax_class_id" class="mt-1 text-[11px] text-danger">{{ detailsErrors.default_tax_class_id }}</div>
                                <div class="text-[11.5px] text-ink-500 mt-2">{{ t('product-types.field_default_tax_class_hint') }}</div>
                            </SideCard>

                            <SideCard :title="t('product-types.side_activity')">
                                <template #actions>
                                    <a :href="urls.activityLog" class="text-[11.5px] font-medium text-ink-500 hover:text-ink-900">{{ t('product-types.side_activity_see_all') }}</a>
                                </template>
                                <ActivityTimeline v-if="activities.length" :events="timelineEvents" :reverse="false" />
                                <div v-else class="text-[11.5px] text-ink-500">{{ t('product-types.side_activity_empty') }}</div>
                            </SideCard>

                            <PageZone region="sidebar" position="after" :product-type="productType" />
                        </div>
                    </aside>
                </div>
            </div>

            <ConfirmDialog
                v-model:open="confirmOpen"
                :title="t('product-types.confirm_delete_title')"
                :description="t('product-types.confirm_delete')"
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
