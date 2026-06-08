<script setup>
defineProps({
  // Highlight one network ('visa' | 'mc' | 'amex'); dim the rest.
  active: String,
})
</script>

<template>
  <span class="netmarks">
    <span :class="['nmk', 'visa', active && active !== 'visa' && 'dim']">VISA</span>
    <span :class="['nmk', 'mc', active && active !== 'mc' && 'dim']">
      <span class="c1" /><span class="c2" />
    </span>
    <span :class="['nmk', 'amex', active && active !== 'amex' && 'dim']">AMEX</span>
  </span>
</template>
