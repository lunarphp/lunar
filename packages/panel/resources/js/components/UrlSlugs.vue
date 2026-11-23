<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useFormSlice } from '../composables/useFormSlice';
import { regionForLanguage } from '../lib/flags';
import Button from './Button.vue';
import Checkbox from './Checkbox.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import Flag from './Flag.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import StatusBadge from './StatusBadge.vue';
import TextInput from './TextInput.vue';
import type { LanguageOption } from './TranslatedInput.vue';

/** One slug in a language's `url:{code}` draft value; new rows have a null id. */
interface SlugRow {
    id: number | null;
    slug: string;
    default: boolean;
}

const props = defineProps<{
    languages: LanguageOption[];
    pathPrefix: string;
    storefrontUrl: string | null;
}>();

const { t } = useI18n();

// Each language's slugs are a draft value: editing, adding, removing and
// choosing the default dirty the page form and land with Save.
const slice = useFormSlice<Record<string, SlugRow[]>>('url');

const rowsFor = (code: string): SlugRow[] => slice.values[code] ?? [];

const setRows = (code: string, rows: SlugRow[]): void => {
    slice.values[code] = rows;
};

const rows = computed(() =>
    props.languages.flatMap((language) =>
        rowsFor(language.code).map((row, index) => ({
            ...row,
            code: language.code,
            index,
            error: slice.errors.value[`${language.code}.${index}.slug`]
                ?? slice.errors.value[`${language.code}.${index}.id`]
                ?? slice.errors.value[`${language.code}.${index}`],
        }))));

const languageErrors = computed(() =>
    props.languages.map((language) => slice.errors.value[language.code]).filter(Boolean));

const updateSlug = (code: string, index: number, slug: string): void => {
    setRows(code, rowsFor(code).map((row, i) => (i === index ? { ...row, slug } : row)));
};

const setDefault = (code: string, index: number): void => {
    setRows(code, rowsFor(code).map((row, i) => ({ ...row, default: i === index })));
};

// Removing a language's default hands it to the first remaining slug, the
// same rule the server applies.
const remove = (code: string, index: number): void => {
    const remaining = rowsFor(code).filter((_, i) => i !== index);

    if (remaining.length && !remaining.some((row) => row.default)) {
        remaining[0] = { ...remaining[0], default: true };
    }

    setRows(code, remaining);
};

// Add dialog: any language is valid — an element may carry several slugs in
// the same language (only one is the default; the rest act as alias/redirect
// slugs on the storefront).
const addOpen = ref(false);
const addCode = ref('');
const addSlug = ref('');
const addDefault = ref(false);

watch(addOpen, (open) => {
    if (open) {
        addCode.value = props.languages.find((language) => language.default)?.code ?? props.languages[0]?.code ?? '';
        addSlug.value = '';
        addDefault.value = rows.value.length === 0;
    }
});

const submitAdd = (): void => {
    const slug = addSlug.value.trim();

    if (!addCode.value || !slug) {
        return;
    }

    const current = rowsFor(addCode.value);
    const makeDefault = addDefault.value || current.length === 0;

    setRows(addCode.value, [
        ...current.map((row) => (makeDefault ? { ...row, default: false } : row)),
        { id: null, slug, default: makeDefault },
    ]);

    addOpen.value = false;
};

const previewHref = (slug: string): string | null => {
    if (!props.storefrontUrl || !slug) {
        return null;
    }

    return props.storefrontUrl.replace(/\/$/, '') + props.pathPrefix + slug;
};
</script>

