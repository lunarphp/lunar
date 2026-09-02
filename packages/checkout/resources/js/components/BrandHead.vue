<script setup>
import Icon from './primitives/Icon.vue'

defineProps({
  merchant: { type: String, default: 'Store' },
  // Brand logo from CheckoutTheme::branding(). When set it replaces the default
  // icon-mark + wordmark; otherwise the shipped mark renders.
  logo: { type: String, default: '' },
  logoAlt: { type: String, default: '' },
  // Where the brand links back to (the store / basket). '#' renders a dead
  // link, so hosts should always project one; the checkout passes urls.back.
  backUrl: { type: String, default: '/' },
})
</script>

<template>
  <div class="brand-head">
    <span v-if="!logo" class="brand-mark"><span class="ico" style="font-size: 20px"><Icon name="mountain" :size="20" /></span></span>
    <div class="brand-text">
      <a v-if="logo" :href="backUrl" :aria-label="`${merchant} — back to store`"><img class="brand-logo" :src="logo" :alt="logoAlt || `${merchant} logo`" /></a>
      <a v-else :href="backUrl" class="brand-name" :aria-label="`${merchant} — back to store`">{{ merchant }}<span class="dot">.</span></a>
      <div class="secured"><span class="ico"><Icon name="lock" :size="14" /></span> Secure checkout</div>
    </div>
  </div>
</template>
