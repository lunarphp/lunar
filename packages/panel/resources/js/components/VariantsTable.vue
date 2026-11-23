<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Checkbox from './Checkbox.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import FilterDropdown, { type FilterOption } from './FilterDropdown.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';
import StatusBadge from './StatusBadge.vue';
import TextInput from './TextInput.vue';
import Tooltip from './Tooltip.vue';
import ValuePreviewChip from './ValuePreviewChip.vue';
import type { CurrencyOption } from './PricingEditor.vue';
import type { VariantRow } from './ProductOptionsBuilder.vue';

const props = defineProps<{
    variants: VariantRow[];
    currencies: CurrencyOption[];
    bulkUrl: string;
    // Rows the pending option selection would remove; dimmed while drift exists.
    staleIds?: number[];
    // "Size × Colour" summary; enables the Edit-options affordance in the header.
    optionsSummary?: string;
    // Whether the option editor is currently expanded above the table.
    editingOptions?: boolean;
}>();

const emit = defineEmits<{ 'toggle-options': [] }>();

const { t } = useI18n();

const filter = ref<string>('all');
const selected = ref<number[]>([]);

const filterOptions: FilterOption[] = [
    { value: 'all', label: t('products.variants_filter_all') },
    { value: 'enabled', label: t('products.variants_filter_enabled') },
    { value: 'disabled', label: t('products.variants_filter_disabled') },
    { value: 'oos', label: t('products.variants_filter_oos') },
];

const filtered = computed(() =>
    props.variants.filter((variant) =>
        filter.value === 'enabled'
            ? variant.enabled
            : filter.value === 'disabled'
                ? !variant.enabled
                : filter.value === 'oos'
                    ? variant.stock <= 0
                    : true));

const allSelected = computed(() => filtered.value.length > 0 && filtered.value.every((variant) => selected.value.includes(variant.id)));

const toggleAll = (): void => {
    selected.value = allSelected.value ? [] : filtered.value.map((variant) => variant.id);
};

const toggleOne = (id: number): void => {
    selected.value = selected.value.includes(id)
        ? selected.value.filter((selectedId) => selectedId !== id)
        : [...selected.value, id];
};

const defaultCurrency = computed(() => props.currencies.find((currency) => currency.default) ?? props.currencies[0]);

const formatPrice = (minor: number | null): string => {
    if (minor === null || !defaultCurrency.value) {
        return '—';
    }

    const major = minor / 10 ** defaultCurrency.value.decimal_places;

    return `${major.toFixed(defaultCurrency.value.decimal_places)} ${defaultCurrency.value.code}`;
};

// ---------------------------------------------------------------------------
// Bulk bar: enable / disable / delete / set price / set stock.
// ---------------------------------------------------------------------------

const confirmDelete = ref(false);
const prompting = ref<'price' | 'stock' | null>(null);
const promptValue = ref('');

const promptOpen = computed({
    get: () => prompting.value !== null,
    set: (value: boolean) => {
        if (!value) {
            prompting.value = null;
        }
    },
});

const bulk = (op: string, value?: number): void => {
    router.post(props.bulkUrl, { op, ids: selected.value, value }, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = [];
        },
    });
};

const applyPrompt = (): void => {
    const raw = Number(promptValue.value);

    if (!prompting.value || !Number.isFinite(raw) || raw < 0) {
        return;
    }

    const value = prompting.value === 'price' && defaultCurrency.value
        ? Math.round(raw * 10 ** defaultCurrency.value.decimal_places)
        : Math.round(raw);

    bulk(prompting.value, value);
    prompting.value = null;
};

const openPrompt = (kind: 'price' | 'stock'): void => {
    promptValue.value = '';
    prompting.value = kind;
};

const isStale = (variant: VariantRow): boolean => (props.staleIds ?? []).includes(variant.id);
</script>

