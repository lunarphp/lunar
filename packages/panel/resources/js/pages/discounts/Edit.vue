<script setup lang="ts">
import { computed, ref, type Component } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../components/ActivityTimeline.vue';
import AvailabilityCard, { type AvailabilityRow } from '../../components/AvailabilityCard.vue';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import DraftActions from '../../components/DraftActions.vue';
import DraftConflictDialog from '../../components/DraftConflictDialog.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';
import SideCard from '../../components/SideCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';
import TextInput from '../../components/TextInput.vue';
import Toggle from '../../components/Toggle.vue';
import UsageMeter from '../../components/UsageMeter.vue';
import RawDataForm from '../../components/DiscountTypeForms/RawDataForm.vue';
import { useEditDraft, type DraftState } from '../../composables/useEditDraft';

const { t } = useI18n();

interface ActivityEntry {
    description: string;
    created_at: string;
    causer_name: string | null;
    avatar: string | null;
    changes: string[];
}

interface DiscountProps {
    id: number;
    name: string;
    handle: string;
    coupon: string | null;
    type: string;
    status: 'active' | 'scheduled' | 'expired' | 'pending';
    status_label: string;
    starts_at: string | null;
    ends_at: string | null;
    priority: number;
    stop: boolean;
    uses: number;
    max_uses: number | null;
    max_uses_per_user: number | null;
    data: Record<string, unknown>;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    discount: DiscountProps;
    type: { class: string; label: string; component: string; buckets: string[] };
    typeRegistered: boolean;
    draft: DraftState | null;
    currencies: { id: number; code: string; name: string; decimal_places: number; default: boolean }[];
    availability: { channels: AvailabilityRow[]; customer_groups: AvailabilityRow[] };
    availabilityValues: Record<string, unknown>;
    activities: ActivityEntry[];
    urls: {
        index: string;
        activityLog: string;
        update: string;
        destroy: string;
        draft: string;
        draftCommit: string;
    };
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.sales') },
    { label: t('nav.discounts'), href: props.urls.index },
    { label: props.discount.name, current: true },
]);

// A datetime-local input wants `YYYY-MM-DDTHH:mm`; the server sends ISO.
const toLocalInput = (value: string | null): string => (value ? value.slice(0, 16).replace(' ', 'T') : '');

