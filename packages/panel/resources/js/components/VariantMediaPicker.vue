<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from './Button.vue';
import Icon from './Icon.vue';
import Section from './Section.vue';

export interface VariantMediaItem {
    id: number;
    url: string | null;
    name: string | null;
    alt: string | null;
    selected: boolean;
    position: number | null;
}

const props = defineProps<{
    // The product's media pool with this variant's current selection state.
    pool: VariantMediaItem[];
    syncUrl: string;
}>();

const { t } = useI18n();

// Ordered selection; the first image is the variant's primary.
const seed = (): number[] =>
    props.pool
        .filter((item) => item.selected)
        .sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
        .map((item) => item.id);

const selection = ref<number[]>(seed());

watch(() => props.pool, () => {
    selection.value = seed();
});

const dirty = computed(() => JSON.stringify(selection.value) !== JSON.stringify(seed()));

const orderOf = (id: number): number => selection.value.indexOf(id);

const toggle = (id: number): void => {
    selection.value = orderOf(id) === -1
        ? [...selection.value, id]
        : selection.value.filter((selected) => selected !== id);
};

const move = (id: number, delta: number): void => {
    const index = orderOf(id);
    const target = index + delta;

    if (index === -1 || target < 0 || target >= selection.value.length) {
        return;
    }

    const next = [...selection.value];

    [next[index], next[target]] = [next[target], next[index]];
    selection.value = next;
};

const save = (): void => {
    router.put(props.syncUrl, { ids: selection.value }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Section :title="t('products.section_variant_media')">
        <template #desc>{{ t('products.section_variant_media_description') }}</template>
        <template #actions>
            <Button v-if="dirty" size="sm" variant="primary" @click="save">{{ t('common.save') }}</Button>
        </template>

        <div v-if="!pool.length" class="border border-dashed border-line-strong rounded-lg bg-surface-2 px-4 py-4 text-[11.5px] text-ink-500">
            {{ t('products.variant_media_empty') }}
        </div>

        <div v-else class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2.5">
            <div
                v-for="item in pool"
                :key="item.id"
                class="relative group/tile"
            >
                <button
                    type="button"
                    :class="[
                        'w-full aspect-square rounded-md overflow-hidden border-2 grid place-items-center bg-surface-2 transition-colors duration-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sage/35',
                        orderOf(item.id) !== -1 ? 'border-sage' : 'border-line hover:border-ink-300',
                    ]"
                    :aria-pressed="orderOf(item.id) !== -1"
                    :aria-label="t('products.variant_media_toggle', { name: item.name ?? item.id })"
                    @click="toggle(item.id)"
                >
                    <img v-if="item.url" :src="item.url" :alt="item.alt ?? ''" class="w-full h-full object-cover block">
                    <Icon v-else name="image" class="text-ink-400" />
                </button>

                <span
                    v-if="orderOf(item.id) !== -1"
                    class="absolute top-1 left-1 min-w-[18px] h-[18px] px-1 rounded-full bg-sage text-paper text-[10.5px] font-semibold grid place-items-center [font-variant-numeric:tabular-nums]"
                >{{ orderOf(item.id) === 0 ? t('products.variant_media_primary') : orderOf(item.id) + 1 }}</span>

                <div
                    v-if="orderOf(item.id) !== -1 && selection.length > 1"
                    class="absolute bottom-1 right-1 hidden group-hover/tile:flex gap-0.5"
                >
                    <button
                        type="button"
                        class="w-[20px] h-[20px] rounded bg-paper border border-line grid place-items-center text-ink-500 hover:text-ink-900"
                        :aria-label="t('products.variant_media_earlier')"
                        @click="move(item.id, -1)"
                    ><Icon name="chevronLeft" cls="sm" /></button>
                    <button
                        type="button"
                        class="w-[20px] h-[20px] rounded bg-paper border border-line grid place-items-center text-ink-500 hover:text-ink-900"
                        :aria-label="t('products.variant_media_later')"
                        @click="move(item.id, 1)"
                    ><Icon name="chevronRight" cls="sm" /></button>
                </div>
            </div>
        </div>
    </Section>
</template>
