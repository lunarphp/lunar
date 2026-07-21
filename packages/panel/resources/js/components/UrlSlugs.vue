<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { regionForLanguage } from '../lib/flags';
import Button from './Button.vue';
import Checkbox from './Checkbox.vue';
import ConfirmDialog from './ConfirmDialog.vue';
import Dialog from './Dialog.vue';
import FieldLabel from './FieldLabel.vue';
import Flag from './Flag.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';
import Select from './Select.vue';
import StatusBadge from './StatusBadge.vue';
import TextInput from './TextInput.vue';
import type { LanguageOption } from './TranslatedInput.vue';

export interface UrlRow {
    id: number;
    slug: string;
    default: boolean;
    language_id: number;
    language_code: string;
    update_url: string;
    destroy_url: string;
}

const props = defineProps<{
    urls: UrlRow[];
    languages: LanguageOption[];
    storeUrl: string;
    pathPrefix: string;
    storefrontUrl: string | null;
}>();

const { t } = useI18n();

// Rows persist immediately (they are sub-resources, not part of the edit
// draft): slug edits debounce into a PUT, default/delete act at once.
const slugs = reactive<Record<number, string>>({});
const rowErrors = reactive<Record<number, string>>({});

watch(
    () => props.urls,
    (urls) => {
        for (const url of urls) {
            if (!(url.id in slugs)) {
                slugs[url.id] = url.slug;
            }
        }

        for (const id of Object.keys(slugs).map(Number)) {
            if (!urls.some((url) => url.id === id)) {
                delete slugs[id];
                delete rowErrors[id];
            }
        }
    },
    { immediate: true, deep: true },
);

const timers = new Map<number, ReturnType<typeof setTimeout>>();

const save = (url: UrlRow, extra: Partial<{ default: boolean }> = {}): void => {
    delete rowErrors[url.id];

    router.put(
        url.update_url,
        {
            language_id: url.language_id,
            slug: slugs[url.id] ?? url.slug,
            default: extra.default ?? url.default,
        },
        {
            preserveScroll: true,
            onError: (errors) => {
                rowErrors[url.id] = errors.slug ?? Object.values(errors)[0] ?? '';
            },
        },
    );
};

const onSlugInput = (url: UrlRow, value: string): void => {
    slugs[url.id] = value;

    clearTimeout(timers.get(url.id));
    timers.set(url.id, setTimeout(() => {
        if (slugs[url.id] !== url.slug) {
            save(url);
        }
    }, 600));
};

const setDefault = (url: UrlRow): void => {
    if (!url.default) {
        save(url, { default: true });
    }
};

// Deleting re-points the default server-side; confirm first since links stop
// resolving immediately.
const pendingDelete = ref<UrlRow | null>(null);

const confirmDeleteOpen = computed({
    get: () => pendingDelete.value !== null,
    set: (value: boolean) => {
        if (!value) {
            pendingDelete.value = null;
        }
    },
});

const confirmDelete = (): void => {
    if (pendingDelete.value) {
        router.delete(pendingDelete.value.destroy_url, { preserveScroll: true });
        pendingDelete.value = null;
    }
};

// Add dialog: any language is valid — an element may carry several slugs in
// the same language (only one is the default; the rest act as alias/redirect
// slugs on the storefront).
const addOpen = ref(false);
const addLanguageId = ref<number | ''>('');
const addSlug = ref('');
const addDefault = ref(false);
const addErrors = ref<Record<string, string>>({});
const adding = ref(false);

watch(addOpen, (open) => {
    if (open) {
        addLanguageId.value = props.languages.find((language) => language.default)?.id ?? props.languages[0]?.id ?? '';
        addSlug.value = '';
        addDefault.value = props.urls.length === 0;
        addErrors.value = {};
    }
});

const submitAdd = (): void => {
    if (addLanguageId.value === '' || !addSlug.value) {
        return;
    }

    addErrors.value = {};
    adding.value = true;

    router.post(
        props.storeUrl,
        { language_id: Number(addLanguageId.value), slug: addSlug.value, default: addDefault.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                addOpen.value = false;
            },
            onError: (errors) => {
                addErrors.value = errors;
            },
            onFinish: () => {
                adding.value = false;
            },
        },
    );
};

const previewHref = (url: UrlRow): string | null => {
    if (!props.storefrontUrl) {
        return null;
    }

    return props.storefrontUrl.replace(/\/$/, '') + props.pathPrefix + (slugs[url.id] ?? url.slug);
};
</script>

