<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import FieldLabel from '../FieldLabel.vue';
import TextInput from '../TextInput.vue';
import Toggle from '../Toggle.vue';

const { t } = useI18n();

const props = defineProps<{
    modelValue: Record<string, unknown>;
    currencies: { id: number; code: string; decimal_places: number; default: boolean }[];
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [Record<string, unknown>] }>();

const field = <T,>(key: string) => computed<T>({
    get: () => props.modelValue[key] as T,
    set: (value) => emit('update:modelValue', { ...props.modelValue, [key]: value }),
});

const minQty = field<number | string>('min_qty');
const rewardQty = field<number | string>('reward_qty');
const maxRewardQty = field<number | string>('max_reward_qty');
const automaticallyAddRewards = field<boolean>('automatically_add_rewards');
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <FieldLabel for="discount-min-qty" required>{{ t('discounts.field_min_qty') }}</FieldLabel>
            <TextInput id="discount-min-qty" v-model="minQty" type="number" min="1" :invalid="!!errors?.['data.min_qty']" />
            <div v-if="errors?.['data.min_qty']" class="mt-1 text-[11px] text-danger">{{ errors['data.min_qty'] }}</div>
        </div>
        <div>
            <FieldLabel for="discount-reward-qty" required>{{ t('discounts.field_reward_qty') }}</FieldLabel>
            <TextInput id="discount-reward-qty" v-model="rewardQty" type="number" min="1" :invalid="!!errors?.['data.reward_qty']" />
            <div v-if="errors?.['data.reward_qty']" class="mt-1 text-[11px] text-danger">{{ errors['data.reward_qty'] }}</div>
        </div>
        <div>
            <FieldLabel for="discount-max-reward-qty">{{ t('discounts.field_max_reward_qty') }}</FieldLabel>
            <TextInput id="discount-max-reward-qty" v-model="maxRewardQty" type="number" min="1" :invalid="!!errors?.['data.max_reward_qty']" />
            <div v-if="errors?.['data.max_reward_qty']" class="mt-1 text-[11px] text-danger">{{ errors['data.max_reward_qty'] }}</div>
            <div class="mt-1 text-[11.5px] text-ink-500">{{ t('discounts.field_max_reward_qty_hint') }}</div>
        </div>
        <label class="sm:col-span-3 flex items-start gap-3 cursor-pointer">
            <Toggle :on="!!automaticallyAddRewards" @toggle="automaticallyAddRewards = !automaticallyAddRewards" />
            <div>
                <div class="text-[12.5px] text-ink-900 font-medium">{{ t('discounts.field_automatically_add_rewards') }}</div>
                <div class="text-[11px] text-ink-500">{{ t('discounts.field_automatically_add_rewards_hint') }}</div>
            </div>
        </label>
    </div>
</template>
