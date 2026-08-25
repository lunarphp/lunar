<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { type BreadcrumbItem } from '../../../components/Breadcrumbs.vue';
import Button from '../../../components/Button.vue';
import Checkbox from '../../../components/Checkbox.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';
import FieldLabel from '../../../components/FieldLabel.vue';
import Icon from '../../../components/Icon.vue';
import Section from '../../../components/Section.vue';
import Select from '../../../components/Select.vue';
import StatusBadge from '../../../components/StatusBadge.vue';
import TextInput from '../../../components/TextInput.vue';
import Toggle from '../../../components/Toggle.vue';
import Tooltip from '../../../components/Tooltip.vue';
import SettingsShell from '../../../layouts/SettingsShell.vue';

type Attribute = {
    id: number;
    name: string;
    handle: string;
    type: string;
    attribute_group_id: number | null;
    position: number;
    required: boolean;
    validation_rules: string[];
    searchable: boolean;
    filterable: boolean;
    system: boolean;
    configuration: Record<string, unknown>;
    model_types: string[];
};

type Option = { value: string; label: string };
type NamedOption = { id: number; name: string };

// Renderer-agnostic descriptors from the field type's
// getConfigurationFields() — see core FieldType contract.
type ConfigField = {
    key: string;
    type: string;
    label: string;
    hint?: string;
    suggestions?: string[];
    options?: Option[];
};

const props = defineProps<{
    attribute: Attribute;
    attributeGroups: NamedOption[];
    configFields: ConfigField[];
    fieldTypes: Option[];
    modelTypes: Option[];
    urls: { update: string; destroy: string; index: string };
}>();

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.settings') },
    { label: t('attributes_settings.title'), href: props.urls.index },
    { label: props.attribute.name, current: true },
]);

// Lookup values are stored as rows of {label, value}; earlier builds saved
// a label => value map, so hydrate both shapes. Rows edit in local state per
// descriptor key and fold back into the configuration on submit.
type LookupRow = { label: string; value: string };

const hydrateLookupRows = (raw: unknown): LookupRow[] =>
    Array.isArray(raw)
        ? (raw as Array<{ label?: unknown; value?: unknown }>).map((row) => ({
            label: String(row.label ?? ''),
            value: String(row.value ?? ''),
        }))
        : Object.entries((raw as Record<string, string> | undefined) ?? {}).map(([label, value]) => ({
            label,
            value: String(value ?? ''),
        }));

const lookupRows = ref<Record<string, LookupRow[]>>(
    Object.fromEntries(
        props.configFields
            .filter((field) => field.type === 'lookups')
            .map((field) => [field.key, hydrateLookupRows(props.attribute.configuration[field.key])]),
    ),
);

// Configuration values are type-specific scalars, plus lookup rows and tag lists.
type ConfigurationValue = string | number | boolean | null | LookupRow[] | string[];

const form = useForm({
    name: props.attribute.name,
    handle: props.attribute.handle,
    attribute_group_id: props.attribute.attribute_group_id,
    position: String(props.attribute.position),
    required: props.attribute.required,
    validation_rules: [...props.attribute.validation_rules],
    searchable: props.attribute.searchable,
    filterable: props.attribute.filterable,
    model_types: [...props.attribute.model_types],
    configuration: { ...props.attribute.configuration } as Record<string, ConfigurationValue>,
});

const typeLabel = computed(() => props.fieldTypes.find((o) => o.value === props.attribute.type)?.label ?? props.attribute.type);

const toggleModelType = (value: string): void => {
    const index = form.model_types.indexOf(value);
    if (index >= 0) {
        form.model_types.splice(index, 1);
    } else {
        form.model_types.push(value);
    }
};

const addLookup = (key: string): void => {
    (lookupRows.value[key] ??= []).push({ label: '', value: '' });
};

const removeLookup = (key: string, index: number): void => {
    lookupRows.value[key]?.splice(index, 1);
};

// Tag lists (arrays of strings) edit as rows with an inline add input.
const tagDrafts = ref<Record<string, string>>({});

const tagValues = (key: string): string[] => {
    const value = form.configuration[key];

    return Array.isArray(value) ? (value as string[]) : [];
};

