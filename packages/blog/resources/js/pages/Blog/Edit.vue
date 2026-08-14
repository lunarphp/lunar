<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Button,
    Combobox,
    DatePicker,
    FieldLabel,
    PageHeader,
    PageZone,
    TextInput,
    Textarea,
} from '@lunarphp/panel';
import { computed, ref } from 'vue';
import BodyEditor from '../../components/BodyEditor.vue';
import RecordPicker from '../../components/RecordPicker.vue';
import TokenInput from '../../components/TokenInput.vue';

type TiptapDoc = Record<string, unknown> | null;

interface RecordOption {
    id: number;
    label: string;
    meta: string | null;
    thumbnail: string | null;
}

interface ArticlePayload {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    body: TiptapDoc;
    featured_image: string | null;
    featured_image_alt: string | null;
    author_id: number | null;
    seo_title: string | null;
    seo_description: string | null;
    published_at: string | null;
    categories: string[];
    tags: string[];
    related_products: RecordOption[];
    related_articles: RecordOption[];
}

interface Author {
    id: number;
    name: string;
}

const props = defineProps<{
    article: ArticlePayload | null;
    authors: Author[];
    store_url: string;
    update_url: string | null;
    search_url: string;
}>();

const isEdit = computed(() => props.article !== null);

const authorOptions = computed(() =>
    props.authors.map((author) => ({ value: author.id, label: author.name })),
);

const selectedProducts = ref<RecordOption[]>(
    props.article?.related_products ?? [],
);
const selectedArticles = ref<RecordOption[]>(
    props.article?.related_articles ?? [],
);

// Preview shows the stored image, or a freshly-picked file before upload.
const imagePreview = ref<string | null>(props.article?.featured_image ?? null);
const fileInput = ref<HTMLInputElement | null>(null);
const fileName = ref('');

const form = useForm<{
    title: string;
    slug: string;
    excerpt: string;
    body: TiptapDoc;
    featured_image: File | null;
    featured_image_alt: string;
    remove_featured_image: boolean;
    author_id: number | null;
    seo_title: string;
    seo_description: string;
    published_at: string;
    categories: string[];
    tags: string[];
    related_products: number[];
    related_articles: number[];
}>({
    title: props.article?.title ?? '',
    slug: props.article?.slug ?? '',
    excerpt: props.article?.excerpt ?? '',
    body: props.article?.body ?? null,
    featured_image: null,
    featured_image_alt: props.article?.featured_image_alt ?? '',
    remove_featured_image: false,
    author_id: props.article?.author_id ?? props.authors[0]?.id ?? null,
    seo_title: props.article?.seo_title ?? '',
    seo_description: props.article?.seo_description ?? '',
    published_at: props.article?.published_at ?? '',
    categories: props.article?.categories ?? [],
    tags: props.article?.tags ?? [],
    related_products: selectedProducts.value.map((o) => o.id),
    related_articles: selectedArticles.value.map((o) => o.id),
});

function onImageChange(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.featured_image = file;
    form.remove_featured_image = false;
    fileName.value = file?.name ?? '';
    imagePreview.value = file
        ? URL.createObjectURL(file)
        : (props.article?.featured_image ?? null);
}

