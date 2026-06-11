<script setup>
import Icon from './primitives/Icon.vue'

defineProps({
  merchant: { type: String, default: 'Store' },
  // Brand logo from CheckoutTheme::branding(). When set it replaces the default
  // icon-mark + wordmark; otherwise the shipped mark renders.
  logo: { type: String, default: '' },
  logoAlt: { type: String, default: '' },
})
</script>

<template>
  <div class="brand-head">
    <span v-if="!logo" class="brand-mark"><span class="ico" style="font-size: 20px"><Icon name="mountain" :size="20" /></span></span>
    <div class="brand-text">
      <img v-if="logo" class="brand-logo" :src="logo" :alt="logoAlt || `${merchant} logo`" />
      <a v-else href="#" class="brand-name" :aria-label="`${merchant} — home`">{{ merchant }}<span class="dot">.</span></a>
      <div class="secured"><span class="ico"><Icon name="lock" :size="14" /></span> Secure checkout</div>
    </div>
  </div>
</template>
