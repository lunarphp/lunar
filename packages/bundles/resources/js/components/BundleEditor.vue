<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Button,
    ConfirmDialog,
    FieldLabel,
    Select,
    TargetPickerDialog,
    TextInput,
    Toggle,
    ValidationError,
    http,
    useToasts,
} from '@lunarphp/panel';
import type { TargetOption } from '@lunarphp/panel';
import type { BundleComponentRow, BundleGroupRow, BundleSummary, ComponentPayload, GroupPayload } from '../types';
import ComponentList from './ComponentList.vue';
import GroupsEditor from './GroupsEditor.vue';

// The bundle editor for one variant. Every concern saves through its own
// endpoint the moment it changes (no edit draft), and each endpoint answers
// with the whole summary, which replaces local state wholesale.
const props = defineProps<{ summary: BundleSummary }>();

const { t } = useI18n();
const toasts = useToasts();

const summary = ref<BundleSummary>(props.summary);
watch(() => props.summary, (value) => { summary.value = value; });

const bundle = computed(() => summary.value.bundle);
const saving = ref(false);
const errors = ref<string[]>([]);

const run = async (request: () => Promise<BundleSummary>, message = t('bundles::bundles.panel.saved')): Promise<void> => {
    saving.value = true;
    errors.value = [];

    try {
        summary.value = await request();
        toasts.success(message);
    } catch (error) {
        if (error instanceof ValidationError) {
            errors.value = Object.values(error.errors).flat();
        } else {
            toasts.error(t('bundles::bundles.panel.load_failed'));
        }
    } finally {
        saving.value = false;
    }
};

// Define / remove.
const confirmingRemove = ref(false);

const define = (pricing: 'fixed' | 'components', discount: number | null): Promise<BundleSummary> =>
    http.put<BundleSummary>(summary.value.urls.define, { pricing, discount_percentage: discount });

const onToggle = (): void => {
    if (summary.value.defined) {
        confirmingRemove.value = true;

        return;
    }

    void run(() => define('fixed', null));
};

const remove = (): void => {
    void run(() => http.delete<BundleSummary>(summary.value.urls.destroy), t('bundles::bundles.panel.removed'));
};

// Pricing. Switching to component pricing hands the variant's base prices
// over to the package, which rewrites them on every reprice, so confirm first.
const confirmingComponents = ref(false);

const onPricing = (value: string | number | null): void => {
    if (!bundle.value || value === bundle.value.pricing) {
        return;
    }

    if (value === 'components') {
        confirmingComponents.value = true;

        return;
    }

    void run(() => define('fixed', null));
};

const switchToComponents = (): void => {
    void run(() => define('components', bundle.value?.discount_percentage ?? null));
};

const onDiscount = (event: Event): void => {
    const raw = (event.target as HTMLInputElement).value.trim();
    const discount = raw === '' ? null : Number(raw);

    if (bundle.value && discount !== bundle.value.discount_percentage) {
        void run(() => define(bundle.value!.pricing, discount));
    }
};

// Components: the payload is always the complete list, fixed and grouped.
const listOf = (groupId: number | null): BundleComponentRow[] =>
    (bundle.value?.components ?? []).filter((component) => component.group_id === groupId);

const fixedComponents = computed(() => listOf(null));

const toPayload = (components: BundleComponentRow[]): ComponentPayload[] => {
    const counters = new Map<number | null, number>();

    return components.map((component) => {
        const position = counters.get(component.group_id) ?? 0;
        counters.set(component.group_id, position + 1);

        return {
            variant_id: component.variant.id,
            quantity: component.quantity,
            group_id: component.group_id,
            default: component.default,
            position,
        };
    });
};

const syncComponents = (components: BundleComponentRow[]): void => {
    void run(() => http.put<BundleSummary>(summary.value.urls.components, { components: toPayload(components) }));
};

const current = (): BundleComponentRow[] => [...(bundle.value?.components ?? [])];

const onComponentQuantity = (target: BundleComponentRow, quantity: number): void => {
    syncComponents(current().map((component) => (component.id === target.id ? { ...component, quantity } : component)));
};

const onComponentDefault = (target: BundleComponentRow, value: boolean): void => {
    syncComponents(current().map((component) => (component.id === target.id ? { ...component, default: value } : component)));
};

const onComponentRemove = (target: BundleComponentRow): void => {
    syncComponents(current().filter((component) => component.id !== target.id));
};

