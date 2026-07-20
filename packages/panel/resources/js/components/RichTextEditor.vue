<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Button from './Button.vue';

const props = defineProps<{
    modelValue: string;
    invalid?: boolean;
    ariaLabel?: string;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

// Reads and writes HTML so the same value round-trips with the Filament
// admin's rich-text component editing the same field.
const editor = useEditor({
    content: props.modelValue,
    extensions: [StarterKit],
    editorProps: {
        attributes: {
            class: 'prose-editor w-full min-h-[140px] px-2.5 py-2 text-[13px] text-ink-900 leading-normal outline-none',
            ...(props.ariaLabel ? { 'aria-label': props.ariaLabel } : {}),
        },
    },
    onUpdate: ({ editor: instance }) => {
        emit('update:modelValue', instance.isEmpty ? '' : instance.getHTML());
    },
});

// External resets (draft discard, conflict resolution) push the new value in;
// guarded so the editor's own updates don't loop back through setContent.
watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML() && !(value === '' && editor.value.isEmpty)) {
            editor.value.commands.setContent(value);
        }
    },
);

onBeforeUnmount(() => editor.value?.destroy());

const setLink = (): void => {
    if (!editor.value) {
        return;
    }

    const previous = editor.value.getAttributes('link').href as string | undefined;
    const url = window.prompt('URL', previous ?? '');

    if (url === null) {
        return;
    }

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();

        return;
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};
</script>

<template>
    <div
        :class="[
            'border rounded-md overflow-hidden bg-surface transition-[border-color,box-shadow] duration-100 focus-within:border-sage focus-within:ring-3 focus-within:ring-sage/35',
            invalid ? 'border-danger-border' : 'border-line-strong hover:border-ink-300',
        ]"
    >
        <div v-if="editor" class="flex gap-0.5 px-2 py-1.5 border-b border-line bg-surface-2">
            <Button
                variant="ghost"
                size="sm"
                class="!w-[26px] !px-0"
                :class="editor.isActive('bold') ? '!bg-surface !border-line-strong shadow-sm' : ''"
                aria-label="Bold"
                @click="editor.chain().focus().toggleBold().run()"
            ><b>B</b></Button>
            <Button
                variant="ghost"
                size="sm"
                class="!w-[26px] !px-0"
                :class="editor.isActive('italic') ? '!bg-surface !border-line-strong shadow-sm' : ''"
                aria-label="Italic"
                @click="editor.chain().focus().toggleItalic().run()"
            ><i>I</i></Button>
            <Button
                variant="ghost"
                size="sm"
                class="!w-[26px] !px-0"
                :class="editor.isActive('underline') ? '!bg-surface !border-line-strong shadow-sm' : ''"
                aria-label="Underline"
                @click="editor.chain().focus().toggleUnderline().run()"
            ><span class="underline">U</span></Button>
            <div class="w-px bg-line my-0.5 mx-1" />
            <Button
                variant="ghost"
                size="sm"
                :class="editor.isActive('heading', { level: 1 }) ? '!bg-surface !border-line-strong shadow-sm' : ''"
                aria-label="Heading 1"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
            >H1</Button>
            <Button
                variant="ghost"
                size="sm"
                :class="editor.isActive('heading', { level: 2 }) ? '!bg-surface !border-line-strong shadow-sm' : ''"
                aria-label="Heading 2"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
            >H2</Button>
            <Button
                variant="ghost"
                size="sm"
                icon="link"
                :class="editor.isActive('link') ? '!bg-surface !border-line-strong shadow-sm' : ''"
                aria-label="Link"
                @click="setLink"
            />
        </div>
        <EditorContent :editor="editor" />
    </div>
</template>

<style scoped>
/* Minimal typographic treatment for editor content; the storefront owns real
   rendering, this only keeps the editing surface legible. */
:deep(.prose-editor h1) {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0.5em 0 0.25em;
}

:deep(.prose-editor h2) {
    font-size: 1.05rem;
    font-weight: 600;
    margin: 0.5em 0 0.25em;
}

:deep(.prose-editor p) {
    margin: 0 0 0.5em;
}

:deep(.prose-editor a) {
    text-decoration: underline;
}

:deep(.prose-editor ul),
:deep(.prose-editor ol) {
    padding-left: 1.25em;
    margin: 0 0 0.5em;
}
</style>