<template>
    <Section :title="t('urls.title')">
        <template #desc>{{ t('urls.description', { count: rows.length }) }}</template>
        <template #actions>
            <Button icon="plus" size="sm" @click="addOpen = true">{{ t('urls.add_url') }}</Button>
        </template>

        <div v-if="!rows.length" class="text-center px-5 py-6 border border-dashed border-line-strong rounded-md bg-surface-2 text-xs text-ink-500">
            {{ t('urls.empty') }}
        </div>

        <div v-else class="border border-line rounded-lg overflow-x-auto bg-surface">
            <table class="w-full min-w-[520px] border-collapse">
                <thead>
                    <tr>
                        <th class="w-[96px] pl-4 text-left py-2.5 px-3 text-[11px] font-medium text-ink-500 tracking-wider bg-surface-2 border-b border-line">{{ t('urls.column_language') }}</th>
                        <th class="text-left py-2.5 px-3 text-[11px] font-medium text-ink-500 tracking-wider bg-surface-2 border-b border-line">{{ t('urls.column_slug') }}</th>
                        <th class="w-[84px] text-left py-2.5 px-3 text-[11px] font-medium text-ink-500 tracking-wider bg-surface-2 border-b border-line">{{ t('urls.column_default') }}</th>
                        <th class="w-10 py-2.5 px-3 bg-surface-2 border-b border-line" />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="url in rows" :key="`${url.code}-${url.index}`" class="last:[&_td]:border-b-0">
                        <td class="pl-4 whitespace-nowrap align-top pt-3.5 pb-2.5 px-3 border-b border-line">
                            <span class="inline-flex items-center gap-1.5">
                                <Flag :code="regionForLanguage(url.code)" class="text-[13px]" />
                                <span class="font-mono text-[11.5px] text-ink-700">{{ url.code }}</span>
                            </span>
                        </td>
                        <td class="align-top py-2 px-3 border-b border-line">
                            <TextInput
                                :model-value="url.slug"
                                :invalid="!!url.error"
                                mono
                                :aria-label="`${url.code} slug`"
                                @update:model-value="(value) => updateSlug(url.code, url.index, value)"
                            >
                                <template #prefix>{{ pathPrefix }}</template>
                            </TextInput>
                            <div v-if="url.error" class="text-[11px] text-danger mt-1 flex items-center gap-1">
                                <Icon name="alert" cls="sm" />{{ url.error }}
                            </div>
                            <a
                                v-else-if="previewHref(url.slug)"
                                class="group font-mono text-[11px] text-ink-500 inline-flex items-center gap-1 mt-1 rounded p-0.5 -m-0.5 hover:bg-surface-2"
                                :href="previewHref(url.slug)!"
                                target="_blank"
                                rel="noopener"
                            >
                                <span class="truncate max-w-[320px]">{{ previewHref(url.slug) }}</span>
                                <Icon name="externalLink" cls="sm" class="opacity-40 group-hover:opacity-85" />
                            </a>
                        </td>
                        <td class="align-top pt-2.5 py-2.5 px-3 border-b border-line">
                            <button
                                type="button"
                                :class="url.default ? 'cursor-default' : 'cursor-pointer'"
                                :aria-pressed="url.default"
                                @click="setDefault(url.code, url.index)"
                            >
                                <StatusBadge :tone="url.default ? 'sage' : 'neutral'" size="sm">
                                    <Icon v-if="url.default" name="check" cls="sm" />
                                    {{ url.default ? t('urls.default_badge') : t('urls.set_default') }}
                                </StatusBadge>
                            </button>
                        </td>
                        <td class="align-top py-2 px-3 border-b border-line">
                            <Button
                                variant="ghost"
                                size="sm"
                                icon="trash"
                                class="!text-ink-400 hover:!text-danger"
                                :aria-label="t('urls.remove')"
                                @click="remove(url.code, url.index)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-for="message in languageErrors" :key="message" class="mt-1 text-[11px] text-danger">{{ message }}</div>

        <Dialog
            :open="addOpen"
            :title="t('urls.add_url')"
            :description="t('urls.add_description')"
            @update:open="addOpen = $event"
        >
            <div class="flex flex-col gap-3">
                <div>
                    <FieldLabel for="url-language">{{ t('urls.column_language') }}</FieldLabel>
                    <Select id="url-language" v-model="addCode">
                        <option v-for="language in languages" :key="language.id" :value="language.code">
                            {{ language.name ?? language.code }}
                        </option>
                    </Select>
                </div>
                <div>
                    <FieldLabel for="url-slug" required>{{ t('urls.field_slug') }}</FieldLabel>
                    <TextInput
                        id="url-slug"
                        v-model="addSlug"
                        mono
                        :placeholder="t('urls.slug_placeholder')"
                        @keydown.enter.prevent="submitAdd"
                    >
                        <template #prefix>{{ pathPrefix }}</template>
                    </TextInput>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <Checkbox :model-value="addDefault" :aria-label="t('urls.make_default')" @update:model-value="addDefault = $event" />
                    <span class="text-[12.5px] text-ink-900">{{ t('urls.make_default') }}</span>
                </label>
                <div class="text-[11.5px] text-ink-500">{{ t('urls.non_default_hint') }}</div>
            </div>

            <template #footer>
                <Button variant="ghost" @click="addOpen = false">{{ t('common.cancel') }}</Button>
                <Button variant="primary" :disabled="!addCode || !addSlug.trim()" @click="submitAdd">
                    {{ t('urls.add') }}
                </Button>
            </template>
        </Dialog>
    </Section>
</template>
