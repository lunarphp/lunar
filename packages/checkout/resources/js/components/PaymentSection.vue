<script setup>
import { computed, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state, fmt, breakdown, totalLabel } = useCheckout()

const card = ref('')
const exp = ref('')
const cvc = ref('')
const name = ref('')
const cardFocus = ref(false)
const billingSame = ref(true)

const methods = [
  { id: 'card', label: 'Card', icon: 'credit-card' },
  { id: 'paypal', label: 'PayPal' },
  { id: 'clearpay', label: 'Pay in 4' },
  { id: 'klarna', label: 'Pay later' },
]

const formatCard = (v) => v.replace(/\D/g, '').slice(0, 16).replace(/(.{4})/g, '$1 ').trim()
const formatExp = (v) => {
  const d = v.replace(/\D/g, '').slice(0, 4)
  return d.length >= 3 ? d.slice(0, 2) + ' / ' + d.slice(2) : d
}

// Instalment timelines (cosmetic).
const instalments = (count) => {
  const each = Math.round(breakdown.value.total / count)
  return Array.from({ length: count }, (_, i) => ({
    amount: fmt(i === count - 1 ? breakdown.value.total - each * (count - 1) : each),
    when: i === 0 ? 'Today' : `Wk ${i * 2}`,
  }))
}
const clearpaySteps = computed(() => instalments(4))
const klarnaSteps = computed(() => instalments(3))
</script>

<template>
  <section class="block" data-block="payment">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"><span class="num">4</span><span class="chk ico"><Icon name="check" /></span></span>
        Payment
      </h2>
    </div>

    <div class="secure-note">
      <span class="txt"><span class="ico"><Icon name="lock" :size="15" /></span> All transactions are secure and encrypted</span>
      <span class="netmarks" aria-hidden="true">
        <span class="nmk visa">VISA</span>
        <span class="nmk mc"><span class="c1"></span><span class="c2"></span></span>
        <span class="nmk amex">AMEX</span>
        <span class="nmk more">+4</span>
      </span>
    </div>

    <div class="pm-tabs" role="radiogroup" aria-label="Payment method">
      <button
        v-for="m in methods"
        :key="m.id"
        type="button"
        class="pm-tab"
        :class="{ on: state.method === m.id }"
        role="radio"
        :aria-checked="state.method === m.id"
        @click="state.method = m.id"
      >
        <span class="mk">
          <span v-if="m.icon" class="ico"><Icon :name="m.icon" :size="21" /></span>
          <span v-else class="lbl" style="font-weight: 800">{{ m.label }}</span>
        </span>
        <span class="lbl">{{ m.label }}</span>
      </button>
    </div>

    <!-- Card -->
    <div v-if="state.method === 'card'" class="pm-panel">
      <div class="card-group" :class="{ 'is-focus': cardFocus }">
        <div class="cg-cell cg-num">
          <span class="lead ico"><Icon name="credit-card" :size="18" /></span>
          <input
            inputmode="numeric"
            placeholder="Card number"
            :value="card"
            @input="card = formatCard($event.target.value)"
            @focus="cardFocus = true"
            @blur="cardFocus = false"
          />
          <span class="trail"><span class="ico" style="font-size: 16px"><Icon name="lock" :size="16" /></span></span>
        </div>
        <div class="cg-split">
          <div class="cg-cell cg-exp">
            <input
              inputmode="numeric"
              placeholder="MM / YY"
              :value="exp"
              @input="exp = formatExp($event.target.value)"
              @focus="cardFocus = true"
              @blur="cardFocus = false"
            />
          </div>
          <div class="cg-cell cg-cvc">
            <input
              inputmode="numeric"
              placeholder="CVC"
              :value="cvc"
              @input="cvc = $event.target.value.replace(/\D/g, '').slice(0, 4)"
              @focus="cardFocus = true"
              @blur="cardFocus = false"
            />
            <span class="trail"><span class="ico" style="font-size: 16px"><Icon name="help-circle" :size="16" /></span></span>
          </div>
        </div>
      </div>

      <div style="margin-top: 12px">
        <FloatingField id="card-name" v-model="name" label="Name on card" autocomplete="cc-name" />
      </div>

      <label class="check" style="margin-top: 14px">
        <input type="checkbox" v-model="billingSame" />
        <span class="box ico"><Icon name="check" /></span>
        <span class="txt">Use delivery address as billing address</span>
      </label>
    </div>

    <!-- PayPal -->
    <div v-else-if="state.method === 'paypal'" class="pm-panel">
      <div class="pm-redirect">
        <span class="ico"><Icon name="external-link" :size="20" /></span>
        <p class="rt">
          You'll be securely redirected to <strong>PayPal</strong> to approve your payment of
          <strong class="mono">{{ totalLabel }}</strong>. No card details are entered here.
        </p>
      </div>
    </div>

    <!-- Clearpay -->
    <div v-else-if="state.method === 'clearpay'" class="pm-panel">
      <div class="instal-head"><span class="t">4 interest-free payments</span><span class="s">with Clearpay</span></div>
      <div class="instal">
        <div v-for="(s, i) in clearpaySteps" :key="i" class="instal-step" :class="{ first: i === 0 }">
          <div class="a">{{ s.amount }}</div>
          <div class="w">{{ s.when }}</div>
        </div>
      </div>
      <p class="pm-note"><span class="ico"><Icon name="info" :size="13" /></span> First payment today, then every 2 weeks. No interest, no fees when you pay on time.</p>
    </div>

    <!-- Klarna -->
    <div v-else-if="state.method === 'klarna'" class="pm-panel">
      <div class="instal-head"><span class="t">Pay in 3, interest-free</span><span class="s">with Klarna</span></div>
      <div class="instal">
        <div v-for="(s, i) in klarnaSteps" :key="i" class="instal-step" :class="{ first: i === 0 }">
          <div class="a">{{ s.amount }}</div>
          <div class="w">{{ s.when }}</div>
        </div>
      </div>
      <p class="pm-note"><span class="ico"><Icon name="info" :size="13" /></span> Klarna will confirm your plan and run a soft check that won't affect your credit score.</p>
    </div>
  </section>
</template>
