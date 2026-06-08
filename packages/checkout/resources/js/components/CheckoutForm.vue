<script setup>
import { ref } from 'vue'
import Icon from './primitives/Icon.vue'
import Button from './primitives/Button.vue'
import Field from './primitives/Field.vue'
import TextInput from './primitives/TextInput.vue'
import Select from './primitives/Select.vue'
import NetworkMarks from './primitives/NetworkMarks.vue'

defineProps({
  totalLabel: { type: String, default: '' },
  processing: Boolean,
})

const emit = defineEmits(['pay'])

const method = ref('card')
const email = ref('')
const card = ref('')
const exp = ref('')
const cvc = ref('')
const name = ref('')
const zip = ref('')
const country = ref('US')
const cardFocus = ref(false)
const save = ref(true)

const formatCard = (v) =>
  v
    .replace(/\D/g, '')
    .slice(0, 16)
    .replace(/(.{4})/g, '$1 ')
    .trim()

const formatExp = (v) => {
  const d = v.replace(/\D/g, '').slice(0, 4)
  return d.length >= 3 ? d.slice(0, 2) + ' / ' + d.slice(2) : d
}

const onCard = (e) => (card.value = formatCard(e.target.value))
const onExp = (e) => (exp.value = formatExp(e.target.value))
const onCvc = (e) => (cvc.value = e.target.value.replace(/\D/g, '').slice(0, 4))
</script>

<template>
  <div class="co-form-side">
    <div class="co-form-inner">
      <h2 class="co-form-title">Payment details</h2>

      <div class="fsection">
        <Field label="Email">
          <TextInput v-model="email" type="email" lead-icon="mail" placeholder="you@example.com" />
        </Field>
      </div>

      <div class="fsection">
        <span class="flabel">Payment method</span>
        <div class="pay-tabs">
          <button :class="['pay-tab', method === 'card' && 'active']" @click="method = 'card'">
            <Icon name="credit-card" :size="18" /> Card
          </button>
          <button :class="['pay-tab', 'wallet', method === 'apple' && 'active']" @click="method = 'apple'">
            <Icon name="apple" :size="18" /> Pay
          </button>
          <button :class="['pay-tab', 'wallet', method === 'google' && 'active']" @click="method = 'google'">
            <Icon name="wallet" :size="18" /> Wallet
          </button>
        </div>

        <template v-if="method === 'card'">
          <Field>
            <div :class="['card-group', cardFocus && 'is-focus']">
              <div class="cg-row">
                <div class="cg-cell cg-num">
                  <span class="lead-ico"><Icon name="credit-card" :size="17" /></span>
                  <input
                    inputmode="numeric"
                    placeholder="Card number"
                    :value="card"
                    @input="onCard"
                    @focus="cardFocus = true"
                    @blur="cardFocus = false"
                  />
                  <NetworkMarks />
                </div>
              </div>
              <div class="cg-row">
                <div class="cg-cell cg-exp">
                  <input
                    inputmode="numeric"
                    placeholder="MM / YY"
                    :value="exp"
                    @input="onExp"
                    @focus="cardFocus = true"
                    @blur="cardFocus = false"
                  />
                </div>
                <div class="cg-cell cg-cvc">
                  <input
                    inputmode="numeric"
                    placeholder="CVC"
                    :value="cvc"
                    @input="onCvc"
                    @focus="cardFocus = true"
                    @blur="cardFocus = false"
                  />
                  <span style="color: var(--fg-tertiary); display: inline-flex">
                    <Icon name="help-circle" :size="16" />
                  </span>
                </div>
              </div>
            </div>
          </Field>

          <Field label="Name on card">
            <TextInput v-model="name" placeholder="Full name" />
          </Field>

          <div class="field-row">
            <Field label="Country">
              <Select v-model="country">
                <option value="US">United States</option>
                <option value="GB">United Kingdom</option>
                <option value="CA">Canada</option>
                <option value="AU">Australia</option>
              </Select>
            </Field>
            <Field label="ZIP code">
              <TextInput v-model="zip" inputmode="numeric" placeholder="90210" />
            </Field>
          </div>

          <div :class="['checkrow', save && 'on']" @click="save = !save">
            <span class="box"><Icon name="check" :size="14" /></span>
            <span class="txt">Securely save this card for faster checkout next time.</span>
          </div>
        </template>

        <div v-else style="padding: 8px 0 16px">
          <Button variant="wallet" block @click="emit('pay')">
            <Icon :name="method === 'apple' ? 'apple' : 'wallet'" :size="18" />
            {{ method === 'apple' ? 'Pay with Apple Pay' : 'Pay with Wallet' }}
          </Button>
          <p class="legal">You'll confirm the {{ totalLabel }} charge in your wallet.</p>
        </div>
      </div>

      <div v-if="method === 'card'" class="pay-cta">
        <Button variant="primary" block :loading="processing" :disabled="processing" @click="emit('pay')">
          <Icon name="lock" :size="16" /> Pay {{ totalLabel }}
        </Button>
        <p class="legal">
          By paying you agree to our <a href="#">terms</a> and <a href="#">privacy policy</a>. You can cancel anytime.
        </p>
      </div>

      <div class="powered">Powered by <strong>Lunar</strong></div>
    </div>
  </div>
</template>
