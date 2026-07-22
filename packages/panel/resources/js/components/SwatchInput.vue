<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from './Icon.vue';
import Tooltip from './Tooltip.vue';

const props = defineProps<{
    // Current swatch image url, or null when unset.
    modelValue: string | null;
    // Upload/delete endpoint for a saved value; null while the value is unsaved.
    url: string | null;
    disabled?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string | null] }>();

const { t } = useI18n();

const input = ref<HTMLInputElement | null>(null);
const busy = ref(false);

const pick = (): void => {
    if (props.disabled || !props.url) {
        return;
    }

    input.value?.click();
};

const onFile = (event: Event): void => {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file || !props.url) {
        return;
    }

    // Show the picked file straight away; the stored url arrives on next load.
    emit('update:modelValue', URL.createObjectURL(file));
    busy.value = true;

    router.post(props.url, { file }, {
        forceFormData: true,
        preserveScroll: true,
        // Keep pending name/colour/position edits in the form intact.
        preserveState: true,
        onFinish: () => {
            busy.value = false;

            if (input.value) {
                input.value.value = '';
            }
        },
    });
};

const remove = (): void => {
    if (props.disabled || !props.url) {
        return;
    }

    emit('update:modelValue', null);
    busy.value = true;

    router.delete(props.url, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            busy.value = false;
        },
    });
};
</script>

<template>
    <div class="flex items-center gap-2">
        <Tooltip :text="!url ? t('product_options.swatch_save_first') : ''" :disabled="!!url">
            <button
                type="button"
                class="inline-flex items-center gap-1.5 h-8 px-2.5 border border-line-strong rounded-md bg-surface text-[12px] text-ink-700 hover:bg-surface-2 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="disabled || !url || busy"
                @click="pick"
            >
                <Icon name="upload" cls="sm" />
                <span>{{ modelValue ? t('product_options.swatch_replace') : t('product_options.swatch_upload') }}</span>
            </button>
        </Tooltip>

        <button
            v-if="modelValue"
            type="button"
            class="inline-flex items-center justify-center w-8 h-8 rounded-md text-ink-500 hover:text-danger disabled:opacity-50"
            :disabled="disabled || busy"
            :aria-label="t('product_options.swatch_remove_label')"
            @click="remove"
        >
            <Icon name="trash" cls="sm" />
        </button>

        <input ref="input" type="file" accept="image/*" class="hidden" @change="onFile" />
    </div>
</template>
