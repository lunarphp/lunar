<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import AddressFields from './AddressFields.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state, storeShippingAddress } = useCheckout()

// 'delivery' (default) or 'collect'. Collect keeps the same form and saved
// address picker: Lunar stores the shipping option on the shipping address
// row, and the payment step defaults billing to it, so the customer's own
// address is still captured (spec 0013 §F.1).
const props = defineProps({
  variant: { type: String, default: 'delivery' },
})

const heading = computed(() => (props.variant === 'collect' ? 'Your details' : 'Delivery details'))
const subcopy = computed(() =>
  props.variant === 'collect' ? "We'll use this as your billing address." : '',
)

// Hydrate from the cart's stored address so a returning session round-trips.
const stored = state.shippingAddress

// The first projected delivery country (spec 0011 §H) is the store's home
// market, so a blank form starts there.
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

const saving = ref(false)
const errors = ref({})

// The address fields render their own inline messages by payload key;
// anything else (unexpected keys) still surfaces below the form instead of
// failing silently.
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

// --- Saved addresses (the signed-in customer's address book) ----------------
//
// With saved addresses the step is a picker, not a form: the top address
// (last used first, then shipping default, per the projection) is applied
// automatically, the
// collapsed view shows what is being delivered to, and "Change" re-opens the
// choice. The manual form only appears for guests, for a stored address that
// matches no card (it was hand-entered), or via "Add a new address".

// Line 1 + postcode is identity enough; false negatives just show no match.
const matches = (entry, address) =>
  Boolean(address && address.line1 === entry.address.line1 && address.postcode === entry.address.postcode)

const mode = ref(
  state.savedAddresses.length === 0 || (stored?.line1 && !state.savedAddresses.some((entry) => matches(entry, stored)))
    ? 'form'
    : 'book',
)
const choosing = ref(false)

const selectedEntry = computed(() => state.savedAddresses.find((entry) => matches(entry, state.shippingAddress)))

// "Pick the top one and use it": arriving with saved addresses and no cart
// address yet applies the first without a click.
onMounted(() => {
  if (mode.value === 'book' && !state.addressValid) {
    useSaved(state.savedAddresses[0])
  }
})

// A saved address is complete (name included), so selecting one saves straight
// through the shipping-address route. It also fills the form so "Add a new
// address" edits start from something real if the customer switches over.
// The fields post verbatim rather than round-tripping through the joined
// name input, which would mis-split multi-word surnames.
function useSaved(entry) {
  if (saving.value || !entry) return
  const address = entry.address

  form.name = [address.firstName, address.lastName].filter(Boolean).join(' ')
  form.companyName = address.companyName ?? ''
  form.line1 = address.line1 ?? ''
  form.line2 = address.line2 ?? ''
  form.city = address.city ?? ''
  form.postcode = address.postcode ?? ''
  form.country = address.countryCode ?? defaultCountry
  form.phone = address.phone ?? ''

  storeShippingAddress(
    {
      first_name: address.firstName,
      last_name: address.lastName,
      company_name: address.companyName || null,
      line1: address.line1,
      line2: address.line2 || null,
      city: address.city,
      postcode: address.postcode,
      country_code: address.countryCode,
      phone: address.phone || null,
    },
    {
      onStart: () => {
        saving.value = true
        errors.value = {}
      },
      onSuccess: () => {
        choosing.value = false
      },
      // A saved address the server refuses (missing postcode, retired
      // country) re-opens the choice with the reasons underneath.
      onError: (err) => {
        errors.value = err
        choosing.value = true
      },
      onFinish: () => (saving.value = false),
    },
  )
}

// "Add a new address": a blank form, not an edit of the saved one.
function startNewAddress() {
  mode.value = 'form'
  choosing.value = false
  errors.value = {}
  form.name = ''
  form.companyName = ''
  form.line1 = ''
  form.line2 = ''
  form.city = ''
  form.postcode = ''
  form.country = defaultCountry
  form.phone = ''
}

