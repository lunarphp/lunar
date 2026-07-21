<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import Checkbox from './Checkbox.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';
import TextInput from './TextInput.vue';

export interface PickerAttribute {
    id: number;
    name: string;
    handle: string;
    type: string;
    required: boolean;
}

export interface PickerGroup {
    handle: string;
    name: string;
    attributes: PickerAttribute[];
}

const props = defineProps<{
    // Selected attribute ids; lives on the page's draft form so autosave and
    // conflict handling come free.
    modelValue: number[];
    groups: PickerGroup[];
    title: string;
    description?: string;
    manageUrl?: string | null;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: number[]] }>();

const { t } = useI18n();

const q = ref('');

// Every group starts open; toggles survive prop refreshes for groups that remain.
const openMap = ref<Record<string, boolean>>({});

watch(
    () => props.groups,
    (groups) => {
        const next: Record<string, boolean> = {};
        groups.forEach((group) => {
            next[group.handle] = openMap.value[group.handle] ?? true;
        });
        openMap.value = next;
    },
    { immediate: true },
);

const selectedSet = computed(() => new Set(props.modelValue));

const totalCount = computed(() => props.groups.reduce((sum, group) => sum + group.attributes.length, 0));

// Ids emit sorted so draft equality against the server's normalised set holds.
const commit = (ids: Set<number>): void => {
    emit('update:modelValue', [...ids].sort((a, b) => a - b));
};

const toggle = (id: number): void => {
    const ids = new Set(selectedSet.value);

    if (ids.has(id)) {
        ids.delete(id);
    } else {
        ids.add(id);
    }

    commit(ids);
};

const setGroup = (group: PickerGroup, selected: boolean): void => {
    const ids = new Set(selectedSet.value);

    group.attributes.forEach((attribute) => {
        if (selected) {
            ids.add(attribute.id);
        } else {
            ids.delete(attribute.id);
        }
    });

    commit(ids);
};

const groupSelectedCount = (group: PickerGroup): number =>
    group.attributes.filter((attribute) => selectedSet.value.has(attribute.id)).length;

const filteredGroups = computed(() => {
    const term = q.value.trim().toLowerCase();

    if (!term) {
        return props.groups;
    }

    return props.groups
        .map((group) => ({
            ...group,
            attributes: group.attributes.filter(
                (attribute) =>
                    attribute.name.toLowerCase().includes(term) || attribute.handle.toLowerCase().includes(term),
            ),
        }))
        .filter((group) => group.attributes.length > 0);
});
</script>

<template>
    <Section :title="title">
        <template v-if="description" #desc>{{ description }}</template>
        <template v-if="groups.length" #actions>
            <span class="text-[11.5px] text-ink-500 whitespace-nowrap">
                {{ t('attributes.picker_selected_count', { selected: modelValue.length, total: totalCount }) }}
            </span>
        </template>

        <div v-if="!groups.length" class="text-center px-5 py-7 border border-dashed border-line-strong rounded-md bg-surface-2">
            <div class="w-9 h-9 mx-auto mb-2 bg-surface border border-line rounded-lg grid place-items-center text-ink-500"><Icon name="boxes" /></div>
            <div class="text-xs text-ink-500 max-w-[320px] mx-auto">{{ t('attributes.picker_empty') }}</div>
        </div>

        <template v-else>
            <div class="mb-2.5 max-w-[280px]">
                <TextInput v-model="q" clearable :placeholder="t('attributes.picker_search')" :aria-label="t('attributes.picker_search')">
                    <template #prefix><Icon name="search" cls="sm" /></template>
                </TextInput>
            </div>

            <div
                v-for="group in filteredGroups"
                :key="group.handle"
                class="border border-line rounded-lg mb-2.5 overflow-hidden"
            >
                <div class="w-full flex items-center gap-2 px-3.5 py-2.5 bg-surface-2">
                    <button
                        type="button"
                        class="flex items-center gap-2 flex-1 min-w-0 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35 rounded-sm"
                        :aria-expanded="openMap[group.handle]"
                        @click="openMap[group.handle] = !openMap[group.handle]"
                    >
                        <Icon name="chevDown" cls="sm" :class="openMap[group.handle] ? '' : '-rotate-90'" class="transition-transform duration-100 text-ink-500" />
                        <span class="text-[12.5px] font-semibold text-ink-900 truncate">{{ group.name }}</span>
                        <span class="text-[11px] text-ink-500 [font-variant-numeric:tabular-nums]">
                            {{ t('attributes.picker_group_count', { selected: groupSelectedCount(group), total: group.attributes.length }) }}
                        </span>
                    </button>
                    <div class="flex items-center gap-1 shrink-0">
                        <button
                            type="button"
                            class="h-[22px] px-1.5 rounded-md text-[11px] font-medium text-ink-700 hover:bg-surface focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                            @click="setGroup(group, true)"
                        >{{ t('attributes.picker_all') }}</button>
                        <button
                            type="button"
                            class="h-[22px] px-1.5 rounded-md text-[11px] font-medium text-ink-700 hover:bg-surface focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
                            @click="setGroup(group, false)"
                        >{{ t('attributes.picker_none') }}</button>
                    </div>
                </div>

                <div v-show="openMap[group.handle]" class="border-t border-line">
                    <label
                        v-for="attribute in group.attributes"
                        :key="attribute.id"
                        class="flex items-center gap-2.5 px-3.5 py-2 border-b border-line last:border-b-0 cursor-pointer hover:bg-surface-2"
                    >
                        <Checkbox
                            :model-value="selectedSet.has(attribute.id)"
                            :aria-label="attribute.name"
                            @update:model-value="toggle(attribute.id)"
                        />
                        <span class="text-[12.5px] font-medium text-ink-900 truncate">{{ attribute.name }}</span>
                        <span class="text-[11px] font-mono text-ink-500 truncate">{{ attribute.handle }}</span>
                        <span class="ml-auto shrink-0 inline-flex items-center gap-1">
                            <span class="h-[18px] px-1.5 inline-flex items-center rounded-full border border-line bg-surface-2 text-[10.5px] font-medium text-ink-700">{{ attribute.type }}</span>
                            <span
                                v-if="attribute.required"
                                class="h-[18px] px-1.5 inline-flex items-center rounded-full border border-warn/40 bg-warn/10 text-[10.5px] font-medium text-warn"
                            >{{ t('attributes.picker_required') }}</span>
                        </span>
                    </label>
                </div>
            </div>

            <a
                v-if="manageUrl"
                :href="manageUrl"
                class="inline-block text-[11.5px] text-ink-500 underline underline-offset-2 hover:text-ink-900 rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35"
            >{{ t('attributes.picker_manage') }} →</a>
        </template>
    </Section>
</template>
