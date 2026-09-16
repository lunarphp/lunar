<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// A sidebar column that scrolls with the page and pins when it can. Shorter
// than the viewport, it sticks below the breadcrumb bar like before. Taller,
// it scrolls with the page straight away and sticks when its own end reaches
// the bottom of the viewport, so cards added by add-ons are reachable without
// scrolling the main column to its end. A negative sticky `top` is what makes
// the second case work; the value tracks the sidebar's own height.
const props = withDefaults(defineProps<{ offset?: number; gap?: number }>(), { offset: 60, gap: 16 });

const element = ref<HTMLElement | null>(null);
const height = ref(0);
const viewport = ref(0);
let observer: ResizeObserver | undefined;

const measure = (): void => {
    height.value = element.value?.offsetHeight ?? 0;
    viewport.value = window.innerHeight;
};

onMounted(() => {
    measure();
    window.addEventListener('resize', measure);

    if (typeof ResizeObserver !== 'undefined' && element.value) {
        observer = new ResizeObserver(measure);
        observer.observe(element.value);
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', measure);
    observer?.disconnect();
});

const top = computed(() => {
    const fits = height.value + props.offset + props.gap <= viewport.value;

    return `${fits ? props.offset : viewport.value - height.value - props.gap}px`;
});
</script>

<template>
    <div ref="element" class="lg:sticky" :style="{ top }" data-testid="sticky-sidebar">
        <slot />
    </div>
</template>
