<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ActivityTimeline from '../../../components/ActivityTimeline.vue';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import ColorPicker from '../../../components/ColorPicker.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Icon from '../../../components/Icon.vue';
import Section from '../../../components/Section.vue';
import SideCard from '../../../components/SideCard.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import SwatchInput from '../../../components/SwatchInput.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import TranslatedInput, { type LanguageOption } from '../../../components/TranslatedInput.vue';
import ValuePreviewChip, { type PreviewValue } from '../../../components/ValuePreviewChip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';
import { useDragSort } from '../../../composables/useDragSort';

type Translations = Record<string, string>;

interface ProductOption {
    id: number;
    name: Translations;
    label: Translations;
    handle: string | null;
    type: string;
    shared: boolean;
    products_count: number;
    created_at: string;
    updated_at: string;
}

interface OptionValue {
    id: number | null;
    name: Translations;
    position: number;
    colour: string | null;
    swatch: string | null;
    variant_count: number;
    inUse: boolean;
    urls?: { swatch: string };
}

interface ActivityEntry {
    description: string;
    created_at: string;
    causer_name: string | null;
    avatar: string | null;
    changes: string[];
}

const props = defineProps<{
    productOption: ProductOption;
    values: OptionValue[];
    languages: LanguageOption[];
    hasProducts: boolean;
    activities: ActivityEntry[];
    urls: { update: string; destroy: string; index: string; products: string; activityLog: string };
}>();

const { t, te } = useI18n();

const KNOWN_TYPES = ['text', 'colour', 'swatch'] as const;
const TYPE_ICONS: Record<string, string> = { text: 'type', colour: 'palette', swatch: 'image' };

const typeIcon = (type: string): string => TYPE_ICONS[type] ?? 'help';
const typeLabel = (type: string): string => {
    const key = `product_options.type_${type}`;

    return te(key) ? t(key) : type;
};
const typeTone = (type: string): 'neutral' | 'sage' | 'warn' =>
    type === 'text' ? 'neutral' : (KNOWN_TYPES as readonly string[]).includes(type) ? 'sage' : 'warn';

const defaultCode = computed(
    () => props.languages.find((language) => language.default)?.code ?? props.languages[0]?.code ?? 'en',
);

const nameString = (name: Translations): string =>
    name[defaultCode.value] || Object.values(name).find(Boolean) || '';

const valuePreview = (value: OptionValue): PreviewValue => ({
    name: nameString(value.name),
    colour: value.colour,
    swatch: value.swatch,
});

const optionName = computed(() => nameString(props.productOption.name) || t('product_options.untitled'));

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('product_options.title'), href: props.urls.index },
    { label: optionName.value, current: true },
]);

const buildValues = (): OptionValue[] => props.values.map((value) => ({ ...value }));

const form = useForm({
    name: { ...props.productOption.name },
    label: { ...props.productOption.label },
    handle: props.productOption.handle ?? '',
    type: props.productOption.type,
    shared: props.productOption.shared,
    values: buildValues(),
});

// Re-seed from fresh props after a save so newly created values pick up their
// server ids and swatch upload urls.
const syncFromProps = (): void => {
    form.name = { ...props.productOption.name };
    form.label = { ...props.productOption.label };
    form.handle = props.productOption.handle ?? '';
    form.type = props.productOption.type;
    form.shared = props.productOption.shared;
    form.values = buildValues();
};

const isColour = computed(() => form.type === 'colour');
const isSwatch = computed(() => form.type === 'swatch');
const isKnownType = computed(() => (KNOWN_TYPES as readonly string[]).includes(form.type));

const addValue = (): void => {
    const nextPosition = form.values.reduce((max, value) => Math.max(max, value.position), 0) + 10;

    form.values.push({ id: null, name: {}, position: nextPosition, colour: isColour.value ? '#888888' : null, swatch: null, variant_count: 0, inUse: false });
};

const removeValue = (index: number): void => {
    form.values.splice(index, 1);
};