const draftForm = useEditDraft({
    initial: {
        name: props.discount.name,
        handle: props.discount.handle,
        coupon: props.discount.coupon ?? '',
        starts_at: toLocalInput(props.discount.starts_at),
        ends_at: toLocalInput(props.discount.ends_at),
        priority: props.discount.priority,
        stop: props.discount.stop,
        max_uses: props.discount.max_uses ?? '',
        max_uses_per_user: props.discount.max_uses_per_user ?? '',
        // The type owns this shape, so it drafts as one unit.
        data: { ...props.discount.data },
        ...props.availabilityValues,
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

// First-party type forms resolve locally; anything else comes from the add-on
// component registry, the same path the dashboard widgets take.
const LOCAL_TYPE_FORMS: Record<string, Component> = {
    RawDataForm,
};

const typeFormComponent = computed<Component | null>(() => {
    const name = props.type.component;

    if (LOCAL_TYPE_FORMS[name]) {
        return LOCAL_TYPE_FORMS[name];
    }

    const resolved = window.LunarPanel?.resolveExtensionComponent(name);

    if (!resolved) {
        console.warn(`[lunar-panel] Unresolvable discount type form component [${name}].`);
    }

    return resolved ?? null;
});

const statusTone = computed(() => {
    if (props.discount.status === 'active') {
        return 'sage' as const;
    }

    if (props.discount.status === 'scheduled') {
        return 'neutral' as const;
    }

    return props.discount.status === 'expired' ? ('danger' as const) : ('warn' as const);
});

const confirmOpen = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <PanelLayout>
        <div data-screen-label="Edit discount" class="contents">
            <Breadcrumbs :items="breadcrumbs" />

            <PageHeader :title="discount.name" :description="discount.handle">
                <template #icon>
                    <Link
                        :href="urls.index"
                        class="text-ink-500 hover:text-ink-900 shrink-0 self-center"
                        :aria-label="t('discounts.back_to_discounts')"
                    >
                        <Icon name="arrowLeft" />
                    </Link>
                </template>
                <template #actions>
                    <DraftActions :form="draftForm" />
                    <Button icon="trash" class="!text-danger" @click="confirmOpen = true">
                        <span class="hidden sm:inline">{{ t('discounts.delete_discount') }}</span>
                    </Button>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[1400px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" :discount="discount" />

                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-5">
                    <form class="min-w-0 flex flex-col gap-5" @submit.prevent="submitDetails">
                        <section class="bg-surface border border-line rounded-xl shadow-sm p-5">
                            <div class="pb-4 border-b border-line mb-4">
                                <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('discounts.section_details') }}</h2>
                                <div class="text-xs text-ink-500 leading-normal">{{ t('discounts.section_details_description') }}</div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <FieldLabel for="discount-name" required>{{ t('discounts.field_name') }}</FieldLabel>
                                    <TextInput id="discount-name" v-model="details.name" :invalid="!!detailsErrors.name" />
                                    <div v-if="detailsErrors.name" class="mt-1 text-[11px] text-danger">{{ detailsErrors.name }}</div>
                                </div>
                                <div>
                                    <FieldLabel for="discount-handle" required>{{ t('discounts.field_handle') }}</FieldLabel>
                                    <TextInput id="discount-handle" v-model="details.handle" :invalid="!!detailsErrors.handle" />
                                    <div v-if="detailsErrors.handle" class="mt-1 text-[11px] text-danger">{{ detailsErrors.handle }}</div>
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_handle_hint') }}</div>
                                </div>
                                <div>
                                    <FieldLabel for="discount-priority">{{ t('discounts.field_priority') }}</FieldLabel>
                                    <TextInput id="discount-priority" v-model="details.priority" type="number" min="1" max="100" :invalid="!!detailsErrors.priority" />
                                    <div v-if="detailsErrors.priority" class="mt-1 text-[11px] text-danger">{{ detailsErrors.priority }}</div>
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_priority_hint') }}</div>
                                </div>
                                <div class="flex flex-col justify-center">
                                    <Toggle v-model="details.stop" :label="t('discounts.field_stop')" />
                                    <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_stop_hint') }}</div>
                                </div>
                            </div>
                        </section>

                        <section class="bg-surface border border-line rounded-xl shadow-sm p-5">
                            <div class="pb-4 border-b border-line mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('discounts.section_configuration') }}</h2>
                                    <div class="text-xs text-ink-500 leading-normal">{{ t('discounts.section_configuration_description') }}</div>
                                </div>
                                <StatusBadge tone="neutral">{{ type.label }}</StatusBadge>
                            </div>

                            <div v-if="!typeRegistered" class="mb-3 text-[11.5px] text-warn">{{ t('discounts.type_missing') }}</div>

                            <component
                                :is="typeFormComponent"
                                v-if="typeFormComponent"
                                v-model="details.data"
                                :currencies="currencies"
                                :errors="detailsErrors"
                            />
                        </section>

                        <PageZone region="main" position="after" :discount="discount" />
                    </form>

                    <aside class="min-w-0">
                        <div class="flex flex-col gap-5">
                            <PageZone region="sidebar" position="before" :discount="discount" />

                            <SideCard :title="t('discounts.section_schedule')">
                                <template #actions>
                                    <StatusBadge :tone="statusTone" dot>{{ discount.status_label }}</StatusBadge>
                                </template>
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <FieldLabel for="discount-starts-at" required>{{ t('discounts.field_starts_at') }}</FieldLabel>
                                        <TextInput id="discount-starts-at" v-model="details.starts_at" type="datetime-local" :invalid="!!detailsErrors.starts_at" />
                                        <div v-if="detailsErrors.starts_at" class="mt-1 text-[11px] text-danger">{{ detailsErrors.starts_at }}</div>
                                    </div>
                                    <div>
                                        <FieldLabel for="discount-ends-at">{{ t('discounts.field_ends_at') }}</FieldLabel>
                                        <TextInput id="discount-ends-at" v-model="details.ends_at" type="datetime-local" :invalid="!!detailsErrors.ends_at" />
                                        <div v-if="detailsErrors.ends_at" class="mt-1 text-[11px] text-danger">{{ detailsErrors.ends_at }}</div>
                                        <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_ends_at_hint') }}</div>
                                    </div>
                                </div>
                            </SideCard>

                            <SideCard :title="t('discounts.section_usage')">
                                <div class="flex flex-col gap-3">
                                    <div>
                                        <div class="text-[11.5px] text-ink-500 mb-1">{{ t('discounts.usage_redeemed') }}</div>
                                        <UsageMeter :used="discount.uses" :max="discount.max_uses" />
                                    </div>
                                    <div>
                                        <FieldLabel for="discount-coupon">{{ t('discounts.field_coupon') }}</FieldLabel>
                                        <TextInput id="discount-coupon" v-model="details.coupon" :invalid="!!detailsErrors.coupon" />
                                        <div v-if="detailsErrors.coupon" class="mt-1 text-[11px] text-danger">{{ detailsErrors.coupon }}</div>
                                        <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_coupon_hint') }}</div>
                                    </div>
                                    <div>
                                        <FieldLabel for="discount-max-uses">{{ t('discounts.field_max_uses') }}</FieldLabel>
                                        <TextInput id="discount-max-uses" v-model="details.max_uses" type="number" min="1" :invalid="!!detailsErrors.max_uses" />
                                        <div v-if="detailsErrors.max_uses" class="mt-1 text-[11px] text-danger">{{ detailsErrors.max_uses }}</div>
                                        <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_max_uses_hint') }}</div>
                                    </div>
                                    <div>
                                        <FieldLabel for="discount-max-uses-per-user">{{ t('discounts.field_max_uses_per_user') }}</FieldLabel>
                                        <TextInput id="discount-max-uses-per-user" v-model="details.max_uses_per_user" type="number" min="1" :invalid="!!detailsErrors.max_uses_per_user" />
                                        <div v-if="detailsErrors.max_uses_per_user" class="mt-1 text-[11px] text-danger">{{ detailsErrors.max_uses_per_user }}</div>
                                        <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_max_uses_per_user_hint') }}</div>
                                    </div>
                                </div>
                            </SideCard>

                            <AvailabilityCard
                                :channels="availability.channels"
                                :customer-groups="availability.customer_groups"
                                :values="details"
                            />

                            <SideCard :title="t('discounts.section_activity')">
                                <template #actions>
                                    <a :href="urls.activityLog" class="text-[11.5px] font-medium text-ink-500 hover:text-ink-900">{{ t('discounts.activity_see_all') }}</a>
                                </template>
                                <ActivityTimeline v-if="activities.length" :events="activities" :reverse="false" />
                                <div v-else class="text-[11.5px] text-ink-500">{{ t('discounts.activity_empty') }}</div>
                            </SideCard>

                            <PageZone region="sidebar" position="after" :discount="discount" />
                        </div>
                    </aside>
                </div>
            </div>

            <ConfirmDialog
                v-model:open="confirmOpen"
                :title="t('discounts.delete_discount')"
                :description="t('discounts.confirm_delete_discount')"
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
