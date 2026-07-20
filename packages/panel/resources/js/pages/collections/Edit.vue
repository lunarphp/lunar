<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import AttributeFields, { type AttributeGroup } from '../../components/AttributeFields.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftConflictDialog from '../../components/DraftConflictDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import MediaManager from '../../components/MediaManager.vue';
import { type MediaItem } from '../../components/MediaEditDialog.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import ParentCollectionPicker, { type ParentOption } from '../../components/ParentCollectionPicker.vue';
import Section from '../../components/Section.vue';
import Select from '../../components/Select.vue';
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
}

const props = defineProps<{
    collection: {
        id: number;
        name: Record<string, string>;
        display_name: string | null;
        handle: string;
        status: string;
        status_label: string;
        sort: string;
        short_description: Record<string, string>;
        description: Record<string, string>;
        thumbnail: string | null;
        group_id: number;
        parent: ParentOption | null;
        products_count: number;
        descendants_count: number;
        created_at: string;
        updated_at: string;
    };
    draft: DraftState | null;
    languages: LanguageOption[];
    groups: { id: number; name: string }[];
    collectionUrls: UrlRow[];
    media: MediaItem[];
    attributeGroups: AttributeGroup[];
    attributeValues: Record<string, unknown>;
    storefrontUrl: string | null;
    activities: ActivityEntry[];
    urls: {
        index: string;
        update: string;
        destroy: string;
        move: string;
        draft: string;
        draftCommit: string;
        urlsStore: string;
        mediaStore: string;
        mediaReorder: string;
        collectionsSearch: string;
    };
}>();

const { t, te } = useI18n();

const initials = (): string => props.collection.display_name?.trim().slice(0, 1).toUpperCase() || '?';

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.catalog') },
    { label: t('nav.collections'), href: props.urls.index },
    { label: props.collection.display_name ?? '', current: true },
]);

