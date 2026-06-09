<script setup>
import Icon from './primitives/Icon.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state } = useCheckout()
</script>

<template>
  <div class="seg" :data-active="state.fulfilment" role="radiogroup" aria-label="How would you like your order?">
    <span class="seg-thumb" aria-hidden="true"></span>
    <button
      type="button"
      class="seg-btn"
      role="radio"
      :aria-checked="state.fulfilment === 'delivery'"
      @click="state.fulfilment = 'delivery'"
    >
      <span class="ico"><Icon name="truck" :size="17" /></span> Deliver
    </button>
    <button
      type="button"
      class="seg-btn"
      role="radio"
      :aria-checked="state.fulfilment === 'collect'"
      @click="state.fulfilment = 'collect'"
    >
      <span class="ico"><Icon name="store" :size="17" /></span> Click &amp; collect
    </button>
  </div>
</template>