const onComponentMove = (target: BundleComponentRow, direction: -1 | 1): void => {
    const siblings = listOf(target.group_id);
    const index = siblings.findIndex((component) => component.id === target.id);
    const swap = siblings[index + direction];

    if (!swap) {
        return;
    }

    const reordered = [...siblings];
    reordered[index] = swap;
    reordered[index + direction] = target;

    syncComponents([
        ...current().filter((component) => component.group_id !== target.group_id),
        ...reordered,
    ]);
};

// Picker: one dialog serves the fixed list and every group.
const pickerOpen = ref(false);
const pickerGroup = ref<number | null>(null);

const openPicker = (groupId: number | null): void => {
    pickerGroup.value = groupId;
    pickerOpen.value = true;
};

const onPicked = (targets: TargetOption[]): void => {
    const existing = current();
    const additions: BundleComponentRow[] = targets
        .filter((target) => !existing.some((component) => component.group_id === pickerGroup.value && component.variant.id === target.id))
        .map((target, index) => ({
            id: -(index + 1),
            public_id: '',
            group_id: pickerGroup.value,
            quantity: 1,
            default: false,
            position: 0,
            // The picker row carries the summary's variant fields; only `id`
            // is read before the response replaces this placeholder.
            variant: { ...(target as unknown as BundleComponentRow['variant']), id: target.id },
        }));

    if (additions.length) {
        syncComponents([...existing, ...additions]);
    }
};

// Groups.
const groupPayload = (groups: BundleGroupRow[]): GroupPayload[] =>
    groups.map((group, position) => ({
        id: group.id > 0 ? group.id : null,
        name: group.name,
        min_selections: group.min_selections,
        max_selections: group.max_selections,
        position,
    }));

const syncGroups = (groups: BundleGroupRow[]): void => {
    void run(() => http.put<BundleSummary>(summary.value.urls.groups, { groups: groupPayload(groups) }));
};

const currentGroups = (): BundleGroupRow[] => [...(bundle.value?.groups ?? [])];

const onGroupAdd = (): void => {
    syncGroups([
        ...currentGroups(),
        {
            id: 0,
            public_id: '',
            name: { [summary.value.default_language]: t('bundles::bundles.panel.new_group_name') },
            label: '',
            min_selections: 1,
            max_selections: 1,
            position: currentGroups().length,
        },
    ]);
};

const onGroupUpdate = (target: BundleGroupRow, patch: Partial<BundleGroupRow>): void => {
    syncGroups(currentGroups().map((group) => (group.id === target.id ? { ...group, ...patch } : group)));
};

const onGroupRemove = (target: BundleGroupRow): void => {
    syncGroups(currentGroups().filter((group) => group.id !== target.id));
};

const onGroupMove = (target: BundleGroupRow, direction: -1 | 1): void => {
    const groups = currentGroups();
    const index = groups.findIndex((group) => group.id === target.id);
    const swap = groups[index + direction];

    if (!swap) {
        return;
    }

    groups[index] = swap;
    groups[index + direction] = target;
    syncGroups(groups);
};

const availabilityText = computed(() => {
    const availability = bundle.value?.availability;

    if (!availability) {
        return '';
    }

    if (availability.unlimited) {
        return t('bundles::bundles.panel.availability_unlimited');
    }

    return availability.available > 0
        ? t('bundles::bundles.panel.availability_count', { count: availability.available })
        : t('bundles::bundles.panel.availability_none');
});
</script>

