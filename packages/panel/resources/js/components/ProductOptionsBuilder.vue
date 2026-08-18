<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';
import TextInput from './TextInput.vue';
import ValuePreviewChip from './ValuePreviewChip.vue';

export interface OptionValue {
    id: number;
    name: string;
    colour?: string | null;
    swatch?: string | null;
}

export interface AttachedOption {
    id: number;
    name: string;
    type?: string;
    shared: boolean;
    values: OptionValue[];
    selected_value_ids: number[];
}

export interface VariantPreviewValue {
    name: string;
    type: string;
    colour: string | null;
    swatch: string | null;
}

export interface VariantRow {
    id: number;
    label: string;
    values?: VariantPreviewValue[];
    value_ids: number[];
    sku: string | null;
    price: number | null;
    stock: number;
    enabled: boolean;
    locked: boolean;
    thumbnail: string | null;
    edit_url: string;
}

const props = defineProps<{
    attachedOptions: AttachedOption[];
    variants: VariantRow[];
    searchUrl: string;
    generateUrl: string;
}>();

const emit = defineEmits<{ 'update:staleIds': [ids: number[]]; generated: [] }>();

const { t } = useI18n();

const MAX_OPTIONS = 3;

// ---------------------------------------------------------------------------
// Local builder state. Option edits stay client-side until Generate posts the
// whole selection — abandoning the page abandons pending edits, exactly like
// the prototype's reset affordance.
// ---------------------------------------------------------------------------

interface ValueToken {
    id: number | null;
    name: string;
    colour?: string | null;
    swatch?: string | null;
}

interface OptionRowState {
    key: string;
    id: number | null;
    shared: boolean;
    type: string;
    name: string;
    // Shared rows pick from the canonical set; exclusive rows own their values.
    canonical: OptionValue[];
    selectedIds: number[];
    values: ValueToken[];
}

let localKey = 0;

const seedRows = (): OptionRowState[] =>
    props.attachedOptions.map((option) => ({
        key: `opt-${option.id}`,
        id: option.id,
        shared: option.shared,
        type: option.type ?? 'text',
        name: option.name,
        canonical: option.values,
        selectedIds: [...option.selected_value_ids],
        values: option.shared ? [] : option.values.map((value) => ({ ...value })),
    }));

const rows = reactive<{ list: OptionRowState[] }>({ list: seedRows() });

watch(() => props.attachedOptions, () => {
    rows.list = seedRows();
});

// ---------------------------------------------------------------------------
// Pending combinations and the keep / add / remove diff, mirroring the server
// action's semantics (which recomputes authoritatively on generate).
// ---------------------------------------------------------------------------

const optionTokens = (row: OptionRowState): ValueToken[] =>
    row.shared
        ? row.canonical.filter((value) => row.selectedIds.includes(value.id))
        : row.values.filter((value) => value.name.trim() !== '');

const pendingCombos = computed<ValueToken[][]>(() => {
    if (!rows.list.length) {
        return [];
    }

    let combos: ValueToken[][] = [[]];

    for (const row of rows.list) {
        const tokens = optionTokens(row);

        if (!tokens.length) {
            return [];
        }

        combos = combos.flatMap((combo) => tokens.map((token) => [...combo, token]));
    }

    return combos;
});

const comboKey = (combo: ValueToken[]): string | null =>
    combo.every((token) => token.id !== null)
        ? combo.map((token) => token.id).sort((a, b) => Number(a) - Number(b)).join(':')
        : null;

const variantKey = (variant: VariantRow): string => [...variant.value_ids].sort((a, b) => a - b).join(':');

const diff = computed(() => {
    const pendingKeys = new Set(pendingCombos.value.map(comboKey).filter((key) => key !== null));

    const withValues = props.variants.filter((variant) => variant.value_ids.length > 0);
    const valueless = props.variants.filter((variant) => variant.value_ids.length === 0);

    const kept = withValues.filter((variant) => pendingKeys.has(variantKey(variant)));
    let removed = withValues.filter((variant) => !pendingKeys.has(variantKey(variant)));

    const matched = new Set(kept.map(variantKey));
    let added = pendingCombos.value.filter((combo) => {
        const key = comboKey(combo);

        return key === null || !matched.has(key);
    });

    // The first valueless variant adopts the first new combination (keeping
    // its prices, stock and SKU); the rest are removals.
    let adopted = 0;

    if (valueless.length && added.length) {
        adopted = 1;
        added = added.slice(1);
        removed = [...removed, ...valueless.slice(1)];
    } else {
        removed = [...removed, ...valueless.filter(() => pendingCombos.value.length > 0)];
    }

    return { kept, added, removed, adopted };
});

