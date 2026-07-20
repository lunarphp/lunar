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
const listDrag = ref<{ field: string; index: number } | null>(null);

const onListRowDragStart = (field: AttributeField, index: number): void => {
    listDrag.value = { field: field.key, index };
};

const onListRowDragOver = (field: AttributeField, index: number): void => {
    if (!listDrag.value || listDrag.value.field !== field.key || listDrag.value.index === index) {
        return;
    }

    const rows = listRows(field);
    rows.splice(index, 0, ...rows.splice(listDrag.value.index, 1));
    writeListRows(field, rows);
    listDrag.value.index = index;
};

const onListRowDragEnd = (): void => {
    listDrag.value = null;
};

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
                            <div v-if="listRows(field).length" class="divide-y divide-line">
                                <div
                                    v-for="(row, index) in listRows(field)"
                                    :key="row.key ?? index"
                                    class="grid items-center gap-1.5 px-1.5 py-1"
                                    :class="[
                                        row.key !== null ? 'grid-cols-[auto_minmax(0,1fr)_minmax(0,2fr)_auto]' : 'grid-cols-[auto_minmax(0,1fr)_auto]',
                                        listDrag?.field === field.key && listDrag?.index === index ? 'opacity-60' : '',
                                    ]"
                                    @dragover.prevent="onListRowDragOver(field, index)"
                                >
                                    <button
                                        type="button"
                                        draggable="true"
                                        class="w-5 h-6 grid place-items-center text-ink-400 cursor-grab active:cursor-grabbing hover:text-ink-700"
                                        :aria-label="t('attributes.reorder_item')"
                                        @dragstart="onListRowDragStart(field, index)"
                                        @dragend="onListRowDragEnd"
                                    ><Icon name="grip" cls="sm" /></button>
                                    <span
                                        v-if="row.key !== null"
                                        class="px-1 text-[11.5px] font-mono text-ink-500 truncate"
                                        :title="row.key"
                                    >{{ row.key }}</span>
                                    <TextInput
                                        :model-value="row.value"
                                        :aria-label="row.key !== null ? `${field.label}: ${row.key}` : `${field.label} ${index + 1}`"
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
                                class="grid items-center gap-1.5 px-1.5 py-1 bg-surface-2"
                                :class="[
                                    isKeyedList(field) ? 'grid-cols-[auto_minmax(0,1fr)_minmax(0,2fr)_auto]' : 'grid-cols-[auto_minmax(0,1fr)_auto]',
                                    listRows(field).length ? 'border-t border-line' : '',
                                ]"
                            >
                                <span class="w-5" aria-hidden="true" />
                                <TextInput
                                    v-if="isKeyedList(field)"
                                    v-model="listDraft(field).key"
                                    mono
                                    :placeholder="t('attributes.list_key_placeholder')"
                                    :aria-label="`${field.label}: ${t('attributes.list_key_placeholder')}`"
                                    @keydown.enter.prevent="addListRow(field)"
                                />
                                <TextInput
                                    v-model="listDraft(field).value"
                                    :placeholder="isKeyedList(field) ? t('attributes.list_value_placeholder') : t('attributes.list_placeholder')"
                                    :aria-label="field.label"
                                    @keydown.enter.prevent="addListRow(field)"
                                />
                                <button
                                    type="button"
                                    class="w-6 h-6 rounded-md grid place-items-center text-ink-500 hover:bg-surface hover:text-ink-900"
                                    :aria-label="t('attributes.add_item')"
                                    @click="addListRow(field)"
                                ><Icon name="plus" cls="sm" /></button>
                            </div>
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