// The whole form is draft-backed: dirty fields autosave server-side and
// commit with field-level conflict detection. Hierarchy (group/parent) is
// deliberately NOT drafted — moves restructure a shared tree and apply
// immediately through the move endpoint below.
const draftForm = useEditDraft({
    initial: {
        name: { ...props.collection.name },
        handle: props.collection.handle,
        status: props.collection.status,
        sort: props.collection.sort,
        short_description: { ...props.collection.short_description },
        description: { ...props.collection.description },
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
    { value: 'published', label: t('collections.status_published'), tone: 'sage' as const },
    { value: 'draft', label: t('collections.status_draft'), tone: 'warn' as const },
    { value: 'archived', label: t('collections.status_archived'), tone: 'neutral' as const },
]);

const statusHelp = computed(() => {
    const status = details.status as string;

    return status === 'published'
        ? t('collections.status_published_help')
        : status === 'archived'
            ? t('collections.status_archived_help')
            : t('collections.status_draft_help');
});

const statusBadge = computed(() => ({
    tone: props.collection.status === 'published'
        ? ('sage' as const)
        : props.collection.status === 'draft'
            ? ('warn' as const)
            : ('archived' as const),
    label: props.collection.status_label,
}));

// Hierarchy: immediate move endpoint, confirmed before applying because the
// whole subtree moves with the collection.
const moveGroupId = ref<number | null>(props.collection.group_id);
const moveParent = ref<ParentOption | null>(props.collection.parent);
const moveConfirmOpen = ref(false);
const moving = ref(false);

// Hierarchies never cross groups, so a group change resets the parent —
// back to the saved parent when returning to the saved group.
watch(moveGroupId, (groupId) => {
    moveParent.value = groupId === props.collection.group_id ? props.collection.parent : null;
});

const moveDirty = computed(() =>
    moveGroupId.value !== props.collection.group_id
    || (moveParent.value?.id ?? null) !== (props.collection.parent?.id ?? null));

const applyMove = (): void => {
    moveConfirmOpen.value = false;

    router.put(
        props.urls.move,
        {
            collection_group_id: moveGroupId.value,
            parent_id: moveParent.value?.id ?? null,
        },
        {
            preserveScroll: true,
            onStart: () => {
                moving.value = true;
            },
            onFinish: () => {
                moving.value = false;
            },
        },
    );
};

// Delete confirmation
const confirmOpen = ref(false);

const confirmDestroy = (): void => {
    confirmOpen.value = false;
    router.delete(`${props.urls.destroy}?reparent=1`);
};

const activityLabel = (description: string): string => {
    const key = `collections.activity_${description.replaceAll('-', '_')}`;

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
        <div data-screen-label="Collection edit" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <DraftActions :form="draftForm" />
                </template>
            </Breadcrumbs>

            <PageHeader :title="collection.display_name ?? ''">
                <template #icon>
                    <div class="w-11 h-11 rounded-md overflow-hidden shrink-0 bg-surface-2 border border-line grid place-items-center text-ink-700 text-[13px] font-semibold">
                        <img v-if="collection.thumbnail" :src="collection.thumbnail" :alt="collection.display_name ?? ''" class="w-full h-full object-cover" />
                        <span v-else>{{ initials() }}</span>
                    </div>
                </template>
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <StatusBadge :tone="statusBadge.tone" dot>{{ statusBadge.label }}</StatusBadge>
                        <span class="text-ink-500">·</span>
                        <span class="font-mono">{{ collection.handle }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('collections.descendants_count', { count: collection.descendants_count }) }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ t('collections.products_count', { count: collection.products_count }) }}</span>
                    </div>
                </template>
                <template #actions>
                    <Button icon="trash" class="!text-danger" @click="confirmOpen = true">{{ t('collections.delete_collection') }}</Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" :collection="collection" />

                <div class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div class="min-w-0">
                        <form @submit.prevent="submitDetails">
                            <Section :title="t('collections.section_basics')">
                                <template #desc>{{ t('collections.section_basics_description') }}</template>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <FieldLabel required>{{ t('collections.field_name') }}</FieldLabel>
                                        <TranslatedInput
                                            v-model="details.name"
                                            :languages="languages"
                                            kind="text"
                                            :invalid="!!detailsErrors.name"
                                        />
                                        <div v-if="detailsErrors.name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.name }}</div>
                                    </div>
                                    <div>
                                        <FieldLabel for="collection-handle" :hint="t('collections.field_handle_hint')">{{ t('collections.field_handle') }}</FieldLabel>
                                        <TextInput id="collection-handle" v-model="details.handle" mono :invalid="!!detailsErrors.handle" />
                                        <div v-if="detailsErrors.handle" class="mt-1 text-[11px] text-danger">{{ detailsErrors.handle }}</div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <FieldLabel>{{ t('collections.field_short_description') }}</FieldLabel>
                                    <TranslatedInput
                                        v-model="details.short_description"
                                        :languages="languages"
                                        kind="text"
                                        :invalid="!!detailsErrors.short_description"
                                    />
                                    <div v-if="detailsErrors.short_description" class="mt-1 text-[11px] text-danger">{{ detailsErrors.short_description }}</div>
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('collections.field_short_description_hint') }}</div>
                                </div>
                                <div>
                                    <FieldLabel>{{ t('collections.field_description') }}</FieldLabel>
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

                        <Section :title="t('collections.section_hierarchy')">
                            <template #desc>{{ t('collections.section_hierarchy_description') }}</template>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <FieldLabel for="collection-group" required>{{ t('collections.field_group') }}</FieldLabel>
                                    <Select id="collection-group" v-model="moveGroupId">
                                        <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                                    </Select>
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('collections.hierarchy_group_hint') }}</div>
                                </div>
                                <div>
                                    <FieldLabel>{{ t('collections.field_parent') }}</FieldLabel>
                                    <ParentCollectionPicker
                                        v-model="moveParent"
                                        :search-url="urls.collectionsSearch"
                                        :group-id="moveGroupId"
                                        :exclude-id="collection.id"
                                    />
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('collections.field_parent_hint') }}</div>
                                </div>
                            </div>
                            <div v-if="moveDirty" class="mt-3 pt-3 border-t border-line">
                                <Button variant="primary" :disabled="moving" @click="moveConfirmOpen = true">
                                    {{ t('collections.hierarchy_apply') }}
                                </Button>
                            </div>
                        </Section>

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
                            :description="t('collections.attributes_description')"
                        />

                        <UrlSlugs
                            :urls="collectionUrls"
                            :languages="languages"
                            :store-url="urls.urlsStore"
                            path-prefix="/collections/"
                            :storefront-url="storefrontUrl"
                        />

                        <PageZone region="main" position="after" :collection="collection" />
                    </div>

                    <!-- Sidebar -->
                    <aside>
                        <div class="lg:sticky lg:top-[60px] flex flex-col gap-4">
                            <SideCard :title="t('collections.side_status')">
                                <StatusSegmentedControl v-model="details.status" :options="statusOptions" />
                                <div class="text-[11.5px] text-ink-500 mt-2.5">{{ statusHelp }}</div>
                            </SideCard>

                            <SideCard :title="t('collections.side_usage')">
                                <div class="text-xs text-ink-700">
                                    <div class="flex items-baseline gap-1.5 mb-2">
                                        <span class="text-2xl font-semibold tracking-[-0.02em] text-ink-900 [font-variant-numeric:tabular-nums]">{{ collection.products_count }}</span>
                                        <span class="text-ink-500 text-[11.5px]">{{ t('collections.side_usage_products') }}</span>
                                    </div>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-lg font-semibold tracking-[-0.02em] text-ink-900 [font-variant-numeric:tabular-nums]">{{ collection.descendants_count }}</span>
                                        <span class="text-ink-500 text-[11.5px]">{{ t('collections.side_usage_descendants') }}</span>
                                    </div>
                                    <div class="h-px bg-line my-3" />
                                    <div class="text-[11.5px] text-ink-500">
                                        {{ t('collections.last_updated') }}: {{ new Date(collection.updated_at).toLocaleString() }}
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('collections.side_activity')">
                                <ActivityTimeline v-if="activities.length" :events="timelineEvents" :reverse="false" />
                                <div v-else class="text-[11.5px] text-ink-500">{{ t('collections.side_activity_empty') }}</div>
                            </SideCard>

                            <PageZone region="sidebar" position="after" :collection="collection" />
                        </div>
                    </aside>
                </div>
            </div>

            <ConfirmDialog
                v-model:open="confirmOpen"
                :title="t('collections.confirm_delete_collection_title')"
                :description="t('collections.confirm_delete_collection')"
                tone="danger"
                :confirm-label="t('common.delete')"
                @confirm="confirmDestroy"
            />

            <ConfirmDialog
                v-model:open="moveConfirmOpen"
                :title="t('collections.hierarchy_confirm_title')"
                :description="t('collections.hierarchy_confirm')"
                :confirm-label="t('collections.hierarchy_apply')"
                @confirm="applyMove"
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
