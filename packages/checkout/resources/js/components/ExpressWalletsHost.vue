<script setup>
import { resolveElement } from '../composables/elements.js'

/**
 * Host-mode express wallet region (spec 0012 SF): mounted standalone on host
 * pages (the cart, later a mini-cart) outside the checkout bundle, via
 * `mountExpress()` in express.js. Unlike ExpressWallets.vue it carries no
 * useCheckout state: the server-projected methods, the checkout start url
 * and the cart total arrive as plain props, and each wallet component mints
 * its own checkout session lazily, on first interaction (see StripeExpress's
 * `ensureSession()`), rather than one already existing.
 */
defineProps({
  methods: { type: Array, required: true },
  startUrl: { type: String, required: true },
  amount: { type: Number, required: true },
  currency: { type: String, default: 'GBP' },
})
</script>

<template>
  <div v-if="methods.length" class="wallets">
    <component
      :is="resolveElement(m.expressComponent)"
      v-for="m in methods"
      :key="m.handle"
      :method="m"
      :start-url="startUrl"
      :amount="amount"
      :currency="currency"
    />
  </div>
</template>
