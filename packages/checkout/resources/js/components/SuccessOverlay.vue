<script setup>
import Icon from './primitives/Icon.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state } = useCheckout()
</script>

<template>
  <div v-if="state.paid" class="success-overlay">
    <div class="success-card">
      <div class="success-check ico"><Icon name="check" :size="32" /></div>
      <h2>Payment confirmed</h2>
      <p>Thanks — a receipt is on its way to your inbox.</p>
      <button type="button" class="btn btn-secondary btn-block" @click="state.paid = false">Done</button>
    </div>
  </div>
</template>
