<script setup lang="ts">
import { computed, watch } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Breadcrumbs, { type BreadcrumbItem } from '../../components/Breadcrumbs.vue';
import Button from '../../components/Button.vue';
import DiscountTypeCard from '../../components/DiscountTypeCard.vue';
import FieldLabel from '../../components/FieldLabel.vue';
import Icon from '../../components/Icon.vue';
import TextInput from '../../components/TextInput.vue';
import PageHeader from '../../components/PageHeader.vue';
import PageZone from '../../components/PageZone.vue';
import PanelLayout from '../../layouts/PanelLayout.vue';

interface DiscountTypeOption {
    class: string;
    label: string;
    component: string;
    buckets: string[];
}

const props = defineProps<{
    types: DiscountTypeOption[];
    urls: { store: string; index: string };
}>();

const { t } = useI18n();

const toLocalInput = (date: Date): string => {
    const pad = (value: number): string => String(value).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};

const form = useForm({
    name: '',
    handle: '',
    type: props.types[0]?.class ?? '',
    starts_at: toLocalInput(new Date()),
});

// snake_case, matching how the Filament admin normalises a discount handle.
const slugify = (value: string): string =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_|_$/g, '');

// The handle tracks the name until it is edited by hand, then stops.
let handleTouched = false;

watch(() => form.name, (name) => {
    if (!handleTouched) {
        form.handle = slugify(name);
    }
});

const onHandleInput = (): void => {
    handleTouched = true;
};

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { label: t('nav.sales') },
    { label: t('nav.discounts'), href: props.urls.index },
    { label: t('discounts.create_title'), current: true },
]);

const submit = (): void => {
    form.post(props.urls.store);
};
</script>

<template>
    <PanelLayout>
        <div data-screen-label="New discount" class="contents">
            <Breadcrumbs :items="breadcrumbs">
                <template #actions>
                    <a href="https://docs.lunarphp.com/" target="_blank" rel="noopener">
                        <Button icon="help"><span class="hidden sm:inline">{{ t('common.docs') }}</span></Button>
                    </a>
                </template>
            </Breadcrumbs>

            <PageHeader :title="t('discounts.create_title')">
                <template #icon>
                    <Link
                        :href="urls.index"
                        class="text-ink-500 hover:text-ink-900 shrink-0 self-center"
                        :aria-label="t('discounts.back_to_discounts')"
                    >
                        <Icon name="arrowLeft" />
                    </Link>
                </template>
            </PageHeader>

            <div class="px-4 sm:px-5 lg:px-7 max-w-[720px] w-full mx-auto pt-5 pb-7">
                <PageZone region="main" position="before" />
                <form class="bg-surface border border-line rounded-xl shadow-sm p-5" @submit.prevent="submit">
                    <div class="pb-5 border-b border-line mb-5">
                        <h2 class="m-0 mb-1 text-sm font-semibold tracking-[-0.01em] text-ink-900">{{ t('discounts.section_details') }}</h2>
                        <div class="text-xs text-ink-500 leading-normal max-w-[560px]">
                            {{ t('discounts.create_description') }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <FieldLabel for="discount-name" required>{{ t('discounts.field_name') }}</FieldLabel>
                            <TextInput id="discount-name" v-model="form.name" :invalid="!!form.errors.name" autofocus />
                            <div v-if="form.errors.name" class="mt-1 text-[11px] text-danger">{{ form.errors.name }}</div>
                            <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_name_create_hint') }}</div>
                        </div>

                        <div>
                            <FieldLabel for="discount-handle" required>{{ t('discounts.field_handle') }}</FieldLabel>
                            <TextInput id="discount-handle" v-model="form.handle" :invalid="!!form.errors.handle" @input="onHandleInput" />
                            <div v-if="form.errors.handle" class="mt-1 text-[11px] text-danger">{{ form.errors.handle }}</div>
                        </div>

                        <div>
                            <FieldLabel required>{{ t('discounts.field_type') }}</FieldLabel>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                                <DiscountTypeCard
                                    v-for="type in types"
                                    :key="type.class"
                                    :label="type.label"
                                    :selected="form.type === type.class"
                                    @select="form.type = type.class"
                                />
                            </div>
                            <div v-if="form.errors.type" class="mt-1 text-[11px] text-danger">{{ form.errors.type }}</div>
                        </div>

                        <div class="max-w-[280px]">
                            <FieldLabel for="discount-starts-at" required>{{ t('discounts.field_starts_at') }}</FieldLabel>
                            <TextInput id="discount-starts-at" v-model="form.starts_at" type="datetime-local" :invalid="!!form.errors.starts_at" />
                            <div v-if="form.errors.starts_at" class="mt-1 text-[11px] text-danger">{{ form.errors.starts_at }}</div>
                        </div>
                    </div>

                    <div class="mt-5 pt-5 border-t border-line">
                        <Button type="submit" variant="primary" :disabled="form.processing">{{ t('discounts.create_discount') }}</Button>
                    </div>
                </form>

                <PageZone region="main" position="after" />
            </div>
        </div>
    </PanelLayout>
</template>
