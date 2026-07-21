<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import StatusSegmentedControl from '../../components/StatusSegmentedControl.vue';
import TextInput from '../../components/TextInput.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';

const props = defineProps<{
    urls: { store: string; index: string };
}>();

const form = useForm({
    name: '',
    status: 'active',
});

const { t } = useI18n();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.catalog') },
    { label: t('nav.product_types'), href: props.urls.index },
    { label: t('product-types.create_title'), current: true },
]);

const statusOptions = computed(() => [
    { value: 'active', label: t('product-types.status_active'), tone: 'sage' as const },
    { value: 'draft', label: t('product-types.status_draft'), tone: 'warn' as const },
]);

const submit = (): void => {
    form.post(props.urls.store);
};
</script>

<template>
    <PanelLayout>
        <div data-screen-label="New product type" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader :title="t('product-types.create_title')">
                <template #icon>
                    <Link
                        :href="urls.index"
                        class="text-ink-500 hover:text-ink-900 shrink-0 self-center"
                        :aria-label="t('product-types.back_to_product_types')"
                    >
                        <Icon name="arrowLeft" />
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[720px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />
                <form class="bg-surface border border-line rounded-xl shadow-sm p-5" @submit.prevent="submit">
                    <div class="pb-5 border-b border-line mb-5">
                        <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('product-types.section_basics') }}</h2>
                        <div class="text-xs text-ink-500 leading-normal max-w-[560px]">
                            {{ t('product-types.create_description') }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <FieldLabel for="product-type-name" required>{{ t('product-types.field_name') }}</FieldLabel>
                            <TextInput id="product-type-name" v-model="form.name" :invalid="!!form.errors.name" autofocus />
                            <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                            <div class="mt-1 text-[11.5px] text-ink-500">{{ t('product-types.field_name_create_hint') }}</div>
                        </div>
                        <div class="max-w-[280px]">
                            <FieldLabel>{{ t('product-types.field_status') }}</FieldLabel>
                            <StatusSegmentedControl v-model="form.status" :options="statusOptions" />
                            <div class="mt-1.5 text-[11.5px] text-ink-500">
                                {{ form.status === 'active' ? t('product-types.status_active_help') : t('product-types.status_draft_help') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-line">
                        <Button type="submit" variant="primary" :disabled="form.processing">{{ t('product-types.create_product_type') }}</Button>
                    </div>
                </form>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
