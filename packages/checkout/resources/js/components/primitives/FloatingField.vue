<script setup>
import Icon from './Icon.vue'

defineOptions({ inheritAttrs: false })

defineProps({
  id: { type: String, required: true },
  label: { type: String, required: true },
  modelValue: { type: String, default: '' },
  error: { type: String, default: '' },
  optional: Boolean,
  mono: Boolean,
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div>
    <div class="fl" :class="{ 'has-error': error }">
      <input
        :id="id"
        :class="{ mono }"
        :value="modelValue"
        placeholder=" "
        v-bind="$attrs"
        @input="$emit('update:modelValue', $event.target.value)"
      />
      <label :for="id">{{ label }} <span v-if="optional" class="opt">(optional)</span></label>
    </div>
    <div class="err-msg" :class="{ show: error }" role="alert">
      <span class="ico"><Icon name="alert-circle" :size="14" /></span><span class="t">{{ error }}</span>
    </div>
  </div>
</template>
