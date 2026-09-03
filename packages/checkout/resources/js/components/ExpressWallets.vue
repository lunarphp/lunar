<script setup>
import { computed } from 'vue'
import { useCheckout } from '../composables/useCheckout.js'
import { resolveElement } from '../composables/elements.js'

// Driver-projected express region (spec 0012 SC): every registered method
// that declares express support renders its own component chunk here. No
// eligible methods = no region, no divider.
const { state } = useCheckout()

const expressMethods = computed(() =>
  (state.paymentMethods || []).filter(
    (m) => m.supportsExpress && m.expressComponent && resolveElement(m.expressComponent),
  ),
)
</script>

<template>
  <div v-if="expressMethods.length">
    <div class="wallets">
      <component :is="resolveElement(m.expressComponent)" v-for="m in expressMethods" :key="m.handle" :method="m" />
    </div>
    <div class="divider"><span>or</span></div>
  </div>
</template>
