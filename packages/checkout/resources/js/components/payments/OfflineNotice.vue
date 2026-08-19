<script setup>
import Icon from '../primitives/Icon.vue'
import { useCheckout } from '../../composables/useCheckout.js'

/**
 * Panel for a payment method taken outside the checkout — at a counter on
 * collection, on account, against an invoice. There is nothing to capture
 * here: the order is placed unpaid and settled later.
 */
defineProps({
  method: { type: Object, required: true },
})

const { totalLabel } = useCheckout()
</script>

<template>
  <div class="pm-panel">
    <div class="pm-redirect">
      <span class="ico"><Icon name="info" :size="20" /></span>
      <p class="rt">
        No payment is taken now. <strong>{{ method.label }}</strong> —
        <strong class="mono">{{ totalLabel }}</strong> is settled when you collect or on your
        agreed terms.
      </p>
    </div>
  </div>
</template>
