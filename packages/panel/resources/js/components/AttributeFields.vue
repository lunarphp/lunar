<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import FieldLabel from './FieldLabel.vue';
import Icon from './Icon.vue';
import RichTextEditor from './RichTextEditor.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import Textarea from './Textarea.vue';
import TextInput from './TextInput.vue';
import Toggle from './Toggle.vue';
import TranslatedInput, { type LanguageOption } from './TranslatedInput.vue';
import { useDragSort } from '../composables/useDragSort';

export interface AttributeField {
    key: string;
    handle: string;
    label: string;
    required: boolean;
    type: string;
    config: {
        richtext?: boolean;
        options?: { label: string; value: string | null }[];
        min?: number | null;
        max?: number | null;
        max_items?: number | null;
    };
}

export interface AttributeGroup {
    handle: string;
    name: string;
    fields: AttributeField[];
}

const props = defineProps<{
    groups: AttributeGroup[];
    // The draft form's reactive values object; attribute values live under
    // their `attribute:{handle}` keys so autosave and conflicts come free.
    values: Record<string, unknown>;
    errors: Record<string, string>;
    languages: LanguageOption[];
    description?: string;
}>();

const { t } = useI18n();

// Every group starts open; toggles survive prop refreshes for groups that remain.
const openMap = ref<Record<string, boolean>>({});

watch(
    () => props.groups,
    (groups) => {
        const next: Record<string, boolean> = {};
        groups.forEach((group) => {
            next[group.handle] = openMap.value[group.handle] ?? true;
        });
        openMap.value = next;
    },
    { immediate: true },
);

const allOpen = computed(() => props.groups.every((group) => openMap.value[group.handle]));

const toggleAll = (): void => {
    const target = !allOpen.value;
    props.groups.forEach((group) => {
        openMap.value[group.handle] = target;
    });
};

const stringValue = (field: AttributeField): string => {
    const value = props.values[field.key];

    return typeof value === 'string' || typeof value === 'number' ? String(value) : '';
};

const mapValue = (field: AttributeField): Record<string, string> => {
    const value = props.values[field.key];

    return value && typeof value === 'object' && !Array.isArray(value) ? (value as Record<string, string>) : {};
};

const set = (field: AttributeField, value: unknown): void => {
    props.values[field.key] = value;
};

// List fields edit as a table of rows with drag-handle reordering, matching
// the Filament admin's KeyValue editor. Values authored there arrive as
// key => value maps (a key per entry); panel-authored lists are sequential
// arrays and their rows carry no key. Rows write back in the shape they
// were stored in.
type ListRow = { key: string | null; value: string };

const listRows = (field: AttributeField): ListRow[] => {
    const value = props.values[field.key];

    if (Array.isArray(value)) {
        return value.map((item) => ({ key: null, value: String(item ?? '') }));
    }

    if (value && typeof value === 'object') {
        return Object.entries(value as Record<string, unknown>).map(([key, item]) => ({ key, value: String(item ?? '') }));
    }

    return [];
};

const isKeyedList = (field: AttributeField): boolean => listRows(field).some((row) => row.key !== null);

const writeListRows = (field: AttributeField, rows: ListRow[]): void => {
    if (rows.some((row) => row.key !== null)) {
        set(field, Object.fromEntries(rows.map((row) => [row.key ?? '', row.value])));
    } else {
        set(field, rows.map((row) => row.value));
    }
};

const setListRowValue = (field: AttributeField, index: number, value: string): void => {
    const rows = listRows(field);
    rows[index] = { ...rows[index], value };
    writeListRows(field, rows);
};

const removeListRow = (field: AttributeField, index: number): void => {
    writeListRows(field, listRows(field).filter((_, i) => i !== index));
};

// Plain (sequential) lists render an always-present trailing input so typing
// adds an entry inline (dirty-on-type), with no separate add step — an empty
// list no longer looks inert. The trailing row is index === listRows().length.
const plainDisplayRows = (field: AttributeField): ListRow[] =>
    canAddListRow(field) ? [...listRows(field), { key: null, value: '' }] : listRows(field);

const isTrailingRow = (field: AttributeField, index: number): boolean => index === listRows(field).length;

const onPlainInput = (field: AttributeField, index: number, value: string): void => {
    const rows = listRows(field);

    if (index < rows.length) {
        rows[index] = { key: null, value };
        writeListRows(field, rows);
    } else if (value !== '') {
        // First keystroke in the trailing row promotes it to a real entry; the
        // input keeps focus (rows are keyed by index) and a fresh blank appears.
        writeListRows(field, [...rows, { key: null, value }]);
    }
};

