<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        length?: number;
        invalid?: boolean;
        autoFocus?: boolean;
        disabled?: boolean;
    }>(),
    { modelValue: '', length: 6 },
);

const emit = defineEmits<{ 'update:modelValue': [value: string]; complete: [value: string] }>();

const cells = computed(() => {
    const v = props.modelValue || '';

    return Array.from({ length: props.length }, (_, i) => v[i] || '');
});

const inputs = ref<HTMLInputElement[]>([]);
const shaking = ref(false);

const focusCell = (i: number) => {
    const el = inputs.value[i];

    if (el) {
        el.focus();
        el.select();
    }
};

const commit = (next: string, focusIndex?: number) => {
    emit('update:modelValue', next);

    if (focusIndex !== undefined) {
        nextTick(() => focusCell(focusIndex));
    }

    if (next.length === props.length) {
        emit('complete', next);
    }
};

const distribute = (from: number, digits: string) => {
    const arr = cells.value.slice();
    let idx = from;

    for (const d of digits) {
        if (idx >= props.length) break;
        arr[idx] = d;
        idx++;
    }

    commit(arr.join('').slice(0, props.length), Math.min(idx, props.length - 1));
};

const writeAt = (index: number, char: string) => {
    const arr = cells.value.slice();
    arr[index] = char;
    commit(arr.join('').slice(0, props.length));
};

const onInput = (i: number, e: Event) => {
    const digits = (e.target as HTMLInputElement).value.replace(/\D/g, '');

    if (!digits) {
        writeAt(i, '');
        return;
    }

    if (digits.length === 1) {
        writeAt(i, digits);

        if (i < props.length - 1) {
            nextTick(() => focusCell(i + 1));
        }

        return;
    }

    distribute(i, digits);
};

const onKeydown = (i: number, e: KeyboardEvent) => {
    if (e.key === 'Backspace') {
        if (cells.value[i]) {
            writeAt(i, '');
            e.preventDefault();
        } else if (i > 0) {
            writeAt(i - 1, '');
            nextTick(() => focusCell(i - 1));
            e.preventDefault();
        }

        return;
    }

    if (e.key === 'ArrowLeft' && i > 0) {
        e.preventDefault();
        focusCell(i - 1);
        return;
    }

    if (e.key === 'ArrowRight' && i < props.length - 1) {
        e.preventDefault();
        focusCell(i + 1);
        return;
    }

    if (e.key === 'Home') {
        e.preventDefault();
        focusCell(0);
        return;
    }

    if (e.key === 'End') {
        e.preventDefault();
        focusCell(props.length - 1);
    }
};

const onPaste = (i: number, e: ClipboardEvent) => {
    const digits = (e.clipboardData?.getData('text') || '').replace(/\D/g, '');

    if (!digits) return;

    e.preventDefault();
    distribute(i, digits);
};

const focus = () => focusCell(0);
const clear = () => {
    emit('update:modelValue', '');
    nextTick(() => focusCell(0));
};
defineExpose({ focus, clear });

onMounted(() => {
    if (props.autoFocus) {
        nextTick(() => focusCell(0));
    }
});

watch(
    () => props.invalid,
    (v) => {
        if (!v) return;
        shaking.value = true;
        setTimeout(() => (shaking.value = false), 400);
    },
);

const cellCls = computed(() => [
    'w-12 h-14 text-center text-xl font-mono text-ink-900 bg-surface border rounded-md outline-none transition-[border-color,box-shadow,background-color] duration-100 focus:ring-3',
    props.invalid
        ? 'border-danger focus:border-danger focus:ring-danger/25'
        : 'border-line-strong hover:border-ink-300 focus:border-sage focus:ring-sage/35',
    props.disabled ? 'opacity-60 cursor-not-allowed' : '',
]);
</script>

<template>
    <div :class="['flex gap-2', shaking ? 'animate-shake' : '']">
        <input
            v-for="(c, i) in cells"
            :key="i"
            ref="inputs"
            :value="c"
            :class="cellCls"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            maxlength="1"
            :aria-label="`Digit ${i + 1} of ${length}`"
            :disabled="disabled"
            @input="onInput(i, $event)"
            @keydown="onKeydown(i, $event)"
            @paste="onPaste(i, $event)"
            @focus="(e) => (e.target as HTMLInputElement).select()"
        />
    </div>
</template>
