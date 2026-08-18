<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps<{ image?: string; imageAlt?: string }>();

const imageFailed = ref(false);

const panelName = computed(() => (usePage().props.panel as { name: string }).name);
</script>

<template>
    <div class="min-h-screen bg-canvas font-sans lg:grid lg:grid-cols-[minmax(420px,1fr)_1.2fr]">
        <div class="flex flex-col px-6 py-8 sm:px-12 lg:px-16">
            <div class="flex items-center gap-2 text-[15px] font-semibold tracking-[-0.015em] text-ink-900">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-ink-900 text-paper text-[11px] font-semibold">L</span>
                <span>{{ panelName }}</span>
            </div>
            <div class="flex-1 flex items-center py-10">
                <div class="w-full max-w-[400px] mx-auto">
                    <slot />
                </div>
            </div>
            <div class="text-[11px] text-ink-400">&copy; LunarPHP Ltd</div>
        </div>

        <div class="hidden lg:block relative overflow-hidden bg-gradient-to-br from-sage-soft via-canvas to-paper">
            <img
                v-if="image && !imageFailed"
                :src="image"
                :alt="imageAlt ?? ''"
                class="absolute inset-0 w-full h-full object-cover"
                @error="imageFailed = true"
            />
            <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-black/60 via-black/15 to-transparent" />
            <div v-if="$slots.caption" class="absolute bottom-8 left-8 right-8 text-[12px] text-white/85 leading-relaxed max-w-sm">
                <slot name="caption" />
            </div>
        </div>
    </div>
</template>