const addTag = (key: string): void => {
    const draft = (tagDrafts.value[key] ?? '').trim();

    if (!draft || tagValues(key).includes(draft)) {
        return;
    }

    form.configuration[key] = [...tagValues(key), draft];
    tagDrafts.value[key] = '';
};

const removeTag = (key: string, index: number): void => {
    form.configuration[key] = tagValues(key).filter((_, i) => i !== index);
};

// Validation rules edit as a tag list on the form root (spec 0062).
const ruleDraft = ref('');

const addRule = (): void => {
    const draft = ruleDraft.value.trim();

    if (!draft || form.validation_rules.includes(draft)) {
        return;
    }

    form.validation_rules = [...form.validation_rules, draft];
    ruleDraft.value = '';
};

const removeRule = (index: number): void => {
    form.validation_rules = form.validation_rules.filter((_, i) => i !== index);
};

const ruleError = computed<string | undefined>(() => {
    const errors = form.errors as Record<string, string>;

    return errors.validation_rules
        ?? Object.entries(errors).find(([key]) => key.startsWith('validation_rules.'))?.[1];
});

const scalarConfig = (key: string): string => {
    const value = form.configuration[key];

    return typeof value === 'string' || typeof value === 'number' ? String(value) : '';
};

const configError = (key: string): string | undefined =>
    (form.errors as Record<string, string>)[`configuration.${key}`];

const submit = (): void => {
    // A rule typed but not yet committed with Enter would otherwise be
    // silently discarded on save.
    addRule();

    for (const field of props.configFields) {
        if (field.type === 'lookups') {
            form.configuration = {
                ...form.configuration,
                [field.key]: (lookupRows.value[field.key] ?? [])
                    .filter((row) => row.label.trim() !== '')
                    .map((row) => ({ label: row.label.trim(), value: row.value.trim() || row.label.trim() })),
            };
        }
    }

    form.put(props.urls.update);
};

// System attributes belong to Lunar and cannot be removed.
const deleteBlockedReason = computed<string>(() =>
    (props.attribute.system ? t('attributes_settings.delete_blocked_system') : ''));

const deleting = ref(false);

const confirmDestroy = (): void => {
    router.delete(props.urls.destroy);
};
</script>