const hasGeneratedVariants = computed(() => props.variants.some((variant) => variant.value_ids.length > 0));

const hasDrift = computed(() =>
    pendingCombos.value.length > 0
    && hasGeneratedVariants.value
    && (diff.value.added.length > 0 || diff.value.removed.length > 0));

const lockedRemovals = computed(() => diff.value.removed.filter((variant) => variant.locked));

const comboCount = computed(() => pendingCombos.value.length);

const canGenerate = computed(() =>
    rows.list.length > 0
    && comboCount.value > 0
    && lockedRemovals.value.length === 0);

watch(hasDrift, () => {
    emit('update:staleIds', hasDrift.value ? diff.value.removed.map((variant) => variant.id) : []);
});

// ---------------------------------------------------------------------------
// Add-option menu: shared options fetched from settings, or a fresh
// product-specific (exclusive) option.
// ---------------------------------------------------------------------------

const addOpen = ref(false);
const sharedOptions = ref<{ id: number; name: string; type: string; values: OptionValue[] }[]>([]);
const loadingShared = ref(false);

const openAdd = async (): Promise<void> => {
    addOpen.value = true;
    loadingShared.value = true;

    try {
        const response = await fetch(props.searchUrl, { headers: { Accept: 'application/json' } });
        const payload = (await response.json()) as { data: typeof sharedOptions.value };

        sharedOptions.value = payload.data;
    } finally {
        loadingShared.value = false;
    }
};

const availableShared = computed(() =>
    sharedOptions.value.filter((option) => !rows.list.some((row) => row.shared && row.id === option.id)));

const atCapacity = computed(() => rows.list.length >= MAX_OPTIONS);

const addShared = (option: { id: number; name: string; type: string; values: OptionValue[] }): void => {
    if (atCapacity.value) {
        return;
    }

    rows.list.push({
        key: `opt-${option.id}`,
        id: option.id,
        shared: true,
        type: option.type,
        name: option.name,
        canonical: option.values,
        selectedIds: [],
        values: [],
    });

    addOpen.value = false;
};

const addExclusive = (): void => {
    if (atCapacity.value) {
        return;
    }

    rows.list.push({
        key: `new-${++localKey}`,
        id: null,
        shared: false,
        type: 'text',
        name: '',
        canonical: [],
        selectedIds: [],
        values: [],
    });

    addOpen.value = false;
};

const removeRow = (row: OptionRowState): void => {
    rows.list.splice(rows.list.indexOf(row), 1);
};

const toggleSharedValue = (row: OptionRowState, id: number): void => {
    row.selectedIds = row.selectedIds.includes(id)
        ? row.selectedIds.filter((selected) => selected !== id)
        : [...row.selectedIds, id];
};

const exclusiveDraft = reactive<Record<string, string>>({});

const addExclusiveValue = (row: OptionRowState): void => {
    const name = (exclusiveDraft[row.key] ?? '').trim();

    if (!name) {
        return;
    }

    row.values.push({ id: null, name });
    exclusiveDraft[row.key] = '';
};

const removeExclusiveValue = (row: OptionRowState, index: number): void => {
    row.values.splice(index, 1);
};

// ---------------------------------------------------------------------------
// Generate / regenerate / reset.
// ---------------------------------------------------------------------------

const regenOpen = ref(false);
const generating = ref(false);

const selectionsPayload = () =>
    rows.list.map((row) => row.shared
        ? { type: 'shared', id: row.id, value_ids: row.selectedIds }
        : {
            type: 'exclusive',
            id: row.id,
            name: row.name,
            values: row.values.map((value) => ({ id: value.id, name: value.name })),
        });

