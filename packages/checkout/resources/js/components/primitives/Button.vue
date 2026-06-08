<script setup>
defineProps({
  variant: { type: String, default: 'primary' },
  block: Boolean,
  loading: Boolean,
})
</script>

<template>
  <button :class="['btn', 'btn-' + variant, block && 'btn-block']">
    <span v-if="loading" class="spinner" />
    <slot v-else />
  </button>
</template>
