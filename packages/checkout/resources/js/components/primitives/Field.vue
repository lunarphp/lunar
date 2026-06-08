<script setup>
import Icon from './Icon.vue'

defineProps({
  label: String,
  hint: String,
  error: String,
})
</script>

<template>
  <div class="field">
    <label v-if="label">{{ label }}</label>
    <slot />
    <span v-if="error" class="err-msg"><Icon name="alert-circle" :size="14" /> {{ error }}</span>
    <span v-else-if="hint" class="hint">{{ hint }}</span>
  </div>
</template>
