<script setup>
import { computed, reactive, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import AddressFields from './AddressFields.vue'
import { useCheckout } from '../composables/useCheckout.js'

// The billing address form under the payment method, shown when "Use
// delivery address as billing address" is unticked. Same fields and postcode
// lookup as the delivery step; persistence goes through the billing-address
// route, and the projection brings the stored address back.
const { state, storeBillingAddress, billingDiffers } = useCheckout()

// Only a stored billing address that is not the delivery address copied
// across is the customer's own, so only that one pre-fills and collapses.
const stored = billingDiffers.value ? state.billingAddress : null

const defaultCountry = state.countries[0]?.code ?? 'GB'

const form = reactive({
  name: [stored?.firstName, stored?.lastName].filter(Boolean).join(' '),
  companyName: stored?.companyName ?? '',
  line1: stored?.line1 ?? '',
  line2: stored?.line2 ?? '',
  city: stored?.city ?? '',
  postcode: stored?.postcode ?? '',
  country: stored?.countryCode ?? defaultCountry,
  phone: stored?.phone ?? '',
})

const editing = ref(stored === null)
const saving = ref(false)
const errors = ref({})

const inlineErrorKeys = [
  'first_name',
  'last_name',
  'company_name',
  'line1',
  'line2',
  'city',
  'postcode',
  'country_code',
  'phone',
]
const otherErrors = computed(() =>
  Object.entries(errors.value)
    .filter(([key]) => !inlineErrorKeys.includes(key))
    .map(([, message]) => message),
)

const summary = computed(() => {
  const address = state.billingAddress
  if (!address) return null

  return {
    name: [address.firstName, address.lastName].filter(Boolean).join(' '),
    line: [address.line1, address.line2, address.city, address.postcode].filter(Boolean).join(', '),
  }
})

// Same required-field gate as the delivery form: the button stays clickable
// and explains itself inline rather than sitting disabled.
function save() {
  if (saving.value) return

  const missing = {}
  if (!form.name.trim()) missing.first_name = 'Enter the name on the billing address.'
  if (!form.line1.trim()) missing.line1 = 'Enter the first line of the address.'
  if (!form.city.trim()) missing.city = 'Enter the town or city.'
  if (!form.postcode.trim()) missing.postcode = 'Enter the postcode.'

  if (Object.keys(missing).length > 0) {
    errors.value = missing
    return
  }

  const name = form.name.trim()
  const splitAt = name.lastIndexOf(' ')

  storeBillingAddress(
    {
      first_name: splitAt === -1 ? name : name.slice(0, splitAt),
      last_name: splitAt === -1 ? name : name.slice(splitAt + 1),
      company_name: form.companyName || null,
      line1: form.line1,
      line2: form.line2 || null,
      city: form.city,
      postcode: form.postcode,
      country_code: form.country,
      phone: form.phone || null,
    },
    {
      onStart: () => {
        saving.value = true
        errors.value = {}
      },
      onSuccess: () => {
        editing.value = false
        state.payError = ''
      },
      onError: (err) => (errors.value = err),
      onFinish: () => (saving.value = false),
    },
  )
}
</script>

<template>
  <div class="billing" style="margin-top: 14px">
    <div class="block-head">
      <p class="lookup-results-caption" style="margin: 0">
        <Icon name="map-pin" :size="15" />
        Billing address
      </p>
      <button v-if="!editing" type="button" class="block-action" @click="editing = true">Change</button>
    </div>

    <div v-if="!editing && summary" class="deliver-to">
      <p class="deliver-to-name">{{ summary.name }}</p>
      <p class="deliver-to-line">{{ summary.line }}</p>
    </div>

    <template v-else>
      <AddressFields :form="form" :errors="errors" id-prefix="billing-" phone-label="Phone" />

      <div class="stack" style="margin-top: 12px">
        <p v-for="message in otherErrors" :key="message" class="help" role="alert" style="color: var(--error-700)">
          {{ message }}
        </p>

        <button type="button" class="btn btn-secondary btn-step" :disabled="saving" @click="save">
          <span v-if="saving" class="spinner"></span>
          <template v-else>Save billing address</template>
        </button>
      </div>
    </template>
  </div>
</template>