<template>
    <Section :title="t('urls.title')">
        <template #desc>{{ t('urls.description', { count: urls.length }) }}</template>
        <template #actions>
            <Button icon="plus" size="sm" @click="addOpen = true">{{ t('urls.add_url') }}</Button>
        </template>

        <div v-if="!urls.length" class="text-center px-5 py-6 border border-dashed border-line-strong rounded-md bg-surface-2 text-xs text-ink-500">
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
                    <tr v-for="url in urls" :key="url.id" class="last:[&_td]:border-b-0">
                        <td class="pl-4 whitespace-nowrap align-top pt-3.5 pb-2.5 px-3 border-b border-line">
                            <span class="inline-flex items-center gap-1.5">
                                <Flag :code="regionForLanguage(url.language_code)" class="text-[13px]" />
                                <span class="font-mono text-[11.5px] text-ink-700">{{ url.language_code }}</span>
                            </span>
                        </td>
                        <td class="align-top py-2 px-3 border-b border-line">
                            <TextInput
                                :model-value="slugs[url.id] ?? url.slug"
                                :invalid="!!rowErrors[url.id]"
                                mono
                                :aria-label="`${url.language_code} slug`"
                                @update:model-value="(value) => onSlugInput(url, value)"
                            >
                                <template #prefix>{{ pathPrefix }}</template>
                            </TextInput>
                            <div v-if="rowErrors[url.id]" class="text-[11px] text-danger mt-1 flex items-center gap-1">
                                <Icon name="alert" cls="sm" />{{ rowErrors[url.id] }}
                            </div>
                            <a
                                v-else-if="previewHref(url)"
                                class="group font-mono text-[11px] text-ink-500 inline-flex items-center gap-1 mt-1 rounded p-0.5 -m-0.5 hover:bg-surface-2"
                                :href="previewHref(url)!"
                                target="_blank"
                                rel="noopener"
                            >
                                <span class="truncate max-w-[320px]">{{ previewHref(url) }}</span>
                                <Icon name="externalLink" cls="sm" class="opacity-40 group-hover:opacity-85" />
                            </a>
                        </td>
                        <td class="align-top pt-2.5 py-2.5 px-3 border-b border-line">
                            <button
                                type="button"
                                :class="url.default ? 'cursor-default' : 'cursor-pointer'"
                                :aria-pressed="url.default"
                                @click="setDefault(url)"
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
                                @click="pendingDelete = url"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog
            :open="addOpen"
            :title="t('urls.add_url')"
            :description="t('urls.add_description')"
            @update:open="addOpen = $event"
        >
            <div class="flex flex-col gap-3">
                <div>
                    <FieldLabel for="url-language">{{ t('urls.column_language') }}</FieldLabel>
                    <Select id="url-language" v-model="addLanguageId" :invalid="!!addErrors.language_id">
                        <option v-for="language in languages" :key="language.id" :value="language.id">
                            {{ language.name ?? language.code }}
                        </option>
                    </Select>
                    <div v-if="addErrors.language_id" class="mt-1 text-[11px] text-danger">{{ addErrors.language_id }}</div>
                </div>
                <div>
                    <FieldLabel for="url-slug" required>{{ t('urls.field_slug') }}</FieldLabel>
                    <TextInput
                        id="url-slug"
                        v-model="addSlug"
                        mono
                        :invalid="!!addErrors.slug"
                        :placeholder="t('urls.slug_placeholder')"
                        @keydown.enter.prevent="submitAdd"
                    >
                        <template #prefix>{{ pathPrefix }}</template>
                    </TextInput>
                    <div v-if="addErrors.slug" class="mt-1 text-[11px] text-danger">{{ addErrors.slug }}</div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <Checkbox :model-value="addDefault" :aria-label="t('urls.make_default')" @update:model-value="addDefault = $event" />
                    <span class="text-[12.5px] text-ink-900">{{ t('urls.make_default') }}</span>
                </label>
                <div class="text-[11.5px] text-ink-500">{{ t('urls.non_default_hint') }}</div>
            </div>

            <template #footer>
                <Button variant="ghost" @click="addOpen = false">{{ t('common.cancel') }}</Button>
                <Button variant="primary" :disabled="addLanguageId === '' || !addSlug || adding" @click="submitAdd">
                    {{ t('urls.add') }}
                </Button>
            </template>
        </Dialog>

        <ConfirmDialog
            v-model:open="confirmDeleteOpen"
            :title="t('urls.confirm_remove_title')"
            :description="t('urls.confirm_remove_body')"
            tone="danger"
            :confirm-label="t('common.delete')"
            @confirm="confirmDelete"
        />
    </Section>
</template>
