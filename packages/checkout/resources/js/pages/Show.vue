<script setup>
import LunarCheckout from '../components/LunarCheckout.vue'

// Inertia page props from CheckoutController::show().
defineProps({
  checkout: { type: Object, required: true },
  theme: { type: Object, default: () => ({}) },
  branding: { type: Object, default: () => ({}) },
})
</script>

<template>
  <LunarCheckout :checkout="checkout" :theme="theme" :branding="branding" />
</template>
