<script setup>
import Icon from './primitives/Icon.vue'
import BillingSection from './BillingSection.vue'
import { useCheckout } from '../composables/useCheckout.js'
import { resolveElement } from '../composables/elements.js'

// Step number, derived by the parent from how many main-region elements are
// registered ahead of payment (spec 0011 §G) — payment is always last, but
// "last" isn't a fixed number when the elements ahead of it are opt-in.
defineProps({
  step: { type: Number, default: 5 },
})

const { state, activePaymentMethod } = useCheckout()

// The active method's component, resolved from the same runtime registry the
// elements use (spec 0002 §C) — a gateway chunk self-registers its component.
const panelFor = (method) => resolveElement(method.component)
</script>

<template>
  <section class="block" data-block="payment">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">{{ step }}</span><span class="chk ico"><Icon name="check" /></span></span>
        Payment
      </h2>
    </div>

    <div v-if="!state.paymentMethods.length" class="locked" role="status">
      <span class="ico"><Icon name="credit-card" :size="17" /></span>
      <span>
        <template v-if="state.paymentUnavailable.length">
          <span v-for="reason in state.paymentUnavailable" :key="reason" class="reason">{{ reason }}</span>
        </template>
        <template v-else>No payment methods are available.</template>
      </span>
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

      <!-- Delivery only: a collecting customer's step 2 is "Your details" and
           already the billing address (spec 0013 §F.1), so there is nothing
           to ask. Unticked: the customer's own billing address, captured here
           and stored on the cart before pay() pins the session. -->
      <template v-if="state.fulfilment !== 'collect'">
        <label class="check" style="margin-top: 14px">
          <input v-model="state.billingSame" type="checkbox" />
          <span class="box ico"><Icon name="check" /></span>
          <span class="txt">Use delivery address as billing address</span>
        </label>

        <BillingSection v-if="!state.billingSame" />
      </template>

      <div v-if="state.payError" class="alert a-error pay-error-inline" role="alert" style="margin-top: 14px">
        <Icon name="alert-circle" :size="18" />
        <span>{{ state.payError }}</span>
      </div>
    </template>
  </section>
</template>