<template>
    <div class="flex flex-col gap-5">
        <div class="flex items-center justify-between gap-4">
            <label class="flex items-center gap-2.5 text-[13px] text-ink-900 cursor-pointer">
                <Toggle :on="summary.defined" :disabled="saving" @toggle="onToggle" />
                {{ t('bundles::bundles.panel.sell_as_bundle') }}
            </label>
            <span v-if="bundle" class="text-[12px] text-ink-500" data-testid="bundle-availability">{{ availabilityText }}</span>
        </div>

        <div v-if="errors.length" class="rounded-md border border-danger-border bg-danger-soft px-3 py-2 text-[12px] text-danger">
            <div v-for="(error, index) in errors" :key="index">{{ error }}</div>
        </div>

        <template v-if="bundle">
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <FieldLabel for="bundle-pricing">{{ t('bundles::bundles.panel.pricing') }}</FieldLabel>
                    <Select id="bundle-pricing" :key="bundle.pricing + String(confirmingComponents)" :model-value="bundle.pricing" @update:model-value="onPricing">
                        <option value="fixed">{{ t('bundles::bundles.panel.pricing_fixed') }}</option>
                        <option value="components">{{ t('bundles::bundles.panel.pricing_components') }}</option>
                    </Select>
                </div>
                <div v-if="bundle.pricing === 'components'">
                    <FieldLabel for="bundle-discount" :hint="t('bundles::bundles.panel.discount_hint')">
                        {{ t('bundles::bundles.panel.discount_percentage') }}
                    </FieldLabel>
                    <TextInput
                        id="bundle-discount"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        :model-value="bundle.discount_percentage ?? ''"
                        :disabled="saving"
                        @change="onDiscount"
                    >
                        <template #suffix>%</template>
                    </TextInput>
                </div>
            </div>

            <p v-if="bundle.pricing === 'components'" class="text-[12px] text-ink-500 leading-normal">
                {{ t('bundles::bundles.panel.derived_note') }}
            </p>

            <div>
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div>
                        <div class="text-[12.5px] font-semibold text-ink-900">{{ t('bundles::bundles.panel.components') }}</div>
                        <div class="text-[11.5px] text-ink-500">{{ t('bundles::bundles.panel.components_description') }}</div>
                    </div>
                    <Button size="sm" icon="plus" :disabled="saving" @click="openPicker(null)">
                        {{ t('bundles::bundles.panel.add_component') }}
                    </Button>
                </div>
                <div class="border border-line rounded-lg bg-surface overflow-hidden">
                    <ComponentList
                        :components="fixedComponents"
                        :disabled="saving"
                        :empty-text="t('bundles::bundles.panel.components_empty')"
                        @quantity="onComponentQuantity"
                        @move="onComponentMove"
                        @remove="onComponentRemove"
                    />
                </div>
            </div>

            <div v-if="bundle.pricing === 'components' && bundle.prices.length">
                <div class="text-[12.5px] font-semibold text-ink-900 mb-2">{{ t('bundles::bundles.panel.derived_prices') }}</div>
                <table class="w-full text-[12.5px] border border-line rounded-lg overflow-hidden">
                    <tbody class="divide-y divide-line">
                        <tr v-for="row in bundle.prices" :key="row.currency">
                            <td class="px-3 py-1.5 font-mono text-ink-700 w-[80px]">{{ row.currency }}</td>
                            <td v-if="row.missing.length" class="px-3 py-1.5 text-warn-ink">
                                {{ t('bundles::bundles.panel.missing_price', { components: row.missing.join(', ') }) }}
                            </td>
                            <td v-else class="px-3 py-1.5 text-ink-900 [font-variant-numeric:tabular-nums]">
                                {{ row.price }}
                                <span v-if="row.list_price" class="ml-2 text-ink-500 line-through">{{ row.list_price }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <div class="mb-2">
                    <div class="text-[12.5px] font-semibold text-ink-900">{{ t('bundles::bundles.panel.groups') }}</div>
                    <div class="text-[11.5px] text-ink-500">{{ t('bundles::bundles.panel.groups_description') }}</div>
                </div>
                <GroupsEditor
                    :groups="bundle.groups"
                    :components="bundle.components"
                    :default-language="summary.default_language"
                    :disabled="saving"
                    @add="onGroupAdd"
                    @update="onGroupUpdate"
                    @move="onGroupMove"
                    @remove="onGroupRemove"
                    @add-option="(group) => openPicker(group.id)"
                    @component-quantity="onComponentQuantity"
                    @component-move="onComponentMove"
                    @component-remove="onComponentRemove"
                    @component-default="onComponentDefault"
                />
            </div>
        </template>

        <ConfirmDialog
            v-model:open="confirmingComponents"
            :title="t('bundles::bundles.panel.confirm_components_title')"
            :description="t('bundles::bundles.panel.confirm_components')"
            @confirm="switchToComponents"
        />

        <ConfirmDialog
            v-model:open="confirmingRemove"
            :title="t('bundles::bundles.panel.confirm_remove_title')"
            :description="t('bundles::bundles.panel.confirm_remove')"
            tone="danger"
            @confirm="remove"
        />

        <TargetPickerDialog
            v-model:open="pickerOpen"
            :search-url="summary.urls.search"
            bucket="components"
            :kinds="['variants']"
            @add="onPicked"
        />
    </div>
</template>
