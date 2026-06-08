<script setup>
import Icon from './Icon.vue'

defineOptions({ inheritAttrs: false })

defineProps({
  leadIcon: String,
  mono: Boolean,
  error: Boolean,
  modelValue: { type: String, default: '' },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <span v-if="leadIcon || $slots.trail" class="input-wrap">
    <span v-if="leadIcon" class="lead-ico"><Icon :name="leadIcon" :size="17" /></span>
    <input
      :class="['input', leadIcon && 'has-lead', mono && 'mono', error && 'is-error']"
      :value="modelValue"
      v-bind="$attrs"
      @input="$emit('update:modelValue', $event.target.value)"
    />
    <span v-if="$slots.trail" class="trail"><slot name="trail" /></span>
  </span>
  <input
    v-else
    :class="['input', mono && 'mono', error && 'is-error']"
    :value="modelValue"
    v-bind="$attrs"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
