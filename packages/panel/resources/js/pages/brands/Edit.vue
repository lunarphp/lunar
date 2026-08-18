<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import AttributeFields, { type AttributeGroup } from '../../components/AttributeFields.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import CollectionPicker, { type CollectionOption } from '../../components/CollectionPicker.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftConflictDialog from '../../components/DraftConflictDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import MediaGroups from '../../components/MediaGroups.vue';
import { type MediaGroup } from '../../components/media';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import Section from '../../components/Section.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import StatusSegmentedControl from '../../components/StatusSegmentedControl.vue';
import TextInput from '../../components/TextInput.vue';
import TranslatedInput, { type LanguageOption } from '../../components/TranslatedInput.vue';
import UrlSlugs, { type UrlRow } from '../../components/UrlSlugs.vue';
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
    brand: {
        id: number;
        name: string;
        handle: string;
        status: 'active' | 'draft';
        short_description: Record<string, string>;
        description: Record<string, string>;
        thumbnail: string | null;
        products_count: number;
        collections_count: number;
        created_at: string;
        updated_at: string;
    };
    draft: DraftState | null;
    languages: LanguageOption[];
    mediaGroups: MediaGroup[];
    attributeGroups: AttributeGroup[];
    attributeValues: Record<string, unknown>;
    collections: CollectionOption[];
    brandUrls: UrlRow[];
    storefrontUrl: string | null;
    activities: ActivityEntry[];
    urls: {
        index: string;
        activityLog: string;
        update: string;
        destroy: string;
        draft: string;
        draftCommit: string;
        urlsStore: string;
        collectionsSearch: string;
    };
}>();

const { t, te } = useI18n();

const initials = (): string => props.brand.name?.trim().slice(0, 1).toUpperCase() || '?';

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.catalog') },
    { label: t('nav.brands'), href: props.urls.index },
    { label: props.brand.name, current: true },
]);