const generate = (): void => {
    generating.value = true;
    regenOpen.value = false;

    router.post(props.generateUrl, { selections: selectionsPayload() }, {
        preserveScroll: true,
        // Regenerating collapses the editor back to the summary, same as Done.
        onSuccess: () => emit('generated'),
        onFinish: () => {
            generating.value = false;
        },
    });
};

const onGenerateClick = (): void => {
    if (!canGenerate.value) {
        return;
    }

    if (hasGeneratedVariants.value && diff.value.removed.length > 0) {
        regenOpen.value = true;

        return;
    }

    generate();
};

const reset = (): void => {
    rows.list = seedRows();
};
</script>

<template>
    <Section :title="t('products.section_options')">
        <template #desc>{{ t('products.section_options_description', { max: MAX_OPTIONS }) }}</template>
        <template #actions>
            <Button size="sm" icon="plus" :disabled="atCapacity" @click="openAdd">{{ t('products.options_add') }}</Button>
        </template>

        <div v-if="rows.list.length === 0" class="border border-dashed border-line-strong rounded-lg bg-surface-2 px-6 py-8 text-center">
            <div class="w-10 h-10 mx-auto mb-3 grid place-items-center rounded-lg border border-line bg-surface text-ink-500">
                <Icon name="boxes" />
            </div>
            <h3 class="text-[13px] font-semibold text-ink-900 mb-1">{{ t('products.options_empty_title') }}</h3>
            <p class="text-[12px] text-ink-500 max-w-[380px] mx-auto">{{ t('products.options_empty_description') }}</p>
        </div>

        <div v-else class="border border-line rounded-lg bg-surface divide-y divide-line">
            <div v-for="row in rows.list" :key="row.key" class="p-3">
                <div class="flex items-center gap-2 mb-2">
                    <template v-if="row.shared">
                        <span class="text-[12.5px] font-semibold text-ink-900">{{ row.name }}</span>
                        <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium bg-surface-2 border border-line text-ink-500">
                            {{ t('products.options_shared_badge') }}
                        </span>
                    </template>
                    <template v-else>
                        <div class="max-w-[240px]">
                            <TextInput
                                v-model="row.name"
                                :placeholder="t('products.options_name_placeholder')"
                                :aria-label="t('products.options_name_placeholder')"
                            />
                        </div>
                        <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium bg-warn-soft border border-warn-border text-warn-ink">
                            {{ t('products.options_exclusive_badge') }}
                        </span>
                    </template>
                    <div class="flex-1" />
                    <button
                        type="button"
                        class="h-7 w-7 grid place-items-center rounded-md text-ink-500 hover:text-danger hover:bg-danger-soft transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-danger/25"
                        :aria-label="t('products.options_remove')"
                        @click="removeRow(row)"
                    ><Icon name="trash" cls="sm" /></button>
                </div>

                <!-- Shared: pick values from the canonical set -->
                <div v-if="row.shared" class="flex flex-wrap gap-1.5">
                    <button
                        v-for="value in row.canonical"
                        :key="value.id"
                        type="button"
                        :class="[
                            'inline-flex items-center gap-1 h-[24px] px-2 rounded-full border text-[11.5px] transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35',
                            row.selectedIds.includes(value.id)
                                ? 'bg-sage-soft border-sage-border text-sage-ink font-medium'
                                : 'bg-surface-2 border-line text-ink-500 hover:text-ink-900',
                        ]"
                        :aria-pressed="row.selectedIds.includes(value.id)"
                        @click="toggleSharedValue(row, value.id)"
                    >
                        <Icon v-if="row.selectedIds.includes(value.id)" name="check" cls="!w-3 !h-3" />
                        <ValuePreviewChip v-if="row.type === 'colour' || row.type === 'swatch'" :type="row.type" :value="value" :size="16" />
                        {{ value.name }}
                    </button>
                </div>

                <!-- Exclusive: edit the values inline -->
                <div v-else>
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        <span
                            v-for="(value, index) in row.values"
                            :key="`${row.key}-${index}`"
                            class="inline-flex items-center gap-1.5 h-[24px] pl-2 pr-1 border border-line bg-surface-2 rounded-full text-[11.5px] text-ink-900"
                        >
                            {{ value.name }}
                            <button
                                type="button"
                                class="w-4 h-4 rounded-full grid place-items-center text-ink-400 cursor-pointer hover:bg-line-strong hover:text-ink-700"
                                :aria-label="t('products.options_value_remove', { value: value.name })"
                                @click="removeExclusiveValue(row, index)"
                            ><Icon name="x" cls="sm" /></button>
                        </span>
                    </div>
                    <div class="flex gap-1.5 max-w-[280px]">
                        <TextInput
                            v-model="exclusiveDraft[row.key]"
                            :placeholder="t('products.options_value_placeholder')"
                            @keydown.enter.prevent="addExclusiveValue(row)"
                        />
                        <Button size="sm" icon="plus" @click="addExclusiveValue(row)">{{ t('common.add') }}</Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drift banner: options no longer match the generated variants. -->
        <div
            v-if="hasDrift"
            class="mt-3 px-3 py-2.5 bg-warn-soft border border-warn-border rounded-md flex items-center gap-3 flex-wrap"
        >
            <Icon name="alert" cls="sm" class="text-warn-ink shrink-0" />
            <span class="text-[12px] text-warn-ink flex-1 min-w-[200px]">
                {{ t('products.options_drift', { add: diff.added.length + diff.adopted, remove: diff.removed.length }) }}
            </span>
            <Button size="sm" variant="ghost" @click="reset">{{ t('products.options_reset') }}</Button>
            <Button size="sm" variant="primary" :disabled="lockedRemovals.length > 0" @click="onGenerateClick">
                {{ t('products.options_regenerate') }}
            </Button>
        </div>
        <div v-if="lockedRemovals.length" class="mt-2 text-[11.5px] text-danger flex items-center gap-1.5">
            <Icon name="lock" cls="sm" />
            {{ t('products.options_locked', { count: lockedRemovals.length }) }}
        </div>

        <div v-if="rows.list.length" class="flex items-center justify-between gap-3 mt-4">
            <span class="text-[11.5px] text-ink-500 [font-variant-numeric:tabular-nums]">
                {{ comboCount > 0 ? t('products.options_combo_count', { count: comboCount }) : t('products.options_underfilled') }}
            </span>
            <Button
                v-if="!hasGeneratedVariants"
                variant="primary"
                :disabled="!canGenerate || generating"
                @click="onGenerateClick"
            >{{ t('products.options_generate') }}</Button>
        </div>

        <!-- Add-option menu -->
        <Dialog
            :open="addOpen"
            size="sm"
            :title="t('products.options_add_title')"
            :description="t('products.options_add_description')"
            @update:open="addOpen = $event"
        >
            <div v-if="loadingShared" class="text-[12px] text-ink-500 py-2">{{ t('common.loading') }}</div>
            <template v-else>
                <FieldLabel v-if="availableShared.length">{{ t('products.options_shared_title') }}</FieldLabel>
                <div v-if="availableShared.length" class="flex flex-col gap-1 mb-3">
                    <button
                        v-for="option in availableShared"
                        :key="option.id"
                        type="button"
                        class="flex items-center gap-2 px-2.5 py-2 rounded-md border border-line bg-surface text-left hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                        @click="addShared(option)"
                    >
                        <span class="text-[12.5px] font-medium text-ink-900">{{ option.name }}</span>
                        <span class="text-[11px] text-ink-500 truncate">
                            {{ option.values.map((value) => value.name).join(', ') }}
                        </span>
                    </button>
                </div>
                <div v-else class="text-[11.5px] text-ink-500 mb-3">{{ t('products.options_shared_none') }}</div>
                <Button icon="plus" class="w-full justify-center" @click="addExclusive">
                    {{ t('products.options_add_exclusive') }}
                </Button>
            </template>
        </Dialog>

        <ConfirmDialog
            :open="regenOpen"
            :title="t('products.options_regenerate_title')"
            :description="t('products.options_regenerate_confirm', {
                keep: diff.kept.length + diff.adopted,
                add: diff.added.length,
                remove: diff.removed.length,
            })"
            tone="danger"
            :confirm-label="t('products.options_regenerate')"
            @update:open="regenOpen = $event"
            @confirm="generate"
        />
    </Section>
</template>