function removeImage(): void {
    form.featured_image = null;
    form.remove_featured_image = true;
    imagePreview.value = null;
    fileName.value = '';

    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function submit(): void {
    if (isEdit.value && props.update_url) {
        form.patch(props.update_url, { preserveScroll: true });
    } else {
        form.post(props.store_url);
    }
}
</script>

<template>
    <div data-screen-label="Article" class="contents">
        <PageHeader :title="isEdit ? 'Edit article' : 'New article'">
            <template #actions>
                <Button
                    variant="primary"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{ isEdit ? 'Save' : 'Create' }}
                </Button>
            </template>
        </PageHeader>

        <div
            data-page-content
            class="mx-auto w-full max-w-[1400px] px-4 pt-5 pb-7 sm:px-5 lg:px-7"
        >
            <PageZone region="main" position="before" />

            <form @submit.prevent="submit">
                <div
                    class="flex flex-col gap-6 lg:grid lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start"
                >
                    <!-- Main column: the content itself -->
                    <div class="flex flex-col gap-6">
                        <section
                            class="bg-surface border-line rounded-xl border p-5 shadow-sm"
                        >
                            <div class="border-line mb-4 border-b pb-4">
                                <h2
                                    class="text-ink-900 m-0 text-sm font-semibold tracking-[-0.01em]"
                                >
                                    Content
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <FieldLabel for="article-title" required
                                        >Title</FieldLabel
                                    >
                                    <TextInput
                                        id="article-title"
                                        v-model="form.title"
                                        :invalid="!!form.errors.title"
                                        autofocus
                                    />
                                    <div
                                        v-if="form.errors.title"
                                        class="text-danger mt-1 text-[11px]"
                                    >
                                        {{ form.errors.title }}
                                    </div>
                                </div>

                                <div>
                                    <FieldLabel for="article-slug"
                                        >Slug</FieldLabel
                                    >
                                    <TextInput
                                        id="article-slug"
                                        v-model="form.slug"
                                        :invalid="!!form.errors.slug"
                                        placeholder="Auto-generated from title"
                                    />
                                    <div
                                        v-if="form.errors.slug"
                                        class="text-danger mt-1 text-[11px]"
                                    >
                                        {{ form.errors.slug }}
                                    </div>
                                </div>

                                <div>
                                    <FieldLabel for="article-excerpt"
                                        >Excerpt</FieldLabel
                                    >
                                    <Textarea
                                        id="article-excerpt"
                                        v-model="form.excerpt"
                                        :invalid="!!form.errors.excerpt"
                                    />
                                    <div
                                        class="text-ink-500 mt-1 text-[11.5px]"
                                    >
                                        A short summary shown in listings.
                                    </div>
                                </div>

                                <div>
                                    <FieldLabel>Body</FieldLabel>
                                    <BodyEditor v-model="form.body" />
                                </div>
                            </div>
                        </section>

                        <section
                            class="bg-surface border-line rounded-xl border p-5 shadow-sm"
                        >
                            <div class="border-line mb-4 border-b pb-4">
                                <h2
                                    class="text-ink-900 m-0 text-sm font-semibold tracking-[-0.01em]"
                                >
                                    Featured image
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <div v-if="imagePreview">
                                    <img
                                        :src="imagePreview"
                                        alt=""
                                        class="border-line aspect-video w-full max-w-[420px] rounded-md border object-cover"
                                    />
                                </div>
                                <div>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="hidden"
                                        @change="onImageChange"
                                    />
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <Button
                                            type="button"
                                            size="sm"
                                            icon="upload"
                                            @click="fileInput?.click()"
                                        >
                                            {{
                                                imagePreview
                                                    ? 'Replace image'
                                                    : 'Choose image'
                                            }}
                                        </Button>
                                        <Button
                                            v-if="imagePreview"
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            @click="removeImage"
                                            >Remove</Button
                                        >
                                        <span
                                            v-if="fileName"
                                            class="text-ink-500 max-w-[220px] truncate text-[12px]"
                                            >{{ fileName }}</span
                                        >
                                    </div>
                                    <div
                                        class="text-ink-500 mt-1 text-[11.5px]"
                                    >
                                        JPEG, PNG or WebP, up to 4MB.
                                    </div>
                                    <div
                                        v-if="form.errors.featured_image"
                                        class="text-danger mt-1 text-[11px]"
                                    >
                                        {{ form.errors.featured_image }}
                                    </div>
                                </div>
                                <div class="max-w-[520px]">
                                    <FieldLabel for="article-alt"
                                        >Alt text</FieldLabel
                                    >
                                    <TextInput
                                        id="article-alt"
                                        v-model="form.featured_image_alt"
                                    />
                                    <div
                                        class="text-ink-500 mt-1 text-[11.5px]"
                                    >
                                        Describes the image for screen readers
                                        and image search.
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Sidebar: publishing + metadata -->
                    <div class="flex flex-col gap-6">
                        <section
                            class="bg-surface border-line rounded-xl border p-5 shadow-sm"
                        >
                            <div class="border-line mb-4 border-b pb-4">
                                <h2
                                    class="text-ink-900 m-0 text-sm font-semibold tracking-[-0.01em]"
                                >
                                    Publishing
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <FieldLabel>Publish date</FieldLabel>
                                    <DatePicker
                                        v-model="form.published_at"
                                        :invalid="!!form.errors.published_at"
                                    />
                                    <div
                                        v-if="form.errors.published_at"
                                        class="text-danger mt-1 text-[11px]"
                                    >
                                        {{ form.errors.published_at }}
                                    </div>
                                    <div
                                        class="text-ink-500 mt-1 text-[11.5px]"
                                    >
                                        Leave blank for a draft. Time is UK
                                        (trading) time.
                                    </div>
                                </div>
                                <div>
                                    <FieldLabel required>Author</FieldLabel>
                                    <Combobox
                                        v-model="form.author_id"
                                        :options="authorOptions"
                                        :invalid="!!form.errors.author_id"
                                        placeholder="Search authors"
                                    />
                                    <div
                                        v-if="form.errors.author_id"
                                        class="text-danger mt-1 text-[11px]"
                                    >
                                        {{ form.errors.author_id }}
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section
                            class="bg-surface border-line rounded-xl border p-5 shadow-sm"
                        >
                            <div class="border-line mb-4 border-b pb-4">
                                <h2
                                    class="text-ink-900 m-0 text-sm font-semibold tracking-[-0.01em]"
                                >
                                    SEO
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <FieldLabel for="article-seo-title"
                                        >SEO title</FieldLabel
                                    >
                                    <TextInput
                                        id="article-seo-title"
                                        v-model="form.seo_title"
                                    />
                                    <div
                                        class="text-ink-500 mt-1 text-[11.5px]"
                                    >
                                        Falls back to the title when empty.
                                    </div>
                                </div>
                                <div>
                                    <FieldLabel for="article-seo-description"
                                        >SEO description</FieldLabel
                                    >
                                    <Textarea
                                        id="article-seo-description"
                                        v-model="form.seo_description"
                                    />
                                </div>
                            </div>
                        </section>

                        <section
                            class="bg-surface border-line rounded-xl border p-5 shadow-sm"
                        >
                            <div class="border-line mb-4 border-b pb-4">
                                <h2
                                    class="text-ink-900 m-0 text-sm font-semibold tracking-[-0.01em]"
                                >
                                    Organisation
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <FieldLabel>Categories</FieldLabel>
                                    <TokenInput
                                        v-model="form.categories"
                                        placeholder="Add and press Enter"
                                    />
                                </div>
                                <div>
                                    <FieldLabel>Tags</FieldLabel>
                                    <TokenInput
                                        v-model="form.tags"
                                        placeholder="Add and press Enter"
                                    />
                                </div>
                            </div>
                        </section>

                        <section
                            class="bg-surface border-line rounded-xl border p-5 shadow-sm"
                        >
                            <div class="border-line mb-4 border-b pb-4">
                                <h2
                                    class="text-ink-900 m-0 text-sm font-semibold tracking-[-0.01em]"
                                >
                                    Related
                                </h2>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <FieldLabel>Related products</FieldLabel>
                                    <RecordPicker
                                        v-model="form.related_products"
                                        v-model:selected="selectedProducts"
                                        type="product"
                                        :search-url="search_url"
                                        placeholder="Search products by SKU"
                                    />
                                </div>
                                <div>
                                    <FieldLabel>Related articles</FieldLabel>
                                    <RecordPicker
                                        v-model="form.related_articles"
                                        v-model:selected="selectedArticles"
                                        type="article"
                                        :search-url="search_url"
                                        :exclude="article?.id ?? null"
                                        placeholder="Search articles by title"
                                    />
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <Button
                        type="submit"
                        variant="primary"
                        :disabled="form.processing"
                        >{{
                            isEdit ? 'Save changes' : 'Create article'
                        }}</Button
                    >
                </div>
            </form>

            <PageZone region="main" position="after" />
        </div>
    </div>
</template>