// The whole form is draft-backed: dirty fields autosave server-side and
// commit with field-level conflict detection instead of a last-write-wins
// PUT. The save cluster lives in the sticky breadcrumb bar.
const draftForm = useEditDraft({
    initial: {
        name: props.brand.name,
        handle: props.brand.handle,
        status: props.brand.status as string,
        short_description: { ...props.brand.short_description },
        description: { ...props.brand.description },
        collection_ids: props.collections.map((collection) => collection.id),
        // Mapped attribute values ride the same draft under attribute:{handle} keys.
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
    { value: 'active', label: t('brands.status_active'), tone: 'sage' as const },
    { value: 'draft', label: t('brands.status_draft'), tone: 'warn' as const },
]);

const statusBadge = computed(() => ({
    tone: props.brand.status === 'active' ? ('sage' as const) : ('warn' as const),
    label: props.brand.status === 'active' ? t('brands.status_active') : t('brands.status_draft'),
}));

// Delete confirmation
const confirmOpen = ref(false);

const destroyBrand = (): void => {
    confirmOpen.value = true;
};

const confirmDestroy = (): void => {
    confirmOpen.value = false;
    router.delete(props.urls.destroy);
};

const activityLabel = (description: string): string => {
    const key = `brands.activity_${description.replaceAll('-', '_')}`;

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
        <div data-screen-label="Brand edit" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <DraftActions :form="draftForm" />
                </template>
            </Breadcrumbs>

            <PageHeader :title="brand.name">
                <template #icon>
                    <div class="w-11 h-11 rounded-md overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700 text-[13px] font-semibold">
                        <img v-if="brand.thumbnail" :src="brand.thumbnail" :alt="brand.name" class="w-full h-full object-cover" />
                        <span v-else>{{ initials() }}</span>
                    </div>
                </template>
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <StatusBadge :tone="statusBadge.tone" dot>{{ statusBadge.label }}</StatusBadge>
                        <span class="text-ink-500">·</span>
                        <span class="font-mono">{{ brand.handle }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('brands.header_collections', { count: brand.collections_count }) }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('brands.header_products', { count: brand.products_count }) }}</span>
                    </div>
                </template>
                <template #actions>
                    <Button icon="trash" class="!text-danger" @click="destroyBrand">{{ t('brands.delete_brand') }}</Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" :brand="brand" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <form @submit.prevent="submitDetails">
                            <Section :title="t('brands.section_basics')">
                                <template #desc>{{ t('brands.section_basics_description') }}</template>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <FieldLabel for="brand-name" required>{{ t('brands.field_name') }}</FieldLabel>
                                        <TextInput id="brand-name" v-model="details.name" :invalid="!!detailsErrors.name" />
                                        <div v-if="detailsErrors.name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.name }}</div>
                                    </div>
                                    <div>
                                        <FieldLabel for="brand-handle" :hint="t('brands.field_handle_hint')">{{ t('brands.field_handle') }}</FieldLabel>
                                        <TextInput id="brand-handle" v-model="details.handle" mono :invalid="!!detailsErrors.handle" />
                                        <div v-if="detailsErrors.handle" class="mt-1 text-[11px] text-danger">{{ detailsErrors.handle }}</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <FieldLabel>{{ t('brands.field_short_description') }}</FieldLabel>
                                    <TranslatedInput
                                        v-model="details.short_description"
                                        :languages="languages"
                                        kind="text"
                                        :invalid="!!detailsErrors.short_description"
                                    />
                                    <div v-if="detailsErrors.short_description" class="mt-1 text-[11px] text-danger">{{ detailsErrors.short_description }}</div>
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('brands.field_short_description_hint') }}</div>
                                </div>
                                <div>
                                    <FieldLabel>{{ t('brands.field_description') }}</FieldLabel>
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
                            :groups="attributeGroups"
                            :values="details"
                            :errors="detailsErrors"
                            :languages="languages"
                            :description="t('brands.attributes_description')"
                        />

                        <UrlSlugs
                            :urls="brandUrls"
                            :languages="languages"
                            :store-url="urls.urlsStore"
                            path-prefix="/brands/"
                            :storefront-url="storefrontUrl"
                        />

                        <PageZone region="main" position="after" :brand="brand" />
                    </div>

                    <!-- Sidebar -->
                    <aside>
                        <div class="lg:sticky lg:top-[60px] flex flex-col gap-4">
                            <SideCard :title="t('brands.side_status')">
                                <StatusSegmentedControl v-model="details.status" :options="statusOptions" />
                                <div class="text-[11.5px] text-ink-500 mt-2.5">
                                    {{ details.status === 'active' ? t('brands.status_active_help') : t('brands.status_draft_help') }}
                                </div>
                            </SideCard>

                            <SideCard :title="t('brands.side_collections')">
                                <CollectionPicker
                                    v-model="details.collection_ids"
                                    :known="collections"
                                    :search-url="urls.collectionsSearch"
                                />
                                <div class="text-[11.5px] text-ink-500 mt-2">{{ t('brands.side_collections_hint') }}</div>
                            </SideCard>

                            <SideCard :title="t('brands.side_usage')">
                                <div class="text-xs text-ink-700">
                                    <div class="flex items-baseline gap-1.5 mb-2">
                                        <span class="text-2xl font-semibold tracking-[-0.02em] text-ink-900 [font-variant-numeric:tabular-nums]">{{ brand.products_count }}</span>
                                        <span class="text-ink-500 text-[11.5px]">{{ t('brands.side_usage_products') }}</span>
                                    </div>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-lg font-semibold tracking-[-0.02em] text-ink-900 [font-variant-numeric:tabular-nums]">{{ brand.collections_count }}</span>
                                        <span class="text-ink-500 text-[11.5px]">{{ t('brands.side_usage_collections') }}</span>
                                    </div>
                                    <div class="h-px bg-line my-3" />
                                    <div class="text-[11.5px] text-ink-500">
                                        {{ t('brands.last_updated') }}: {{ new Date(brand.updated_at).toLocaleString() }}
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('brands.side_activity')">
                                <template #actions>
                                    <a :href="urls.activityLog" class="text-[11.5px] font-medium text-ink-500 hover:text-ink-900">{{ t('brands.side_activity_see_all') }}</a>
                                </template>
                                <ActivityTimeline v-if="activities.length" :events="timelineEvents" :reverse="false" />
                                <div v-else class="text-[11.5px] text-ink-500">{{ t('brands.side_activity_empty') }}</div>
                            </SideCard>

                            <PageZone region="sidebar" position="after" :brand="brand" />
                        </div>
                    </aside>
                </div>
            </div>

            <ConfirmDialog
                v-model:open="confirmOpen"
                :title="t('brands.confirm_delete_brand_title')"
                :description="t('brands.confirm_delete_brand')"
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
