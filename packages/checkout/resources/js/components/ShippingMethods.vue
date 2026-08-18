<script setup>
import Icon from './primitives/Icon.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state, fmt, deliveryMethods, selectShipping } = useCheckout()
</script>

<template>
  <section class="block" data-block="shipping">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">3</span><span class="chk ico"><Icon name="check" /></span></span>
        Shipping method
      </h2>
    </div>

    <div v-if="!state.addressValid" class="locked">
      <span class="ico"><Icon name="lock" :size="17" /></span> Enter your delivery address to see shipping options.
    </div>

    <div v-else-if="!deliveryMethods.length" class="locked">
      <span class="ico"><Icon name="truck" :size="17" /></span> No delivery options are available for this address.
    </div>

    <div v-else role="radiogroup" aria-label="Select a shipping method">
      <button
        v-for="m in deliveryMethods"
        :key="m.id"
        type="button"
        class="pick"
        role="radio"
        :aria-checked="state.shippingId === m.id"
        @click="selectShipping(m.id)"
      >
        <span class="radio" aria-hidden="true"></span>
        <span class="pbody">
          <span class="ptop">
            <span class="pname">{{ m.name }}</span>
            <span class="pprice">{{ m.price ? fmt(m.price) : 'Free' }}</span>
          </span>
          <span class="pmeta">{{ m.sub }}</span>
        </span>
      </button>
    </div>
  </section>
</template>