// The cart address is the source of truth for whether the shipping step is
// unlocked (state.addressValid). The save button stays clickable while the
// form is incomplete: a disabled control with no message reads as broken, so
// the required-field gate runs on click and explains itself inline.
function save() {
  if (saving.value) return

  const missing = {}
  if (!form.name.trim()) missing.first_name = 'Enter the full name for the delivery.'
  if (!form.line1.trim()) missing.line1 = 'Enter the first line of the address.'
  if (!form.city.trim()) missing.city = 'Enter the town or city.'
  if (!form.postcode.trim()) missing.postcode = 'Enter the postcode.'

  if (Object.keys(missing).length > 0) {
    errors.value = missing
    return
  }

  const name = form.name.trim()
  const splitAt = name.lastIndexOf(' ')

  storeShippingAddress(
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
      onError: (err) => (errors.value = err),
      onFinish: () => (saving.value = false),
    },
  )
}
</script>

<template>
  <section class="block" data-block="address" :class="{ 'is-done': state.addressValid }">
    <div class="block-head">
      <h2 class="block-title">
        <span class="block-step"
          ><span class="num">2</span><span class="chk ico"><Icon name="check" /></span
        ></span>
        {{ heading }}
      </h2>
      <button v-if="mode === 'book' && !choosing" type="button" class="block-action" @click="choosing = true">
        Change
      </button>
      <button
        v-else-if="mode === 'form' && state.savedAddresses.length"
        type="button"
        class="block-action"
        @click="((mode = 'book'), (choosing = true), (errors = {}))"
      >
        Use a saved address
      </button>
    </div>
    <p v-if="subcopy" class="block-sub">{{ subcopy }}</p>

    <!-- Address book (signed in): the picker replaces the form entirely.
         Collapsed it names the destination; Change re-opens the cards. -->
    <template v-if="mode === 'book'">
      <div class="address-book">
        <template v-if="choosing">
          <p class="lookup-results-caption">
            <Icon name="book-user" :size="15" />
            Deliver to a saved address
          </p>
          <button
            v-for="entry in state.savedAddresses"
            :key="entry.id"
            type="button"
            class="address-card"
            :class="{
              'is-selected': selectedEntry?.id === entry.id,
            }"
            :disabled="saving"
            @click="useSaved(entry)"
          >
            <span class="txt">
              <strong>{{
                entry.title || [entry.address.firstName, entry.address.lastName].filter(Boolean).join(' ')
              }}</strong>
              <span class="sub">{{
                [entry.address.line1, entry.address.city, entry.address.postcode].filter(Boolean).join(', ')
              }}</span>
            </span>
            <span v-if="entry.shippingDefault" class="tag">Default</span>
            <span class="chk ico"><Icon name="check" :size="16" /></span>
          </button>
          <button type="button" class="btn btn-secondary btn-step" :disabled="saving" @click="startNewAddress">
            Add a new address
          </button>
        </template>

        <template v-else>
          <div v-if="selectedEntry" class="deliver-to">
            <p class="deliver-to-name">
              Delivering to
              {{ [selectedEntry.address.firstName, selectedEntry.address.lastName].filter(Boolean).join(' ') }}
            </p>
            <p class="deliver-to-line">
              {{
                [
                  selectedEntry.address.line1,
                  selectedEntry.address.line2,
                  selectedEntry.address.city,
                  selectedEntry.address.postcode,
                ]
                  .filter(Boolean)
                  .join(', ')
              }}
            </p>
          </div>
          <div v-else class="deliver-to">
            <p class="deliver-to-name">{{ saving ? 'Applying your address…' : 'Choose a delivery address' }}</p>
          </div>
        </template>
      </div>

      <p
        v-for="message in Object.values(errors)"
        :key="message"
        class="help"
        role="alert"
        style="color: var(--error-700)"
      >
        {{ message }}
      </p>
    </template>

    <template v-else>
      <AddressFields :form="form" :errors="errors" />

      <div class="stack" style="margin-top: 12px">
        <p v-for="message in otherErrors" :key="message" class="help" role="alert" style="color: var(--error-700)">
          {{ message }}
        </p>

        <button type="button" class="btn btn-secondary btn-step" :disabled="saving" @click="save">
          <span v-if="saving" class="spinner"></span>
          <template v-else>{{ state.addressValid ? 'Update address' : 'Save & see delivery options' }}</template>
        </button>
      </div>
    </template>
  </section>
</template>
