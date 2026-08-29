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
import TargetChipList, { type TargetChip } from '../../components/TargetChipList.vue';
import TargetPickerDialog, { type TargetOption } from '../../components/TargetPickerDialog.vue';
import TextInput from '../../components/TextInput.vue';
import Toggle from '../../components/Toggle.vue';
import UsageMeter from '../../components/UsageMeter.vue';
import BuyXGetYForm from '../../components/DiscountTypeForms/BuyXGetYForm.vue';
import DiscountConditions from '../../components/DiscountConditions.vue';
import FixedAmountOffForm from '../../components/DiscountTypeForms/FixedAmountOffForm.vue';
import PercentageOffForm from '../../components/DiscountTypeForms/PercentageOffForm.vue';
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
    targets: Record<string, Record<string, number[]>>;
    targetChips: Record<string, Record<string, TargetChip[]>>;
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
        targetSearch: string;
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
        ...props.targets,
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
    PercentageOffForm,
    FixedAmountOffForm,
    BuyXGetYForm,
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

// Only the buckets this discount type declares get a block, so a cart-level
// type renders none of the targeting UI at all.
const TARGET_PREFIX = 'target:';

// Customers are an audience restriction, not a product target — they hang off
// the limitation bucket in core and get their own sidebar card here.
const PRODUCT_KINDS = ['products', 'variants', 'collections', 'brands'];

// The draft values carry dynamic availability and target keys alongside the
// declared ones. Same reactive object, indexed without widening the whole
// form's type.
const targetValues = details as unknown as Record<string, Record<string, number[]>>;

const chips = ref<Record<string, Record<string, TargetChip[]>>>(
    JSON.parse(JSON.stringify(props.targetChips)) as Record<string, Record<string, TargetChip[]>>,
);

const bucketKinds = (bucket: string): string[] =>
    PRODUCT_KINDS.filter((kind) => Object.prototype.hasOwnProperty.call(props.targets[TARGET_PREFIX + bucket] ?? {}, kind));

const visibleBuckets = computed(() => props.type.buckets.filter((bucket) => bucketKinds(bucket).length > 0));

const pickerBucket = ref<string | null>(null);
const pickerOpen = computed({
    get: () => pickerBucket.value !== null,
    set: (value: boolean) => {
        if (!value) {
            pickerBucket.value = null;
        }
    },
});

const pickerKinds = computed(() => (pickerBucket.value ? bucketKinds(pickerBucket.value) : []));

const addTargets = (added: TargetOption[]): void => {
    const bucket = pickerBucket.value;

    if (!bucket) {
        return;
    }

    const field = TARGET_PREFIX + bucket;
    const value = { ...targetValues[field] };
    const bucketChips = { ...(chips.value[field] ?? {}) };

    added.forEach((target) => {
        if ((value[target.kind] ?? []).includes(target.id)) {
            return;
        }

        value[target.kind] = [...(value[target.kind] ?? []), target.id];
        bucketChips[target.kind] = [
            ...(bucketChips[target.kind] ?? []),
            { id: target.id, label: target.label, hint: target.hint },
        ];
    });

    targetValues[field] = value;
    chips.value = { ...chips.value, [field]: bucketChips };
};

const removeTarget = (bucket: string, kind: string, id: number): void => {
    const field = TARGET_PREFIX + bucket;
    const value = { ...targetValues[field] };

    value[kind] = (value[kind] ?? []).filter((row) => row !== id);
    targetValues[field] = value;

    chips.value = {
        ...chips.value,
        [field]: {
            ...chips.value[field],
            [kind]: (chips.value[field]?.[kind] ?? []).filter((chip) => chip.id !== id),
        },
    };
};

// The customer_discount pivot has no bucket column, so eligible customers are
// stored under limitation but read as an audience rather than a product target.
const customerField = `${TARGET_PREFIX}limitation`;
const customerChips = computed(() => chips.value[customerField]?.customers ?? []);

const customerPickerOpen = ref(false);

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
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <DraftActions :form="draftForm" />
                </template>
            </Breadcrumbs>

            <PageHeader :title="discount.name">
                <template #icon>
                    <Link
                        :href="urls.index"
                        class="text-ink-500 hover:text-ink-900 shrink-0 self-center"
                        :aria-label="t('discounts.back_to_discounts')"
                    >
                        <Icon name="arrowLeft" />
                    </Link>
                </template>
                <template #description>
                    <div class="flex gap-2 items-center flex-wrap">
                        <StatusBadge :tone="statusTone" dot>{{ discount.status_label }}</StatusBadge>
                        <span class="text-ink-500">·</span>
                        <span class="font-mono">{{ discount.handle }}</span>
                        <span class="text-ink-500">·</span>
                        <span>{{ type.label }}</span>
                    </div>
                </template>
                <template #actions>
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
                                <label class="flex items-start gap-3 cursor-pointer self-center">
                                    <Toggle :on="!!details.stop" @toggle="details.stop = !details.stop" />
                                    <div>
                                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('discounts.field_stop') }}</div>
                                        <div class="text-[11px] text-ink-500">{{ t('discounts.field_stop_hint') }}</div>
                                    </div>
                                </label>
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

                        <section v-if="visibleBuckets.length" class="bg-surface border border-line rounded-xl shadow-sm p-5">
                            <div class="pb-4 border-b border-line mb-4">
                                <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('discounts.section_targets') }}</h2>
                                <div class="text-xs text-ink-500 leading-normal">{{ t('discounts.section_targets_description') }}</div>
                            </div>

                            <div class="flex flex-col gap-5">
                                <TargetChipList
                                    v-for="bucket in visibleBuckets"
                                    :key="bucket"
                                    :chips="chips[`target:${bucket}`] ?? {}"
                                    :kinds="bucketKinds(bucket)"
                                    :label="t(`discounts.bucket_${bucket}`)"
                                    :description="t(`discounts.bucket_${bucket}_description`)"
                                    @add="pickerBucket = bucket"
                                    @remove="(kind, id) => removeTarget(bucket, kind, id)"
                                />
                            </div>
                        </section>

                        <section class="bg-surface border border-line rounded-xl shadow-sm p-5">
                            <div class="pb-4 border-b border-line mb-4">
                                <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('discounts.section_conditions') }}</h2>
                                <div class="text-xs text-ink-500 leading-normal">{{ t('discounts.section_conditions_description') }}</div>
                            </div>

                            <DiscountConditions
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

                            <!-- The status badge lives in the page header, not here: it is
                                 derived server-side, so beside the date fields that produce
                                 it it would read as stale until the draft commits. -->
                            <SideCard :title="t('discounts.section_schedule')">
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

                            <SideCard :title="t('discounts.section_customers')">
                                <TargetChipList
                                    :chips="{ customers: customerChips }"
                                    :kinds="['customers']"
                                    :label="t('discounts.bucket_customers')"
                                    :description="t('discounts.bucket_customers_description')"
                                    @add="customerPickerOpen = true"
                                    @remove="(kind, id) => removeTarget('limitation', kind, id)"
                                />
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

            <TargetPickerDialog
                v-model:open="pickerOpen"
                :search-url="urls.targetSearch"
                :bucket="pickerBucket ?? 'limitation'"
                :kinds="pickerKinds"
                @add="addTargets"
            />

            <TargetPickerDialog
                v-model:open="customerPickerOpen"
                :search-url="urls.targetSearch"
                bucket="limitation"
                :kinds="['customers']"
                @add="addTargets"
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