<template>
    <SettingsShell :title="t('attributes_settings.edit_title', { name: attribute.name })" :breadcrumbs="breadcrumbs">
        <template #actions>
            <Tooltip :text="deleteBlockedReason">
                <Button variant="ghost" icon="trash" :disabled="!!deleteBlockedReason" @click="deleting = true">{{ t('common.delete') }}</Button>
            </Tooltip>
            <Button variant="primary" icon="check" size="sm" :disabled="form.processing" @click="submit">{{ t('common.save') }}</Button>
        </template>

        <Section :title="t('attributes_settings.section_details')">
            <template v-if="attribute.system" #actions>
                <StatusBadge tone="archived" size="md">{{ t('attributes_settings.system_badge') }}</StatusBadge>
            </template>

            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <FieldLabel required>{{ t('attributes_settings.field_name') }}</FieldLabel>
                    <TextInput v-model="form.name" :disabled="attribute.system" :invalid="!!form.errors.name" />
                    <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                </div>
                <div>
                    <FieldLabel :hint="t('attributes_settings.handle_hint')">{{ t('attributes_settings.field_handle') }}</FieldLabel>
                    <TextInput :model-value="attribute.handle" disabled mono />
                </div>
                <div>
                    <FieldLabel>{{ t('attributes_settings.field_group') }}</FieldLabel>
                    <Select v-model="form.attribute_group_id">
                        <option :value="null">{{ t('attributes_settings.no_group') }}</option>
                        <option v-for="group in attributeGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </Select>
                </div>
                <div>
                    <FieldLabel :hint="t('attributes_settings.type_locked_hint')">{{ t('attributes_settings.field_type') }}</FieldLabel>
                    <TextInput :model-value="typeLabel" disabled />
                </div>
                <div>
                    <FieldLabel>{{ t('attributes_settings.field_position') }}</FieldLabel>
                    <TextInput v-model="form.position" type="number" min="1" :invalid="!!form.errors.position" />
                </div>
            </div>
        </Section>

        <Section :title="t('attributes_settings.section_behaviour')">
            <div class="flex flex-col gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.required" @toggle="form.required = !form.required" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('attributes_settings.flag_required') }}</div>
                        <div class="text-[11px] text-ink-500">{{ t('attributes_settings.required_hint') }}</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.searchable" @toggle="form.searchable = !form.searchable" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('attributes_settings.flag_searchable') }}</div>
                        <div class="text-[11px] text-ink-500">{{ t('attributes_settings.searchable_hint') }}</div>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <Toggle :on="form.filterable" @toggle="form.filterable = !form.filterable" />
                    <div>
                        <div class="text-[12.5px] text-ink-900 font-medium">{{ t('attributes_settings.flag_filterable') }}</div>
                        <div class="text-[11px] text-ink-500">{{ t('attributes_settings.filterable_hint') }}</div>
                    </div>
                </label>
                <div>
                    <FieldLabel :hint="t('attributes_settings.validation_rules_hint')">{{ t('attributes_settings.validation_rules_label') }}</FieldLabel>
                    <div class="flex flex-wrap gap-1.5 px-1.5 py-1.5 min-h-[34px] border border-line-strong rounded-md bg-surface items-center">
                        <span
                            v-for="(rule, index) in form.validation_rules"
                            :key="`${rule}-${index}`"
                            class="inline-flex items-center gap-1 h-[22px] pl-2 pr-1 border border-line bg-surface-2 rounded-full text-[11.5px] font-mono text-ink-700"
                        >
                            {{ rule }}
                            <button
                                type="button"
                                class="w-4 h-4 rounded-full grid place-items-center text-ink-400 hover:bg-line-strong hover:text-ink-700"
                                :aria-label="t('attributes_settings.remove_option')"
                                @click="removeRule(index)"
                            ><Icon name="x" cls="sm" /></button>
                        </span>
                        <input
                            v-model="ruleDraft"
                            type="text"
                            class="flex-1 min-w-[120px] h-[22px] px-1 bg-transparent text-[12.5px] font-mono text-ink-900 outline-none"
                            placeholder="min:1"
                            :aria-label="t('attributes_settings.validation_rules_label')"
                            @keydown.enter.prevent="addRule()"
                            @blur="addRule()"
                        />
                    </div>
                    <div v-if="ruleError" class="mt-1 text-[11px] text-danger">{{ ruleError }}</div>
                </div>
            </div>
        </Section>

        <Section :title="t('attributes_settings.section_model_types')">
            <template #desc>{{ t('attributes_settings.model_types_desc') }}</template>

            <div class="flex flex-wrap gap-x-4 gap-y-2">
                <label v-for="modelType in modelTypes" :key="modelType.value" class="flex items-center gap-2 cursor-pointer">
                    <Checkbox :model-value="form.model_types.includes(modelType.value)" @update:model-value="toggleModelType(modelType.value)" />
                    <span class="text-[12.5px] text-ink-900">{{ modelType.label }}</span>
                </label>
            </div>
            <div v-if="form.errors.model_types" class="mt-1 text-[11px] text-danger">{{ form.errors.model_types }}</div>
        </Section>

        <!-- Type-specific configuration, rendered from the field type's own
             descriptors so consumer-registered types work without panel code. -->
        <Section v-if="configFields.length" :title="t('attributes_settings.section_configuration')">
            <div class="flex flex-col gap-4">
                <div v-for="field in configFields" :key="field.key">
                    <label v-if="field.type === 'toggle'" class="flex items-center gap-3 cursor-pointer">
                        <Toggle :on="!!form.configuration[field.key]" @toggle="form.configuration[field.key] = !form.configuration[field.key]" />
                        <div>
                            <div class="text-[12.5px] text-ink-900 font-medium">{{ field.label }}</div>
                            <div v-if="field.hint" class="text-[11px] text-ink-500">{{ field.hint }}</div>
                        </div>
                    </label>

                    <div v-else-if="field.type === 'number' || field.type === 'text'" class="sm:max-w-[280px]">
                        <FieldLabel :hint="field.hint">{{ field.label }}</FieldLabel>
                        <TextInput
                            :type="field.type === 'number' ? 'number' : 'text'"
                            :model-value="scalarConfig(field.key)"
                            :invalid="!!configError(field.key)"
                            :aria-label="field.label"
                            @update:model-value="(value) => (form.configuration[field.key] = value === '' ? null : value)"
                        />
                        <div v-if="configError(field.key)" class="mt-1 text-[11px] text-danger">{{ configError(field.key) }}</div>
                    </div>

                    <div v-else-if="field.type === 'select'" class="sm:max-w-[280px]">
                        <FieldLabel :hint="field.hint">{{ field.label }}</FieldLabel>
                        <Select v-model="(form.configuration[field.key] as string | null)" :aria-label="field.label">
                            <option :value="null">—</option>
                            <option v-for="option in field.options ?? []" :key="option.value" :value="option.value">{{ option.label }}</option>
                        </Select>
                    </div>

                    <div v-else-if="field.type === 'tags'">
                        <FieldLabel :hint="field.hint">{{ field.label }}</FieldLabel>
                        <div class="flex flex-wrap gap-1.5 px-1.5 py-1.5 min-h-[34px] border border-line-strong rounded-md bg-surface items-center">
                            <span
                                v-for="(tag, index) in tagValues(field.key)"
                                :key="`${tag}-${index}`"
                                class="inline-flex items-center gap-1 h-[22px] pl-2 pr-1 border border-line bg-surface-2 rounded-full text-[11.5px] font-mono text-ink-700"
                            >
                                {{ tag }}
                                <button
                                    type="button"
                                    class="w-4 h-4 rounded-full grid place-items-center text-ink-400 hover:bg-line-strong hover:text-ink-700"
                                    :aria-label="t('attributes_settings.remove_option')"
                                    @click="removeTag(field.key, index)"
                                ><Icon name="x" cls="sm" /></button>
                            </span>
                            <input
                                v-model="tagDrafts[field.key]"
                                type="text"
                                class="flex-1 min-w-[120px] h-[22px] px-1 bg-transparent text-[12.5px] font-mono text-ink-900 outline-none"
                                :list="field.suggestions?.length ? `${field.key}-suggestions` : undefined"
                                :placeholder="t('attributes_settings.tags_placeholder')"
                                :aria-label="field.label"
                                @keydown.enter.prevent="addTag(field.key)"
                            />
                            <datalist v-if="field.suggestions?.length" :id="`${field.key}-suggestions`">
                                <option v-for="suggestion in field.suggestions" :key="suggestion" :value="suggestion" />
                            </datalist>
                        </div>
                    </div>

                    <div v-else-if="field.type === 'lookups'">
                        <div class="flex items-center justify-between mb-1.5">
                            <FieldLabel :hint="field.hint" class="!mb-0">{{ field.label }}</FieldLabel>
                            <Button variant="primary" size="sm" icon="plus" @click="addLookup(field.key)">{{ t('attributes_settings.add_option') }}</Button>
                        </div>

                        <div v-if="!lookupRows[field.key]?.length" class="px-6 py-8 text-center text-xs text-ink-500 border border-dashed border-line rounded-md">
                            {{ t('attributes_settings.no_options') }}
                        </div>

                        <div v-else class="flex flex-col gap-2">
                            <div v-for="(lookup, index) in lookupRows[field.key]" :key="index" class="grid sm:grid-cols-[1fr_1fr_auto] gap-2">
                                <TextInput v-model="lookup.label" :placeholder="t('attributes_settings.option_label_placeholder')" />
                                <TextInput v-model="lookup.value" mono :placeholder="t('attributes_settings.option_value_placeholder')" />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    :aria-label="t('attributes_settings.remove_option')"
                                    class="!w-[26px] !h-[26px] self-center text-ink-700 hover:text-danger"
                                    @click="removeLookup(field.key, index)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Section>
    </SettingsShell>

    <ConfirmDialog
        v-model:open="deleting"
        :title="t('attributes_settings.confirm_delete_title')"
        :description="t('attributes_settings.confirm_delete_body', { name: attribute.name })"
        :confirm-label="t('common.delete')"
        tone="danger"
        @confirm="confirmDestroy"
    />
</template>
