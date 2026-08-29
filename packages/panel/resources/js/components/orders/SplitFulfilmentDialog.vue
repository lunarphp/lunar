<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Button from '../Button.vue';
import Dialog from '../Dialog.vue';
import TextInput from '../TextInput.vue';
import type { FulfilmentData } from './types';

const props = defineProps<{ open: boolean; fulfilment: FulfilmentData | null }>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const { t } = useI18n();

const form = useForm<{ moves: Record<number, number> }>({ moves: {} });

watch(
    () => props.open,
    (open) => {
        if (open && props.fulfilment) {
            form.clearErrors();
            form.moves = Object.fromEntries(
                props.fulfilment.lines.map((line) => [line.order_line_id ?? line.id, 0]),
            );
        }
    },
    { immediate: true },
);

const clamp = (lineId: number, max: number): void => {
    const value = Math.max(0, Math.min(max, Math.floor(Number(form.moves[lineId]) || 0)));
    form.moves[lineId] = value;
};

const moved = computed(() => Object.values(form.moves).reduce((sum, quantity) => sum + (Number(quantity) || 0), 0));
const total = computed(() => (props.fulfilment?.lines ?? []).reduce((sum, line) => sum + line.quantity, 0));
const valid = computed(() => moved.value >= 1 && moved.value < total.value);

const close = (): void => emit('update:open', false);

const submit = (): void => {
    if (props.fulfilment && valid.value) {
        form.post(props.fulfilment.urls.split, { preserveScroll: true, onSuccess: close });
    }
};
</script>

<template>
    <Dialog :open="open" :title="t('orders.split_title')" :description="t('orders.split_help')" size="sm" @update:open="(v: boolean) => !v && close()">
        <div v-if="fulfilment" class="space-y-2">
            <div v-for="line in fulfilment.lines" :key="line.id" class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <div class="text-[12.5px] text-ink-900 truncate">{{ line.description }}</div>
                    <div v-if="line.identifier" class="text-[11px] text-ink-500 font-mono">{{ line.identifier }}</div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <TextInput
                        v-model.number="form.moves[line.order_line_id ?? line.id]"
                        type="number"
                        :min="0"
                        :max="line.quantity"
                        class="!w-[70px] text-right"
                        :aria-label="line.description ?? undefined"
                        @change="clamp(line.order_line_id ?? line.id, line.quantity)"
                    />
                    <span class="text-[11.5px] text-ink-500 [font-variant-numeric:tabular-nums]">/ {{ line.quantity }}</span>
                </div>
            </div>
            <p class="m-0 pt-1 text-[11.5px] text-ink-500">{{ t('orders.split_moving', { moved, total }) }}</p>
            <p v-if="form.errors.moves" class="m-0 text-danger text-[11px]">{{ form.errors.moves }}</p>
        </div>
        <template #footer>
            <Button variant="ghost" @click="close">{{ t('common.cancel') }}</Button>
            <Button variant="primary" :disabled="!valid || form.processing" @click="submit">{{ t('orders.split_confirm') }}</Button>
        </template>
    </Dialog>
</template>
