<script setup>
import Icon from './primitives/Icon.vue'

defineProps({
  merchant: { type: String, default: 'Store' },
})
</script>

<template>
  <div class="brand-head">
    <span class="brand-mark"><span class="ico" style="font-size: 20px"><Icon name="mountain" :size="20" /></span></span>
    <div class="brand-text">
      <a href="#" class="brand-name" :aria-label="`${merchant} — home`">{{ merchant }}<span class="dot">.</span></a>
      <div class="secured"><span class="ico"><Icon name="lock" :size="14" /></span> Secure checkout</div>
    </div>
  </div>
</template>
