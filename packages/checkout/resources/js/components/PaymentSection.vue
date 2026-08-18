<script setup>
import Icon from './primitives/Icon.vue'
import { useCheckout } from '../composables/useCheckout.js'
import { resolveElement } from '../composables/elements.js'

const { state, activePaymentMethod } = useCheckout()

// The active method's component, resolved from the same runtime registry the
// elements use (spec 0002 §C) — a gateway chunk self-registers its component.
const panelFor = (method) => resolveElement(method.component)
</script>

<template>
  <section class="block" data-block="payment">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">4</span><span class="chk ico"><Icon name="check" /></span></span>
        Payment
      </h2>
    </div>

    <div v-if="!state.paymentMethods.length" class="locked">
      <span class="ico"><Icon name="credit-card" :size="17" /></span>
      No payment methods are available.
    </div>

    <template v-else>
      <div class="secure-note">
        <span class="txt"><span class="ico"><Icon name="lock" :size="15" /></span> All transactions are secure and encrypted</span>
      </div>

      <div v-if="state.paymentMethods.length > 1" class="pm-tabs" role="radiogroup" aria-label="Payment method">
        <button
          v-for="m in state.paymentMethods"
          :key="m.handle"
          type="button"
          class="pm-tab"
          :class="{ on: state.method === m.handle }"
          role="radio"
          :aria-checked="state.method === m.handle"
          @click="state.method = m.handle"
        >
          <span class="lbl">{{ m.label }}</span>
        </button>
      </div>

      <component
        :is="panelFor(activePaymentMethod)"
        v-if="activePaymentMethod && panelFor(activePaymentMethod)"
        :key="activePaymentMethod.handle"
        :method="activePaymentMethod"
      />

      <label class="check" style="margin-top: 14px">
        <input v-model="state.billingSame" type="checkbox" />
        <span class="box ico"><Icon name="check" /></span>
        <span class="txt">Use delivery address as billing address</span>
      </label>

      <p v-if="state.payError" class="help" style="color: var(--error-700)">{{ state.payError }}</p>
    </template>
  </section>
</template>
