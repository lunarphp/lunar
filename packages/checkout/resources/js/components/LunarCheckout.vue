<script setup>
import { computed, ref } from 'vue'
import OrderRail from './OrderRail.vue'
import CheckoutForm from './CheckoutForm.vue'
import SuccessScreen from './SuccessScreen.vue'
import { useCheckoutTheme } from '../composables/useCheckoutTheme.js'
import { money } from '../utils/money.js'

const props = defineProps({
  // CheckoutData payload (placeholder shape until spec 0001 lands).
  checkout: { type: Object, required: true },
  // Validated CheckoutTheme token map ({ '--accent': '#…' }).
  theme: { type: Object, default: () => ({}) },
})

// Theme is applied as inline custom properties on this scoped root.
const root = ref(null)
useCheckoutTheme(root, () => props.theme)

const processing = ref(false)
const paid = ref(false)
const promoApplied = ref(false)

const currency = computed(() => props.checkout.currency || 'USD')
const subtotal = computed(() =>
  props.checkout.items.reduce((sum, i) => sum + i.price * i.qty, 0),
)
const discount = computed(() => props.checkout.discount?.amount ?? 0)
const total = computed(
  () =>
    subtotal.value +
    (props.checkout.shipping || 0) +
    (props.checkout.tax || 0) -
    (promoApplied.value ? discount.value : 0),
)
const totalLabel = computed(() => money(total.value, currency.value))

// Cosmetic only — real placement gates on the checkout session (spec 0004).
function pay() {
  processing.value = true
  setTimeout(() => {
    processing.value = false
    paid.value = true
  }, 1400)
}
</script>

<template>
  <div ref="root" class="lunar-checkout">
    <SuccessScreen v-if="paid" :order="checkout" :total-label="totalLabel" @done="paid = false" />
    <div v-else class="co-page">
      <OrderRail :order="checkout" :promo-applied="promoApplied" @apply-promo="promoApplied = true" />
      <CheckoutForm :total-label="totalLabel" :processing="processing" @pay="pay" />
    </div>
  </div>
</template>
