<script setup lang="ts">
import { ref } from 'vue';

export interface FocalPoint {
    x: number;
    y: number;
}

const props = defineProps<{
    modelValue: FocalPoint;
    src: string;
    alt?: string | null;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: FocalPoint] }>();

const surface = ref<HTMLElement | null>(null);
const dragging = ref(false);

// Focal coordinates are integer percentages (0-100), matching how they are
// stored on the media custom properties and consumed by focalCrop.
const setFromEvent = (event: PointerEvent): void => {
    if (!surface.value) {
        return;
    }

    const rect = surface.value.getBoundingClientRect();
    const x = Math.round(Math.min(100, Math.max(0, ((event.clientX - rect.left) / rect.width) * 100)));
    const y = Math.round(Math.min(100, Math.max(0, ((event.clientY - rect.top) / rect.height) * 100)));

    emit('update:modelValue', { x, y });
};

const onPointerDown = (event: PointerEvent): void => {
    dragging.value = true;
    (event.target as HTMLElement).setPointerCapture?.(event.pointerId);
    setFromEvent(event);
};

const onPointerMove = (event: PointerEvent): void => {
    if (dragging.value) {
        setFromEvent(event);
    }
};

const onPointerUp = (): void => {
    dragging.value = false;
};
</script>

<template>
    <div
        ref="surface"
        class="relative rounded-lg overflow-hidden border border-line bg-surface-2 cursor-crosshair select-none touch-none"
        role="slider"
        :aria-valuetext="`${modelValue.x}% ${modelValue.y}%`"
        tabindex="0"
        @pointerdown="onPointerDown"
        @pointermove="onPointerMove"
        @pointerup="onPointerUp"
        @pointercancel="onPointerUp"
    >
        <img :src="src" :alt="alt ?? ''" class="w-full h-auto block pointer-events-none" draggable="false" />
        <div
            class="absolute w-6 h-6 -ml-3 -mt-3 rounded-full border-2 border-white shadow-[0_0_0_1.5px_rgba(0,0,0,0.55)] pointer-events-none"
            :style="{ left: `${modelValue.x}%`, top: `${modelValue.y}%` }"
        >
            <div class="absolute inset-[7px] rounded-full bg-white" />
        </div>
    </div>
</template>
