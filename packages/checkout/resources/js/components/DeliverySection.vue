<script setup>
import { computed, reactive, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'
import { useCheckout } from '../composables/useCheckout.js'

const { state, storeShippingAddress } = useCheckout()

// Hydrate from the cart's stored address so a returning session round-trips.
const stored = state.shippingAddress

const form = reactive({
  name: [stored?.firstName, stored?.lastName].filter(Boolean).join(' '),
  line1: stored?.line1 ?? '',
  line2: stored?.line2 ?? '',
  city: stored?.city ?? '',
  postcode: stored?.postcode ?? '',
  country: stored?.countryCode ?? 'GB',
  phone: stored?.phone ?? '',
})

const complete = computed(() => Boolean(form.name && form.line1 && form.city && form.postcode))
const saving = ref(false)
const errors = ref({})

// The cart address is the source of truth for whether the shipping step is
// unlocked (state.addressValid) — the local form only gates the save button.
function save() {
  if (!complete.value || saving.value) return

  const name = form.name.trim()
  const splitAt = name.lastIndexOf(' ')

  storeShippingAddress(
    {
      first_name: splitAt === -1 ? name : name.slice(0, splitAt),
      last_name: splitAt === -1 ? name : name.slice(splitAt + 1),
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
        <span class="block-step"><span class="num">2</span><span class="chk ico"><Icon name="check" /></span></span>
        Delivery details
      </h2>
    </div>

    <div class="stack" style="margin-bottom: 12px">
      <FloatingField id="first" v-model="form.name" label="Full name" autocomplete="name" />
    </div>

    <!-- Address search (presentational — autocomplete lands with the full flow) -->
    <div class="search" style="margin-bottom: 12px">
      <span class="lead ico"><Icon name="search" :size="18" /></span>
      <label class="sr-only" for="addr-search">Search for your address</label>
      <input id="addr-search" type="text" autocomplete="off" placeholder="Start typing a postcode or street…" />
    </div>

    <div class="stack">
      <FloatingField id="line1" v-model="form.line1" label="Address" autocomplete="address-line1" />
      <FloatingField id="line2" v-model="form.line2" label="Apartment, suite, etc." autocomplete="address-line2" optional />
      <div class="row2">
        <FloatingField id="city" v-model="form.city" label="Town / city" autocomplete="address-level2" />
        <FloatingField
          id="postcode"
          v-model="form.postcode"
          label="Postcode"
          autocomplete="postal-code"
          style="text-transform: uppercase"
        />
      </div>
      <div class="fl">
        <select id="country" v-model="form.country" autocomplete="country">
          <option value="GB">United Kingdom</option>
          <option value="IE">Ireland</option>
          <option value="FR">France</option>
          <option value="DE">Germany</option>
          <option value="US">United States</option>
        </select>
        <label for="country">Country / region</label>
        <span class="chev ico"><Icon name="chevron-down" :size="18" /></span>
      </div>
      <FloatingField id="phone" v-model="form.phone" label="Phone (for delivery updates)" type="tel" autocomplete="tel" inputmode="tel" optional />

      <p v-for="(message, field) in errors" :key="field" class="help" style="color: var(--error-700)">
        {{ message }}
      </p>

      <button type="button" class="btn btn-secondary" :disabled="!complete || saving" @click="save">
        <span v-if="saving" class="spinner"></span>
        <template v-else>{{ state.addressValid ? 'Update address' : 'Save & see delivery options' }}</template>
      </button>
    </div>
  </section>
</template>
