<script setup>
import { computed, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import DeliverySection from './DeliverySection.vue'
import { useCheckout } from '../composables/useCheckout.js'
import { distanceBetween, formatDistance } from '../utils/distance.js'

const { state, collectOption, collectUnavailable, pickupPointRequired, selectPickupPoint } = useCheckout()

// --- Distance (spec 0013 §A) -------------------------------------------------
//
// Points that know where they are get a distance from the customer origin:
// the host's, geocoded from the delivery postcode, or the browser's on
// request. Straight line is enough to choose between a few branches.

const locatable = computed(() => state.pickupPoints.some((point) => point.location))
const canLocate = computed(
  () => locatable.value && !state.pickupOrigin && typeof navigator !== 'undefined' && 'geolocation' in navigator,
)
const locating = ref(false)
const locateError = ref('')

// Miles where the delivery country counts in miles, kilometres elsewhere,
// unless the host has said which.
const unit = computed(
  () => state.distanceUnit ?? (['GB', 'US'].includes(state.shippingAddress?.countryCode) ? 'mi' : 'km'),
)

// Nearest first once an origin is known; the host's order until then.
const points = computed(() => {
  const origin = state.pickupOrigin
  const withDistance = state.pickupPoints.map((point) => ({
    ...point,
    distance: origin && point.location ? distanceBetween(origin, point.location) : null,
  }))

  if (!origin) return withDistance

  return withDistance.sort((a, b) => (a.distance ?? Infinity) - (b.distance ?? Infinity))
})

// Only on the customer's click: no permission prompt on load, and a refusal
// leaves the list exactly as it was.
function useMyLocation() {
  if (locating.value || !canLocate.value) return

  locating.value = true
  locateError.value = ''

  navigator.geolocation.getCurrentPosition(
    (position) => {
      state.pickupOrigin = { latitude: position.coords.latitude, longitude: position.coords.longitude }
      locating.value = false
    },
    () => {
      locateError.value = 'We could not get your location.'
      locating.value = false
    },
    { maximumAge: 300000, timeout: 10000 },
  )
}
</script>

<template>
  <!-- The address block always renders: Lunar needs a shipping address row
       before it can offer the collect option at all (spec 0013 §F.1). -->
  <DeliverySection variant="collect" />

  <section class="block" data-block="pickup">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">3</span><span class="chk ico"><Icon name="check" /></span></span>
        Collect from
      </h2>
    </div>

    <!-- Address saved, still no collect option: out of zone, switch to delivery. -->
    <div v-if="collectUnavailable" class="locked">
      <span class="ico"><Icon name="store" :size="17" /></span>
      Click &amp; collect isn't available for this address.
    </div>

    <!-- No host points: the order simply collects (spec 0013 §A). -->
    <div v-else-if="!state.pickupPoints.length" class="locked">
      <span class="ico"><Icon name="store" :size="17" /></span>
      <span>
        <strong>{{ collectOption?.name ?? 'Click & collect' }}</strong>
        <template v-if="collectOption?.sub"> · {{ collectOption.sub }}</template>
      </span>
    </div>

    <!-- One point: chosen for the customer server-side, shown read-only. -->
    <div v-else-if="state.pickupPoints.length === 1" class="locked">
      <span class="ico"><Icon name="store" :size="17" /></span>
      <span>
        <strong>{{ state.pickupPoints[0].name }}</strong>
        <span v-for="(line, i) in state.pickupPoints[0].lines" :key="i" class="pmeta"> · {{ line }}</span>
      </span>
    </div>

    <div v-else role="radiogroup" aria-label="Choose where to collect your order">
      <button
        v-for="point in points"
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
            <span v-if="point.distance !== null" class="pprice">{{ formatDistance(point.distance, unit) }}</span>
          </span>
          <span class="pmeta">{{ point.lines.join(' · ') }}</span>
        </span>
      </button>
      <p v-if="pickupPointRequired" class="help" role="alert">Choose a branch to continue.</p>

      <button v-if="canLocate" type="button" class="locate" :disabled="locating" @click="useMyLocation">
        <Icon name="locate" :size="15" />
        {{ locating ? 'Finding your location…' : 'Show distances from my location' }}
      </button>
      <p v-if="locateError" class="help" role="alert">{{ locateError }}</p>
    </div>
  </section>
</template>
