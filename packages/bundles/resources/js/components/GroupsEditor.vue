<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button, ConfirmDialog, FieldLabel, TextInput } from '@lunarphp/panel';
import type { BundleComponentRow, BundleGroupRow } from '../types';
import ComponentList from './ComponentList.vue';

// Option groups of a configurable bundle. Group fields save on change; the
// component list inside each group is the same list the fixed section uses.
const props = defineProps<{
    groups: BundleGroupRow[];
    components: BundleComponentRow[];
    defaultLanguage: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    add: [];
    update: [group: BundleGroupRow, patch: Partial<Pick<BundleGroupRow, 'name' | 'min_selections' | 'max_selections'>>];
    move: [group: BundleGroupRow, direction: -1 | 1];
    remove: [group: BundleGroupRow];
    'add-option': [group: BundleGroupRow];
    'component-quantity': [component: BundleComponentRow, quantity: number];
    'component-move': [component: BundleComponentRow, direction: -1 | 1];
    'component-remove': [component: BundleComponentRow];
    'component-default': [component: BundleComponentRow, value: boolean];
}>();

const { t } = useI18n();

const optionsOf = (group: BundleGroupRow): BundleComponentRow[] =>
    props.components.filter((component) => component.group_id === group.id);

const nameOf = (group: BundleGroupRow): string => group.name[props.defaultLanguage] ?? group.label ?? '';

const onName = (group: BundleGroupRow, event: Event): void => {
    const value = (event.target as HTMLInputElement).value.trim();

    if (value && value !== nameOf(group)) {
        emit('update', group, { name: { ...group.name, [props.defaultLanguage]: value } });
    }
};

const onLimit = (group: BundleGroupRow, key: 'min_selections' | 'max_selections', event: Event): void => {
    const value = Math.max(key === 'min_selections' ? 0 : 1, Math.floor(Number((event.target as HTMLInputElement).value)) || 0);

    if (value !== group[key]) {
        emit('update', group, { [key]: value });
    }
};

const removing = ref<BundleGroupRow | null>(null);
</script>

<template>
    <div class="flex flex-col gap-3">
        <div
            v-for="(group, index) in groups"
            :key="group.id"
            class="border border-line rounded-lg bg-surface overflow-hidden"
        >
            <div class="flex flex-wrap items-end gap-3 px-3 py-2.5 bg-surface-2 border-b border-line">
                <div class="flex-1 min-w-[180px]">
                    <FieldLabel :for="`bundle-group-${group.id}-name`" :hint="defaultLanguage.toUpperCase()">
                        {{ t('bundles::bundles.panel.group_name') }}
                    </FieldLabel>
                    <TextInput
                        :id="`bundle-group-${group.id}-name`"
                        :model-value="nameOf(group)"
                        :disabled="disabled"
                        @change="onName(group, $event)"
                    />
                </div>
                <div class="w-[72px]">
                    <FieldLabel :for="`bundle-group-${group.id}-min`">{{ t('bundles::bundles.panel.group_min') }}</FieldLabel>
                    <TextInput
                        :id="`bundle-group-${group.id}-min`"
                        type="number"
                        min="0"
                        step="1"
                        :model-value="group.min_selections"
                        :disabled="disabled"
                        @change="onLimit(group, 'min_selections', $event)"
                    />
                </div>
                <div class="w-[72px]">
                    <FieldLabel :for="`bundle-group-${group.id}-max`">{{ t('bundles::bundles.panel.group_max') }}</FieldLabel>
                    <TextInput
                        :id="`bundle-group-${group.id}-max`"
                        type="number"
                        min="1"
                        step="1"
                        :model-value="group.max_selections"
                        :disabled="disabled"
                        @change="onLimit(group, 'max_selections', $event)"
                    />
                </div>
                <div class="flex items-center gap-0.5 pb-0.5">
                    <Button
                        variant="ghost"
                        size="sm"
                        icon="chevDown"
                        icon-cls="sm rotate-180"
                        :disabled="disabled || index === 0"
                        :aria-label="t('bundles::bundles.panel.move_up')"
                        :title="t('bundles::bundles.panel.move_up')"
                        @click="emit('move', group, -1)"
                    />
                    <Button
                        variant="ghost"
                        size="sm"
                        icon="chevDown"
                        :disabled="disabled || index === groups.length - 1"
                        :aria-label="t('bundles::bundles.panel.move_down')"
                        :title="t('bundles::bundles.panel.move_down')"
                        @click="emit('move', group, 1)"
                    />
                    <Button
                        variant="ghost"
                        size="sm"
                        icon="trash"
                        :disabled="disabled"
                        :aria-label="t('bundles::bundles.panel.remove_group')"
                        :title="t('bundles::bundles.panel.remove_group')"
                        @click="removing = group"
                    />
                </div>
            </div>

            <ComponentList
                :components="optionsOf(group)"
                :disabled="disabled"
                :empty-text="t('bundles::bundles.panel.group_empty')"
                with-default
                @quantity="(component, quantity) => emit('component-quantity', component, quantity)"
                @move="(component, direction) => emit('component-move', component, direction)"
                @remove="(component) => emit('component-remove', component)"
                @default="(component, value) => emit('component-default', component, value)"
            />

            <div class="px-3 py-2 border-t border-line">
                <Button size="sm" icon="plus" :disabled="disabled" @click="emit('add-option', group)">
                    {{ t('bundles::bundles.panel.add_option') }}
                </Button>
            </div>
        </div>

        <div>
            <Button icon="plus" :disabled="disabled" @click="emit('add')">{{ t('bundles::bundles.panel.add_group') }}</Button>
        </div>

        <ConfirmDialog
            :open="removing !== null"
            :title="t('bundles::bundles.panel.remove_group')"
            :description="t('bundles::bundles.panel.confirm_remove_group')"
            tone="danger"
            @update:open="(open) => { if (!open) removing = null; }"
            @confirm="() => { if (removing) emit('remove', removing); }"
        />
    </div>
</template>