const { start, over, style, end, isDragging } = useDragSort({
    onCommit: (_listId, from, to) => {
        const next = [...form.values];
        const [moved] = next.splice(from, 1);
        next.splice(to, 0, moved);
        next.forEach((value, index) => { value.position = (index + 1) * 10; });
        form.values = next;
    },
});

const submit = (): void => {
    form
        .transform((data) => ({
            ...data,
            values: data.values.map((value) => ({
                id: value.id,
                name: value.name,
                position: value.position,
                colour: data.type === 'colour' ? value.colour || null : null,
            })),
        }))
        .put(props.urls.update, { preserveScroll: true, onSuccess: () => syncFromProps() });
};

// --- Type change ---
const changingType = ref(false);
const pendingType = ref<string>(form.type);

const requestTypeChange = (): void => {
    pendingType.value = form.type;
    changingType.value = true;
};

const applyTypeChange = (): void => {
    changingType.value = false;

    if (!pendingType.value || pendingType.value === form.type) {
        return;
    }

    form.type = pendingType.value;
    // Clear per-type payloads locally; names + positions are kept. The server
    // clears the stored colour/swatch to match on save.
    form.values.forEach((value) => {
        value.colour = form.type === 'colour' ? value.colour ?? '#888888' : null;
        value.swatch = null;
    });
};

// --- Delete ---
const anyValueInUse = computed(() => props.values.some((value) => value.inUse));

const deleteBlockedReason = computed<string>(() => {
    if (props.hasProducts) return t('product_options.delete_blocked');
    if (anyValueInUse.value) return t('product_options.delete_blocked_values');
    return '';
});

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};

// --- Handle change warning ---
const originalHandle = props.productOption.handle ?? '';
const inUse = computed(() => props.hasProducts || anyValueInUse.value);
const handleChanged = computed(() => inUse.value && !!form.handle && form.handle !== originalHandle);

const valueError = (index: number): string | undefined =>
    (form.errors as Record<string, string>)[`values.${index}.name`]
    ?? (form.errors as Record<string, string>)[`values.${index}.colour`];

const timelineEvents = computed(() =>
    props.activities.map((activity) => ({
        label: activity.description,
        when: activity.created_at,
        actor: activity.causer_name ?? '',
        avatar: activity.avatar,
        changes: activity.changes,
    })));

const formatDate = (iso: string): string => (iso ? new Date(iso).toLocaleDateString() : '—');
</script>

