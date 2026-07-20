<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import ParentCollectionPicker, { type ParentOption } from '../../components/ParentCollectionPicker.vue';
import Select from '../../components/Select.vue';
import StatusSegmentedControl from '../../components/StatusSegmentedControl.vue';
import TextInput from '../../components/TextInput.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';

const props = defineProps<{
    groups: { id: number; name: string }[];
    preselected: { group_id: number | null; parent: ParentOption | null };
    urls: { store: string; index: string; collectionsSearch: string };
}>();

const { t } = useI18n();

const parent = ref<ParentOption | null>(props.preselected.parent);

const form = useForm<{
    name: string;
    collection_group_id: number | null;
    parent_id: number | null;
    status: string;
}>({
    name: '',
    collection_group_id: props.preselected.group_id ?? props.groups[0]?.id ?? null,
    parent_id: props.preselected.parent?.id ?? null,
    status: 'draft',
});

watch(parent, (option) => {
    form.parent_id = option?.id ?? null;
});

// Hierarchies never cross groups, so a group change resets the parent.
watch(
    () => form.collection_group_id,
    () => {
        parent.value = null;
    },
);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.catalog') },
    { label: t('nav.collections'), href: props.urls.index },
    { label: t('collections.create_title'), current: true },
]);

const statusOptions = computed(() => [
    { value: 'draft', label: t('collections.status_draft'), tone: 'warn' as const },
    { value: 'published', label: t('collections.status_published'), tone: 'sage' as const },
]);

const statusHelp = computed(() =>
    form.status === 'published' ? t('collections.status_published_help') : t('collections.status_draft_help'));

const submit = (): void => {
    form.post(props.urls.store);
};
</script>

<template>
    <PanelLayout>
        <div data-screen-label="New collection" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader :title="t('collections.create_title')">
                <template #icon>
                    <Link
                        :href="urls.index"
                        class="text-ink-500 hover:text-ink-900 shrink-0 self-center"
                        :aria-label="t('collections.back_to_collections')"
                    >
                        <Icon name="arrowLeft" />
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[720px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />
                <form class="bg-surface border border-line rounded-xl shadow-sm p-5" @submit.prevent="submit">
                    <div class="pb-5 border-b border-line mb-5">
                        <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('collections.section_basics') }}</h2>
                        <div class="text-xs text-ink-500 leading-normal max-w-[560px]">
                            {{ t('collections.create_description') }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <FieldLabel for="collection-name" required>{{ t('collections.field_name') }}</FieldLabel>
                            <TextInput id="collection-name" v-model="form.name" :invalid="!!form.errors.name" autofocus />
                            <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                            <div class="mt-1 text-[11.5px] text-ink-500">{{ t('collections.field_name_create_hint') }}</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <FieldLabel for="collection-group" required>{{ t('collections.field_group') }}</FieldLabel>
                                <Select id="collection-group" v-model="form.collection_group_id" :invalid="!!form.errors.collection_group_id">
                                    <option v-for="group in groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                                </Select>
                                <div v-if="form.errors.collection_group_id" class="mt-1 text-[11px] text-danger">{{ form.errors.collection_group_id }}</div>
                            </div>
                            <div>
                                <FieldLabel>{{ t('collections.field_parent') }}</FieldLabel>
                                <ParentCollectionPicker
                                    v-model="parent"
                                    :search-url="urls.collectionsSearch"
                                    :group-id="form.collection_group_id"
                                    :invalid="!!form.errors.parent_id"
                                />
                                <div v-if="form.errors.parent_id" class="mt-1 text-[11px] text-danger">{{ form.errors.parent_id }}</div>
                                <div class="mt-1 text-[11.5px] text-ink-500">{{ t('collections.field_parent_hint') }}</div>
                            </div>
                        </div>

                        <div class="max-w-[280px]">
                            <FieldLabel>{{ t('collections.field_status') }}</FieldLabel>
                            <StatusSegmentedControl v-model="form.status" :options="statusOptions" />
                            <div class="mt-1.5 text-[11.5px] text-ink-500">{{ statusHelp }}</div>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-line">
                        <Button type="submit" variant="primary" :disabled="form.processing">{{ t('collections.create_collection') }}</Button>
                    </div>
                </form>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