<template>
    <Section :title="t('products.section_variants')">
        <template #desc>
            <template v-if="optionsSummary">{{ t('products.variants_summary', { count: variants.length, options: optionsSummary }) }}</template>
            <template v-else>{{ t('products.variants_count', { shown: filtered.length, total: variants.length }) }}</template>
        </template>
        <template v-if="optionsSummary" #actions>
            <Button variant="ghost" size="sm" icon="sliders" @click="emit('toggle-options')">
                {{ editingOptions ? t('common.done') : t('products.variants_edit_options') }}
            </Button>
        </template>

        <!-- Toolbar: variant count and the filter, aligned; replaced by the bulk
             bar while rows are selected. -->
        <div v-if="selected.length" class="flex flex-wrap items-center gap-2 mb-3 px-3 py-2 rounded-md border border-line bg-surface-2">
            <span class="text-xs text-ink-700 whitespace-nowrap">
                <span class="font-semibold text-ink-900 [font-variant-numeric:tabular-nums]">{{ selected.length }}</span>
                {{ t('products.variants_selected') }}
            </span>
            <span class="w-px h-5 bg-line" />
            <Button size="sm" icon="check" @click="bulk('enable')">{{ t('products.variants_enable') }}</Button>
            <Button size="sm" icon="x" @click="bulk('disable')">{{ t('products.variants_disable') }}</Button>
            <Button size="sm" icon="edit" @click="openPrompt('price')">{{ t('products.variants_set_price') }}</Button>
            <Button size="sm" icon="edit" @click="openPrompt('stock')">{{ t('products.variants_set_stock') }}</Button>
            <Button size="sm" icon="trash" class="!text-danger" @click="confirmDelete = true">{{ t('common.delete') }}</Button>
            <div class="flex-1" />
            <Button size="sm" variant="ghost" @click="selected = []">{{ t('common.clear') }}</Button>
        </div>

        <div v-else class="flex items-center justify-between gap-3 mb-3">
            <span class="text-[12.5px] font-medium text-ink-900 [font-variant-numeric:tabular-nums]">
                {{ t('products.variants_count', { shown: filtered.length, total: variants.length }) }}
            </span>
            <FilterDropdown v-model="filter" :label="t('products.variants_filter')" :options="filterOptions" default-value="all" />
        </div>

        <div class="border border-line rounded-lg bg-surface overflow-hidden">
            <div class="grid grid-cols-[28px_36px_minmax(0,1.4fr)_minmax(0,1fr)_110px_70px_80px_60px] items-center gap-3 px-3 py-2 bg-surface-2 border-b border-line text-[10.5px] uppercase tracking-[0.06em] text-ink-500 font-medium">
                <div>
                    <Checkbox :model-value="allSelected" :aria-label="t('common.select_all')" @update:model-value="toggleAll" />
                </div>
                <div />
                <div>{{ t('products.variants_column_variant') }}</div>
                <div>{{ t('products.column_sku') }}</div>
                <div class="text-right">{{ t('products.variants_column_price') }}</div>
                <div class="text-right">{{ t('products.column_stock') }}</div>
                <div>{{ t('products.variants_column_state') }}</div>
                <div />
            </div>

            <div v-if="!filtered.length" class="px-4 py-6 text-center text-[12px] text-ink-500">
                {{ t('products.variants_none_match') }}
            </div>

            <Link
                v-for="variant in filtered"
                :key="variant.id"
                :href="variant.edit_url"
                :class="[
                    'grid grid-cols-[28px_36px_minmax(0,1.4fr)_minmax(0,1fr)_110px_70px_80px_60px] items-center gap-3 px-3 py-2 border-b border-line last:border-b-0 transition-[background-color,opacity] duration-100 cursor-pointer hover:bg-surface-2',
                    isStale(variant) ? 'opacity-45' : '',
                ]"
            >
                <div @click.prevent.stop>
                    <Checkbox
                        :model-value="selected.includes(variant.id)"
                        :aria-label="t('products.variants_select', { label: variant.label })"
                        @update:model-value="toggleOne(variant.id)"
                    />
                </div>
                <div class="w-8 h-8 rounded-md overflow-hidden bg-surface-2 border border-line grid place-items-center text-ink-500">
                    <img v-if="variant.thumbnail" :src="variant.thumbnail" :alt="variant.label" class="w-full h-full object-cover block" loading="lazy">
                    <Icon v-else name="box" cls="sm" />
                </div>
                <div class="min-w-0 flex items-center gap-1.5">
                    <span class="flex items-center gap-1 shrink-0">
                        <ValuePreviewChip
                            v-for="(value, index) in (variant.values ?? []).filter((entry) => entry.type === 'colour' || entry.type === 'swatch')"
                            :key="index"
                            :type="value.type"
                            :value="value"
                            :size="18"
                        />
                    </span>
                    <span class="text-[12.5px] font-medium text-ink-900 truncate">{{ variant.label }}</span>
                    <Tooltip v-if="variant.locked" :text="t('products.variants_locked_tip')">
                        <Icon name="lock" cls="sm" class="text-ink-400 shrink-0" />
                    </Tooltip>
                </div>
                <div class="min-w-0 text-[11.5px] font-mono text-ink-700 truncate">{{ variant.sku ?? '—' }}</div>
                <div class="text-right text-[12.5px] [font-variant-numeric:tabular-nums] text-ink-900">{{ formatPrice(variant.price) }}</div>
                <div :class="['text-right text-[12.5px] [font-variant-numeric:tabular-nums]', variant.stock > 0 ? 'text-ink-900' : 'text-ink-400']">{{ variant.stock }}</div>
                <div>
                    <StatusBadge :tone="variant.enabled ? 'sage' : 'warn'" dot>
                        {{ variant.enabled ? t('products.variants_state_enabled') : t('products.variants_state_disabled') }}
                    </StatusBadge>
                </div>
                <div class="flex justify-end text-ink-400">
                    <Icon name="chevRight" cls="sm" />
                </div>
            </Link>
        </div>

        <ConfirmDialog
            v-model:open="confirmDelete"
            :title="t('products.variants_delete_title')"
            :description="t('products.variants_delete_confirm', { count: selected.length })"
            tone="danger"
            :confirm-label="t('common.delete')"
            @confirm="bulk('destroy')"
        />

        <Dialog
            :open="promptOpen"
            size="sm"
            :title="prompting === 'price' ? t('products.variants_set_price_title') : t('products.variants_set_stock_title')"
            :description="prompting === 'price' ? t('products.variants_set_price_description') : t('products.variants_set_stock_description')"
            @update:open="promptOpen = $event"
        >
            <FieldLabel for="variant-bulk-value" required>
                {{ prompting === 'price' ? t('pricing.amount') : t('products.stock_on_hand') }}
            </FieldLabel>
            <TextInput
                id="variant-bulk-value"
                v-model="promptValue"
                type="number"
                min="0"
                :step="prompting === 'price' ? 'any' : '1'"
                @keydown.enter.prevent="applyPrompt"
            />

            <template #footer>
                <Button variant="ghost" @click="promptOpen = false">{{ t('common.cancel') }}</Button>
                <Button variant="primary" :disabled="promptValue === ''" @click="applyPrompt">{{ t('common.apply') }}</Button>
            </template>
        </Dialog>
    </Section>
</template>
