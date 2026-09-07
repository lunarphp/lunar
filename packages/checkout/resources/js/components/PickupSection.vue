<script setup>
import Icon from './primitives/Icon.vue'
import DeliverySection from './DeliverySection.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state, collectOption, collectAvailable, pickupPointRequired, selectPickupPoint } = useCheckout()
</script>

<template>
  <template v-if="collectAvailable">
    <DeliverySection variant="collect" />

    <section class="block" data-block="pickup">
      <div class="block-head">
        <h2 class="block-title">
          <span class="block-step"><span class="num">3</span><span class="chk ico"><Icon name="check" /></span></span>
          Collect from
        </h2>
      </div>

      <!-- No host points: the order simply collects (spec 0013 §A). -->
      <div v-if="!state.pickupPoints.length" class="locked">
        <span class="ico"><Icon name="store" :size="17" /></span>
        <span>
          <strong>{{ collectOption.name }}</strong>
          <template v-if="collectOption.sub"> · {{ collectOption.sub }}</template>
        </span>
      </div>

      <!-- One point: chosen for the customer server-side, shown read-only. -->
      <div v-else-if="state.pickupPoints.length === 1" class="locked">
        <span class="ico"><Icon name="store" :size="17" /></span>
        <span>
          <strong>{{ state.pickupPoints[0].name }}</strong>
          <span v-for="line in state.pickupPoints[0].lines" :key="line" class="pmeta"> · {{ line }}</span>
        </span>
      </div>

      <div v-else role="radiogroup" aria-label="Choose where to collect your order">
        <button
          v-for="point in state.pickupPoints"
          :key="point.id"
          type="button"
          class="pick"
          role="radio"
          :aria-checked="state.pickupPointId === point.id"
          @click="selectPickupPoint(point.id)"
        >
          <span class="radio" aria-hidden="true"></span>
          <span class="pbody">
            <span class="ptop">
              <span class="pname">{{ point.name }}</span>
              <span class="pprice">Free</span>
            </span>
            <span class="pmeta">{{ point.lines.join(' · ') }}</span>
          </span>
        </button>
        <p v-if="pickupPointRequired" class="help" role="alert">Choose a branch to continue.</p>
      </div>
    </section>
  </template>

  <section v-else class="block">
    <div class="locked">
      <span class="ico"><Icon name="store" :size="17" /></span>
      Click &amp; collect isn't available for this order.
    </div>
  </section>
</template>
