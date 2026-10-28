<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Button, Checkbox, TextInput } from '@lunarphp/panel';
import type { BundleComponentRow } from '../types';
import VariantThumb from './VariantThumb.vue';

// One ordered list of components: the fixed list, or one group's options.
// Every edit is emitted with the row and saved by the editor immediately.
defineProps<{
    components: BundleComponentRow[];
    withDefault?: boolean;
    disabled?: boolean;
    emptyText: string;
}>();

const emit = defineEmits<{
    quantity: [component: BundleComponentRow, quantity: number];
    move: [component: BundleComponentRow, direction: -1 | 1];
    remove: [component: BundleComponentRow];
    default: [component: BundleComponentRow, value: boolean];
}>();

const { t } = useI18n();

const onQuantity = (component: BundleComponentRow, event: Event): void => {
    const quantity = Math.max(1, Math.floor(Number((event.target as HTMLInputElement).value)) || 1);

    if (quantity !== component.quantity) {
        emit('quantity', component, quantity);
    }
};
</script>

<template>
    <div v-if="!components.length" class="px-3 py-3 text-[12px] text-ink-500">{{ emptyText }}</div>
    <ul v-else class="divide-y divide-line">
        <li
            v-for="(component, index) in components"
            :key="component.id"
            class="flex items-center gap-3 px-3 py-2"
        >
            <VariantThumb :variant="component.variant" class="flex-1" />

            <label v-if="withDefault" class="flex items-center gap-1.5 text-[11px] text-ink-500 shrink-0">
                <Checkbox
                    :model-value="component.default"
                    :aria-label="t('bundles::bundles.panel.group_default')"
                    @update:model-value="emit('default', component, $event)"
                />
                {{ t('bundles::bundles.panel.group_default') }}
            </label>

            <div class="w-[84px] shrink-0">
                <TextInput
                    type="number"
                    min="1"
                    step="1"
                    :model-value="component.quantity"
                    :disabled="disabled"
                    :aria-label="t('bundles::bundles.panel.quantity')"
                    @change="onQuantity(component, $event)"
                >
                    <template #prefix>{{ t('bundles::bundles.panel.quantity') }}</template>
                </TextInput>
            </div>

            <div class="flex items-center gap-0.5 shrink-0">
                <Button
                    variant="ghost"
                    size="sm"
                    icon="chevDown"
                    icon-cls="sm rotate-180"
                    :disabled="disabled || index === 0"
                    :aria-label="t('bundles::bundles.panel.move_up')"
                    :title="t('bundles::bundles.panel.move_up')"
                    @click="emit('move', component, -1)"
                />
                <Button
                    variant="ghost"
                    size="sm"
                    icon="chevDown"
                    :disabled="disabled || index === components.length - 1"
                    :aria-label="t('bundles::bundles.panel.move_down')"
                    :title="t('bundles::bundles.panel.move_down')"
                    @click="emit('move', component, 1)"
                />
                <Button
                    variant="ghost"
                    size="sm"
                    icon="trash"
                    :disabled="disabled"
                    :aria-label="t('bundles::bundles.panel.remove')"
                    :title="t('bundles::bundles.panel.remove')"
                    @click="emit('remove', component)"
                />
            </div>
        </li>
    </ul>
</template>
