<script setup>
import { computed, watch } from 'vue'
import Icon from './primitives/Icon.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state, fmt, deliveryMethods, selectShipping } = useCheckout()

// Nothing chosen once options exist = an unorderable cart the customer can
// still click Pay on (the saved-address auto-apply skips the moment a manual
// save used to force this choice). Pick the cheapest as a real server-side
// selection the customer can change, exactly as if they clicked it.
//
// Cheapest by price, not first in the list: the host decides the order, and
// the zone/rate order a table-rate setup produces routinely puts a paid rate
// ahead of the free-over-threshold one it qualifies for. Taking the first
// there charges for delivery the basket had earned.
const cheapestDelivery = computed(() =>
  deliveryMethods.value.reduce((best, m) => (best === null || m.price < best.price ? m : best), null),
)

watch(
  () => [state.addressValid, state.shippingId, cheapestDelivery.value?.id],
  () => {
    if (state.fulfilment === 'delivery' && state.addressValid && !state.shippingId && cheapestDelivery.value) {
      selectShipping(cheapestDelivery.value.id)
    }
  },
  { immediate: true },
)
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

    <!-- The host's reason when it has one (spec 0011 §H); otherwise the plain fact. -->
    <div v-else-if="!deliveryMethods.length" class="locked">
      <span class="ico"><Icon name="truck" :size="17" /></span>
      {{ state.deliveryNotice || 'No delivery options are available for this address.' }}
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