<template>
    <SettingsShell :title="t('product_options.edit_title', { name: optionName })" :breadcrumbs="breadcrumbs" wide>
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <div class="grid lg:grid-cols-[minmax(0,1fr)_320px] gap-6">
            <div class="min-w-0">
                <Section :title="t('product_options.section_details')">
                    <div class="flex flex-col gap-3">
                        <div>
                            <FieldLabel required>{{ t('product_options.field_name') }}</FieldLabel>
                            <TranslatedInput v-model="form.name" :languages="languages" :invalid="!!form.errors.name" :placeholder="t('product_options.name_placeholder')" />
                            <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <FieldLabel :hint="t('product_options.label_hint')">{{ t('product_options.field_label') }}</FieldLabel>
                            <TranslatedInput v-model="form.label" :languages="languages" :placeholder="t('product_options.label_placeholder')" />
                        </div>
                        <div>
                            <FieldLabel :hint="t('product_options.handle_hint')">{{ t('product_options.field_handle') }}</FieldLabel>
                            <TextInput v-model="form.handle" mono :invalid="!!form.errors.handle" />
                            <div v-if="form.errors.handle" class="mt-1 text-[11px] text-danger">{{ form.errors.handle }}</div>
                            <div v-if="handleChanged" class="mt-1 text-[11px] text-warn-ink flex items-center gap-1">
                                <Icon name="alert" cls="sm" />
                                <span>{{ t('product_options.handle_change_warning') }}</span>
                            </div>
                        </div>
                        <div>
                            <FieldLabel>{{ t('product_options.field_type') }}</FieldLabel>
                            <div class="flex items-center gap-2">
                                <StatusBadge :tone="typeTone(form.type)" :icon="typeIcon(form.type)" size="md">{{ typeLabel(form.type) }}</StatusBadge>
                                <button
                                    type="button"
                                    class="text-[12px] text-ink-700 hover:text-ink-900 underline decoration-line-strong underline-offset-2 hover:decoration-ink-700"
                                    @click="requestTypeChange"
                                >{{ t('product_options.change_type') }}</button>
                            </div>
                        </div>
                        <div v-if="productOption.shared" class="flex items-center gap-3 pt-1">
                            <StatusBadge tone="sage" icon="check">{{ t('product_options.shared_badge') }}</StatusBadge>
                            <div class="text-[11px] text-ink-500">{{ t('product_options.shared_locked_hint') }}</div>
                        </div>
                        <label v-else class="flex items-center gap-3 cursor-pointer pt-1">
                            <Toggle :on="form.shared" @toggle="form.shared = !form.shared" />
                            <div>
                                <div class="text-[12.5px] text-ink-900 font-medium">{{ t('product_options.make_shared') }}</div>
                                <div class="text-[11px] text-ink-500">{{ t('product_options.make_shared_hint') }}</div>
                            </div>
                        </label>
                    </div>
                </Section>

                <Section :title="t('product_options.section_values', { count: form.values.length })">
                    <template #desc>{{ t('product_options.values_reorder_desc') }}</template>
                    <template #actions>
                        <Button variant="primary" size="sm" icon="plus" @click="addValue">{{ t('product_options.add_value') }}</Button>
                    </template>

                    <div v-if="!form.values.length" class="px-6 py-8 text-center text-xs text-ink-500 border border-dashed border-line rounded-md">
                        {{ t('product_options.no_values') }}
                    </div>

                    <div
                        v-else
                        class="bg-surface border border-line rounded-xl overflow-hidden"
                        @dragover.prevent="over($event, 'values', form.values.length)"
                        @drop.prevent
                    >
                        <div
                            v-for="(value, index) in form.values"
                            :key="value.id ?? `new-${index}`"
                            class="flex items-start gap-3 px-3 py-2.5 border-b border-line last:border-b-0"
                            :class="isDragging('values', index) ? 'relative z-10 bg-surface-2 shadow-sm' : ''"
                            :style="style('values', index)"
                            @dragend="end()"
                        >
                            <button
                                type="button"
                                draggable="true"
                                class="mt-1.5 text-ink-400 hover:text-ink-700 cursor-grab active:cursor-grabbing"
                                :aria-label="t('product_options.reorder_value')"
                                @dragstart="start($event, 'values', index)"
                                @dragend="end()"
                            >
                                <Icon name="grip" cls="sm" />
                            </button>

                            <div class="mt-0.5 shrink-0">
                                <ValuePreviewChip :type="form.type" :value="valuePreview(value)" :size="32" />
                            </div>

                            <div class="flex-1 min-w-0 grid gap-2" :class="isColour || isSwatch ? 'sm:grid-cols-2' : ''">
                                <div>
                                    <TranslatedInput v-model="value.name" :languages="languages" :placeholder="t('product_options.value_placeholder')" />
                                    <div v-if="valueError(index)" class="mt-1 text-[11px] text-danger">{{ valueError(index) }}</div>
                                </div>
                                <ColorPicker v-if="isColour" v-model="value.colour" :aria-label="t('product_options.pick_colour')" />
                                <SwatchInput
                                    v-else-if="isSwatch"
                                    v-model="value.swatch"
                                    :url="value.urls?.swatch ?? null"
                                />
                                <div v-else-if="!isKnownType" class="text-[11px] text-ink-500 italic self-center">
                                    {{ t('product_options.unsupported_type') }}
                                </div>
                            </div>

                            <div class="mt-1.5 text-[11px] text-ink-500 text-right shrink-0 w-[70px]">
                                <span v-if="value.variant_count > 0">{{ t('product_options.value_variant_count', { count: value.variant_count }) }}</span>
                                <span v-else class="text-ink-400 italic">{{ t('product_options.value_unused') }}</span>
                            </div>

                            <Tooltip :text="value.inUse ? t('product_options.value_delete_blocked') : ''">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :icon="value.inUse ? 'lock' : 'trash'"
                                    :aria-label="t('product_options.remove_value')"
                                    class="!w-[26px] !h-[26px] mt-1 text-ink-500 hover:text-danger"
                                    :disabled="value.inUse"
                                    @click="removeValue(index)"
                                />
                            </Tooltip>
                        </div>
                    </div>
                </Section>
            </div>

            <aside>
                <div class="lg:sticky lg:top-[60px] flex flex-col gap-4">
                    <SideCard :title="t('product_options.side_usage')">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-semibold tracking-[-0.02em] text-ink-900 [font-variant-numeric:tabular-nums]">{{ productOption.products_count }}</span>
                            <span class="text-ink-500 text-[11.5px]">{{ t('product_options.side_usage_products') }}</span>
                        </div>
                        <a
                            v-if="productOption.products_count > 0"
                            :href="urls.products"
                            class="mt-2 inline-flex items-center gap-1 text-[12px] text-ink-700 hover:text-ink-900"
                        >
                            <span>{{ t('product_options.side_view_products') }}</span>
                            <Icon name="chevRight" cls="sm" class="text-ink-400" />
                        </a>
                    </SideCard>

                    <SideCard :title="t('product_options.side_timestamps')">
                        <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1.5 text-[12px]">
                            <dt class="text-ink-500">{{ t('product_options.side_created') }}</dt>
                            <dd class="text-ink-900 text-right">{{ formatDate(productOption.created_at) }}</dd>
                            <dt class="text-ink-500">{{ t('product_options.side_updated') }}</dt>
                            <dd class="text-ink-900 text-right">{{ formatDate(productOption.updated_at) }}</dd>
                        </dl>
                    </SideCard>

                    <SideCard :title="t('product_options.side_activity')">
                        <template #actions>
                            <a :href="urls.activityLog" class="text-[11.5px] font-medium text-ink-500 hover:text-ink-900">{{ t('product_options.side_activity_see_all') }}</a>
                        </template>
                        <ActivityTimeline v-if="activities.length" :events="timelineEvents" :reverse="false" />
                        <div v-else class="text-[11.5px] text-ink-500">{{ t('product_options.side_activity_empty') }}</div>
                    </SideCard>
                </div>
            </aside>
        </div>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('product_options.confirm_delete_title')"
        :description="t('product_options.confirm_delete_body', { name: optionName })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />

    <ConfirmDialog
        v-model:open="changingType"
        :title="t('product_options.change_type_title')"
        :description="t('product_options.change_type_body')"
        :confirm-label="t('product_options.change_type')"
        tone="danger"
        @confirm="applyTypeChange"
    >
        <div class="flex flex-col gap-2 mt-2">
            <FieldLabel>{{ t('product_options.new_type') }}</FieldLabel>
            <div class="grid sm:grid-cols-3 gap-2">
                <button
                    v-for="type in KNOWN_TYPES"
                    :key="type"
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-2.5 py-2 border rounded-md text-[12px] transition-colors',
                        pendingType === type
                            ? 'border-ink-900/40 bg-surface-2 text-ink-900 font-medium'
                            : 'border-line bg-surface text-ink-700 hover:bg-surface-2',
                    ]"
                    @click="pendingType = type"
                >
                    <Icon :name="typeIcon(type)" cls="sm" />
                    <span>{{ typeLabel(type) }}</span>
                </button>
            </div>
        </div>
    </ConfirmDialog>
</template>