// Prune a row emptied by the user once they leave it; the trailing input keeps
// the "add another" affordance, so nothing is silently lost.
const onPlainBlur = (field: AttributeField, index: number): void => {
    const rows = listRows(field);

    if (index < rows.length && rows[index].value.trim() === '') {
        writeListRows(field, rows.filter((_, i) => i !== index));
    }
};

// Keyed lists auto-commit a complete draft row on blur, so a typed key + value
// is never lost by clicking away or saving without pressing enter/plus.
const commitListDraft = (field: AttributeField): void => {
    const draft = listDraft(field);

    if (draft.key.trim() && draft.value.trim()) {
        addListRow(field);
    }
};

const listDrafts = ref<Record<string, { key: string; value: string }>>({});

const listDraft = (field: AttributeField): { key: string; value: string } =>
    (listDrafts.value[field.key] ??= { key: '', value: '' });

const canAddListRow = (field: AttributeField): boolean =>
    !field.config.max_items || listRows(field).length < field.config.max_items;

const addListRow = (field: AttributeField): void => {
    const draft = listDraft(field);
    const keyed = isKeyedList(field);
    const key = draft.key.trim();
    const value = draft.value.trim();
    const rows = listRows(field);

    if (!canAddListRow(field) || (keyed ? !key || rows.some((row) => row.key === key) : !value)) {
        return;
    }

    writeListRows(field, [...rows, { key: keyed ? key : null, value }]);
    listDrafts.value[field.key] = { key: '', value: '' };
};

// Drag & drop reorder, following MediaManager: dragging a row's handle over
// another row moves it into that position; the draft store updates live.
const fieldByKey = computed<Record<string, AttributeField>>(() =>
    Object.fromEntries(props.groups.flatMap((group) => group.fields).map((field) => [field.key, field])),
);

// Animated drag-to-reorder for keyed lists, one list per field (keyed by
// field.key). Only the keyed list carries a drag handle; plain lists stay
// as-is.
const listSort = useDragSort({
    onCommit: (fieldKey, from, to) => {
        const field = fieldByKey.value[fieldKey];

        if (!field) {
            return;
        }

        const rows = listRows(field);
        rows.splice(to, 0, ...rows.splice(from, 1));
        writeListRows(field, rows);
    },
});

const embedUrl = (field: AttributeField): string | null => {
    const id = stringValue(field);

    if (!id) {
        return null;
    }

    return field.type === 'youtube'
        ? `https://www.youtube.com/embed/${encodeURIComponent(id)}`
        : `https://player.vimeo.com/video/${encodeURIComponent(id)}`;
};
</script>

