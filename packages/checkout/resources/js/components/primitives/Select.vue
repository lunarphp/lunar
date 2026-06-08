<script setup>
import Icon from './Icon.vue'

defineOptions({ inheritAttrs: false })

defineProps({
  modelValue: { type: [String, Number], default: '' },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <span class="select-wrap">
    <select
      :value="modelValue"
      v-bind="$attrs"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <slot />
    </select>
    <span class="chev"><Icon name="chevron-down" :size="17" /></span>
  </span>
</template>
