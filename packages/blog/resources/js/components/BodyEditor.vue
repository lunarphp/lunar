<script setup lang="ts">
import Link from '@tiptap/extension-link';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

// tiptap document (JSON). Kept structured, not HTML — the storefront renders it
// to sanitised HTML server-side, so untrusted markup never round-trips.
type TiptapDoc = Record<string, unknown> | null;

const props = defineProps<{
    modelValue: TiptapDoc;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: TiptapDoc];
}>();

const fullscreen = ref(false);
// A stable anchor left in normal flow (the overlay itself is teleported to
// body). Used to measure the page-content region so fullscreen fills that area
// only — beside the menu and below the page header, not the whole viewport.
const anchor = ref<HTMLElement | null>(null);
const overlayStyle = ref<Record<string, string>>({});

const editor = useEditor({
    content: props.modelValue ?? '',
    extensions: [StarterKit, Link.configure({ openOnClick: false })],
    editorProps: { attributes: { class: 'blog-body' } },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.isEmpty ? null : editor.getJSON());
    },
});

function btn(active: boolean): string {
    return [
        'h-7 rounded px-2 text-[12px] font-medium transition-colors',
        active
            ? 'bg-line text-ink-900'
            : 'text-ink-500 hover:bg-line/60 hover:text-ink-900',
    ].join(' ');
}

function computeOverlay(): void {
    const host =
        (anchor.value?.closest('[data-page-content]') as HTMLElement | null) ??
        document.querySelector('main');
    const rect = host?.getBoundingClientRect();

    if (!rect) {
        return;
    }

    overlayStyle.value = {
        position: 'fixed',
        left: `${rect.left}px`,
        top: `${rect.top}px`,
        width: `${rect.width}px`,
        height: `${window.innerHeight - rect.top}px`,
        zIndex: '40',
    };
}

function enterFullscreen(): void {
    computeOverlay();
    fullscreen.value = true;
    window.addEventListener('resize', computeOverlay);
    window.addEventListener('scroll', computeOverlay, true);
}

function exitFullscreen(): void {
    fullscreen.value = false;
    window.removeEventListener('resize', computeOverlay);
    window.removeEventListener('scroll', computeOverlay, true);
}

function toggleFullscreen(): void {
    if (fullscreen.value) {
        exitFullscreen();
    } else {
        enterFullscreen();
    }
}

function setLink(): void {
    const previous = editor.value?.getAttributes('link').href ?? '';
    const url = window.prompt('Link URL', previous);

    if (url === null) {
        return;
    }

    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();

        return;
    }

    editor.value
        ?.chain()
        .focus()
        .extendMarkRange('link')
        .setLink({ href: url })
        .run();
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape' && fullscreen.value) {
        exitFullscreen();
    }
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('resize', computeOverlay);
    window.removeEventListener('scroll', computeOverlay, true);
    editor.value?.destroy();
});
</script>

<template>
    <div>
        <span ref="anchor" class="hidden" aria-hidden="true" />

        <Teleport to="body" :disabled="!fullscreen">
            <div
                :class="[
                    'bg-surface flex flex-col',
                    fullscreen
                        ? 'border-line border shadow-2xl'
                        : 'border-line-strong relative rounded-md border',
                ]"
                :style="fullscreen ? overlayStyle : undefined"
            >
                <div
                    v-if="editor"
                    class="border-line flex flex-wrap items-center gap-0.5 border-b px-1.5 py-1.5"
                >
                    <button
                        type="button"
                        :class="btn(editor.isActive('bold'))"
                        @click="editor.chain().focus().toggleBold().run()"
                    >
                        Bold
                    </button>
                    <button
                        type="button"
                        :class="btn(editor.isActive('italic'))"
                        @click="editor.chain().focus().toggleItalic().run()"
                    >
                        Italic
                    </button>
                    <span class="bg-line mx-1 h-4 w-px" aria-hidden="true" />
                    <button
                        type="button"
                        :class="btn(editor.isActive('heading', { level: 2 }))"
                        @click="
                            editor
                                .chain()
                                .focus()
                                .toggleHeading({ level: 2 })
                                .run()
                        "
                    >
                        H2
                    </button>
                    <button
                        type="button"
                        :class="btn(editor.isActive('heading', { level: 3 }))"
                        @click="
                            editor
                                .chain()
                                .focus()
                                .toggleHeading({ level: 3 })
                                .run()
                        "
                    >
                        H3
                    </button>
                    <span class="bg-line mx-1 h-4 w-px" aria-hidden="true" />
                    <button
                        type="button"
                        :class="btn(editor.isActive('bulletList'))"
                        @click="editor.chain().focus().toggleBulletList().run()"
                    >
                        Bullets
                    </button>
                    <button
                        type="button"
                        :class="btn(editor.isActive('orderedList'))"
                        @click="
                            editor.chain().focus().toggleOrderedList().run()
                        "
                    >
                        Numbered
                    </button>
                    <button
                        type="button"
                        :class="btn(editor.isActive('blockquote'))"
                        @click="editor.chain().focus().toggleBlockquote().run()"
                    >
                        Quote
                    </button>
                    <span class="bg-line mx-1 h-4 w-px" aria-hidden="true" />
                    <button
                        type="button"
                        :class="btn(editor.isActive('link'))"
                        @click="setLink"
                    >
                        Link
                    </button>

                    <button
                        type="button"
                        :class="[btn(false), 'ml-auto']"
                        @click="toggleFullscreen"
                    >
                        {{ fullscreen ? 'Exit fullscreen' : 'Fullscreen' }}
                    </button>
                </div>

                <EditorContent
                    :editor="editor"
                    :class="[
                        'blog-body-scroll',
                        fullscreen
                            ? 'mx-auto w-full max-w-[820px] flex-1 overflow-y-auto px-4'
                            : '',
                    ]"
                />
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
:deep(.blog-body) {
    min-height: 180px;
    padding: 0.75rem;
    outline: none;
    color: var(--color-ink-900, currentColor);
    font-size: 13px;
    line-height: 1.6;
}
.blog-body-scroll :deep(.blog-body h2) {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0.75em 0 0.35em;
}
.blog-body-scroll :deep(.blog-body h3) {
    font-size: 1rem;
    font-weight: 600;
    margin: 0.7em 0 0.3em;
}
.blog-body-scroll :deep(.blog-body p) {
    margin: 0 0 0.6em;
}
.blog-body-scroll :deep(.blog-body ul),
.blog-body-scroll :deep(.blog-body ol) {
    margin: 0 0 0.6em;
    padding-left: 1.4em;
}
.blog-body-scroll :deep(.blog-body ul) {
    list-style: disc;
}
.blog-body-scroll :deep(.blog-body ol) {
    list-style: decimal;
}
.blog-body-scroll :deep(.blog-body blockquote) {
    border-left: 3px solid var(--color-line-strong, #d4d4d8);
    padding-left: 0.9em;
    margin: 0 0 0.6em;
    color: var(--color-ink-500, #71717a);
}
.blog-body-scroll :deep(.blog-body a) {
    color: var(--color-sage, #3f7c6a);
    text-decoration: underline;
}
</style>