<template>
    <Section :title="t('attributes.title')">
        <template v-if="description" #desc>{{ description }}</template>
        <template v-if="groups.length" #actions>
            <button
                type="button"
                class="inline-flex items-center justify-center gap-1.5 h-[26px] px-2 bg-transparent border border-transparent rounded-md text-xs font-medium text-ink-900 whitespace-nowrap hover:bg-surface-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                @click="toggleAll"
            >{{ allOpen ? t('attributes.collapse_all') : t('attributes.expand_all') }}</button>
        </template>

        <div v-if="!groups.length" class="text-center px-5 py-7 border border-dashed border-line-strong rounded-md bg-surface-2">
            <div class="w-9 h-9 mx-auto mb-2 bg-surface border border-line rounded-lg grid place-items-center text-ink-500"><Icon name="boxes" /></div>
            <div class="text-[13px] font-medium mb-0.5">{{ t('attributes.empty_title') }}</div>
            <div class="text-xs text-ink-500 max-w-[320px] mx-auto">{{ t('attributes.empty_body') }}</div>
        </div>

        <div
            v-for="group in groups"
            :key="group.handle"
            class="border border-line rounded-lg mb-2.5 last:mb-0 overflow-hidden"
        >
            <button
                type="button"
                class="w-full flex items-center gap-2 px-3.5 py-2.5 bg-surface-2 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                :aria-expanded="openMap[group.handle]"
                @click="openMap[group.handle] = !openMap[group.handle]"
            >
                <Icon name="chevDown" cls="sm" :class="openMap[group.handle] ? '' : '-rotate-90'" class="transition-transform duration-100 text-ink-500" />
                <span class="text-[12.5px] font-semibold text-ink-900">{{ group.name }}</span>
                <span class="text-[11px] text-ink-500">{{ t('attributes.field_count', { count: group.fields.length }) }}</span>
            </button>

            <!-- Column count follows the card's own width (not the viewport):
                 the page sidebar can leave a lg viewport with a narrow card. -->
            <div v-show="openMap[group.handle]" class="px-3.5 py-3 border-t border-line @container">
                <div class="grid grid-cols-1 @xl:grid-cols-2 gap-x-3.5 gap-y-3">
                    <div
                        v-for="field in group.fields"
                        :key="field.key"
                        :class="(field.type === 'text' && field.config.richtext) || field.type === 'list' ? '@xl:col-span-2' : ''"
                    >
                        <FieldLabel :required="field.required">{{ field.label }}</FieldLabel>

                        <RichTextEditor
                            v-if="field.type === 'text' && field.config.richtext"
                            :model-value="stringValue(field)"
                            :invalid="!!errors[field.key]"
                            :aria-label="field.label"
                            @update:model-value="(value) => set(field, value)"
                        />

                        <TextInput
                            v-else-if="field.type === 'text'"
                            :model-value="stringValue(field)"
                            :invalid="!!errors[field.key]"
                            :aria-label="field.label"
                            @update:model-value="(value) => set(field, value)"
                        />

                        <TranslatedInput
                            v-else-if="field.type === 'translated_text'"
                            :model-value="mapValue(field)"
                            :languages="languages"
                            kind="text"
                            :invalid="!!errors[field.key]"
                            @update:model-value="(value) => set(field, value)"
                        />

                        <Select
                            v-else-if="field.type === 'dropdown'"
                            :model-value="stringValue(field)"
                            :aria-label="field.label"
                            @update:model-value="(value) => set(field, value)"
                        >
                            <option value="">{{ t('attributes.select_placeholder') }}</option>
                            <option v-for="option in field.config.options" :key="option.value ?? option.label" :value="option.value ?? ''">
                                {{ option.label }}
                            </option>
                        </Select>

                        <TextInput
                            v-else-if="field.type === 'number'"
                            type="number"
                            :model-value="stringValue(field)"
                            :invalid="!!errors[field.key]"
                            :aria-label="field.label"
                            @update:model-value="(value) => set(field, value === '' ? null : Number(value))"
                        />

                        <div v-else-if="field.type === 'toggle'" class="h-8 flex items-center">
                            <Toggle :on="!!values[field.key]" @toggle="set(field, !values[field.key])" />
                        </div>

                        <div v-else-if="field.type === 'list'" class="border border-line-strong rounded-md bg-surface overflow-hidden">
                            <!-- Plain lists: inline editable rows plus an always-present
                                 trailing input, so typing adds an entry with no extra step. -->
                            <div
                                v-if="!isKeyedList(field)"
                                class="divide-y divide-line"
                                @dragover.prevent="listSort.over($event, field.key, listRows(field).length)"
                                @drop.prevent
                            >
                                <div
                                    v-for="(row, index) in plainDisplayRows(field)"
                                    :key="index"
                                    class="grid items-center gap-1.5 px-1.5 py-1 grid-cols-[auto_minmax(0,1fr)_auto] bg-surface"
                                    :class="[
                                        isTrailingRow(field, index) ? 'bg-surface-2' : '',
                                        listSort.isDragging(field.key, index) ? 'opacity-60 relative z-10' : '',
                                    ]"
                                    :style="listSort.style(field.key, index)"
                                >
                                    <button
                                        type="button"
                                        :draggable="!isTrailingRow(field, index)"
                                        class="w-5 h-6 grid place-items-center text-ink-400 cursor-grab active:cursor-grabbing hover:text-ink-700"
                                        :class="isTrailingRow(field, index) ? 'invisible pointer-events-none' : ''"
                                        :tabindex="isTrailingRow(field, index) ? -1 : 0"
                                        :aria-label="t('attributes.reorder_item')"
                                        @dragstart="listSort.start($event, field.key, index)"
                                        @dragend="listSort.end()"
                                    ><Icon name="grip" cls="sm" /></button>
                                    <TextInput
                                        :model-value="row.value"
                                        :placeholder="isTrailingRow(field, index) ? t('attributes.list_placeholder') : ''"
                                        :aria-label="isTrailingRow(field, index) ? field.label : `${field.label} ${index + 1}`"
                                        @update:model-value="(value) => onPlainInput(field, index, value)"
                                        @blur="onPlainBlur(field, index)"
                                    />
                                    <button
                                        type="button"
                                        class="w-6 h-6 rounded-md grid place-items-center text-ink-400 hover:bg-surface-2 hover:text-danger"
                                        :class="isTrailingRow(field, index) ? 'invisible pointer-events-none' : ''"
                                        :tabindex="isTrailingRow(field, index) ? -1 : 0"
                                        :aria-label="t('attributes.remove_item')"
                                        @click="removeListRow(field, index)"
                                    ><Icon name="x" cls="sm" /></button>
                                </div>
                            </div>

                            <!-- Keyed lists (Filament KeyValue shape): key + value rows with
                                 an add row that auto-commits a complete draft on blur. -->
                            <template v-else>
                                <div
                                    class="divide-y divide-line"
                                    @dragover.prevent="listSort.over($event, field.key)"
                                    @drop.prevent
                                >
                                    <div
                                        v-for="(row, index) in listRows(field)"
                                        :key="row.key ?? index"
                                        class="grid items-center gap-1.5 px-1.5 py-1 grid-cols-[auto_minmax(0,1fr)_minmax(0,2fr)_auto] bg-surface"
                                        :class="listSort.isDragging(field.key, index) ? 'opacity-60 relative z-10' : ''"
                                        :style="listSort.style(field.key, index)"
                                    >
                                        <button
                                            type="button"
                                            draggable="true"
                                            class="w-5 h-6 grid place-items-center text-ink-400 cursor-grab active:cursor-grabbing hover:text-ink-700"
                                            :aria-label="t('attributes.reorder_item')"
                                            @dragstart="listSort.start($event, field.key, index)"
                                            @dragend="listSort.end()"
                                        ><Icon name="grip" cls="sm" /></button>
                                        <span class="px-1 text-[11.5px] font-mono text-ink-500 truncate" :title="row.key ?? ''">{{ row.key }}</span>
                                        <TextInput
                                            :model-value="row.value"
                                            :aria-label="`${field.label}: ${row.key}`"
                                            @update:model-value="(value) => setListRowValue(field, index, value)"
                                        />
                                        <button
                                            type="button"
                                            class="w-6 h-6 rounded-md grid place-items-center text-ink-400 hover:bg-surface-2 hover:text-danger"
                                            :aria-label="t('attributes.remove_item')"
                                            @click="removeListRow(field, index)"
                                        ><Icon name="x" cls="sm" /></button>
                                    </div>
                                </div>

                                <div
                                    v-if="canAddListRow(field)"
                                    class="grid items-center gap-1.5 px-1.5 py-1 bg-surface-2 grid-cols-[auto_minmax(0,1fr)_minmax(0,2fr)_auto]"
                                    :class="listRows(field).length ? 'border-t border-line' : ''"
                                >
                                    <span class="w-5" aria-hidden="true" />
                                    <TextInput
                                        v-model="listDraft(field).key"
                                        mono
                                        :placeholder="t('attributes.list_key_placeholder')"
                                        :aria-label="`${field.label}: ${t('attributes.list_key_placeholder')}`"
                                        @keydown.enter.prevent="addListRow(field)"
                                        @blur="commitListDraft(field)"
                                    />
                                    <TextInput
                                        v-model="listDraft(field).value"
                                        :placeholder="t('attributes.list_value_placeholder')"
                                        :aria-label="field.label"
                                        @keydown.enter.prevent="addListRow(field)"
                                        @blur="commitListDraft(field)"
                                    />
                                    <button
                                        type="button"
                                        class="w-6 h-6 rounded-md grid place-items-center text-ink-500 hover:bg-surface hover:text-ink-900"
                                        :aria-label="t('attributes.add_item')"
                                        @click="addListRow(field)"
                                    ><Icon name="plus" cls="sm" /></button>
                                </div>
                            </template>
                        </div>

                        <div v-else-if="field.type === 'youtube' || field.type === 'vimeo'">
                            <TextInput
                                :model-value="stringValue(field)"
                                mono
                                :placeholder="t(`attributes.${field.type}_placeholder`)"
                                :invalid="!!errors[field.key]"
                                :aria-label="field.label"
                                @update:model-value="(value) => set(field, value)"
                            />
                            <iframe
                                v-if="embedUrl(field)"
                                :src="embedUrl(field)!"
                                class="mt-2 w-full max-w-[480px] aspect-video rounded-md border border-line"
                                frameborder="0"
                                allowfullscreen
                                :title="field.label"
                            />
                        </div>

                        <div
                            v-else
                            class="min-h-8 flex items-center px-2.5 py-1.5 border border-dashed border-line rounded-md bg-surface-2 text-[12px] leading-snug text-ink-500"
                        >
                            {{ field.type === 'file' ? (stringValue(field) || t('attributes.file_readonly')) : t('attributes.unsupported', { type: field.type }) }}
                        </div>

                        <div v-if="errors[field.key]" class="mt-1 text-[11px] text-danger">{{ errors[field.key] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </Section>
</template>
