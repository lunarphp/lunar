<script setup>
import Icon from '../primitives/Icon.vue'

/**
 * Panel for an order charged to the customer's trade account (spec 0014 §E).
 * Nothing is captured here and no account details are shown: the customer
 * knows their own account, and the number belongs on the invoice.
 */
defineProps({
  method: { type: Object, required: true },
})
</script>

<template>
  <div class="pm-panel">
    <div class="pm-redirect">
      <span class="ico"><Icon name="store" :size="20" /></span>
      <p class="rt">
        This order will be charged to your trade account. You will be invoiced on your usual
        terms.
      </p>
    </div>
  </div>
</template>
