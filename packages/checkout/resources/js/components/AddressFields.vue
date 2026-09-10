<script setup>
import { computed, ref } from 'vue'
import Icon from './primitives/Icon.vue'
import FloatingField from './primitives/FloatingField.vue'
import { useCheckout } from '../composables/useCheckout.js'

// The manual address form shared by the delivery step and the billing form
// under the payment step: name, company, postcode lookup, the address lines
// and the country select. The parent owns the form object and the errors;
// this only fills fields in. Persistence stays with the parent so address
// writes keep exactly one path each (spec 0011 §C).
const props = defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  // Prefixes every field id so two forms on one page stay distinct.
  idPrefix: { type: String, default: '' },
  phoneLabel: { type: String, default: 'Phone (for delivery updates)' },
})

const { state, postJson } = useCheckout()

const id = (name) => `${props.idPrefix}${name}`

// Server validation keys are the payload's names (first_name, line1, ...) and
// the parent's client-side gate writes the same keys, so every message can
// land on the field it belongs to. Falls through when a field has no error.
const fieldError = (...keys) => keys.map((key) => props.errors[key]).find(Boolean) || ''

// Spec 0011 §H: the server projects the only countries it will accept. One
// country needs no choice, so a read-only line names it instead of a select
// with nothing to pick; the value is still posted.
const countries = computed(() => state.countries ?? [])
const onlyCountry = computed(() => (countries.value.length === 1 ? countries.value[0] : null))

// A null url means no driver can answer, so the search never renders.
const lookupEnabled = computed(() => Boolean(state.urls.addressLookup))
const lookupPostcode = ref('')
const lookupResults = ref([])
const lookupBusy = ref(false)
const lookupError = ref('')

async function findAddresses() {
  if (lookupBusy.value || !lookupPostcode.value.trim()) return

  lookupBusy.value = true
  lookupError.value = ''
  lookupResults.value = []

  try {
    const result = await postJson(
      state.urls.addressLookup,
      { postcode: lookupPostcode.value },
      'We could not search for that postcode. Enter your address manually.',
    )
    lookupResults.value = result.addresses ?? []

    if (lookupResults.value.length === 0) {
      lookupError.value = 'No addresses found for that postcode. Enter your address manually.'
    }
  } catch (error) {
    lookupError.value = error?.message || 'We could not search for that postcode. Enter your address manually.'
  } finally {
    lookupBusy.value = false
  }
}

function chooseAddress(index) {
  const address = lookupResults.value[index]
  if (!address) return

  props.form.companyName = address.companyName ?? ''
  props.form.line1 = address.line1 ?? ''
  props.form.line2 = address.line2 ?? ''
  props.form.city = address.city ?? ''
  props.form.postcode = address.postcode ?? ''
  props.form.country = address.countryCode ?? props.form.country

  lookupResults.value = []
}
</script>

<template>
  <div>
    <div class="stack" style="margin-bottom: 12px">
      <FloatingField
        :id="id('first')"
        v-model="form.name"
        label="Full name"
        autocomplete="name"
        :error="fieldError('first_name', 'last_name')"
      />
      <FloatingField
        :id="id('company')"
        v-model="form.companyName"
        label="Company"
        autocomplete="organization"
        optional
        :error="fieldError('company_name')"
      />
    </div>

    <!-- Postcode lookup (spec 0011 §B). Rendered only when a driver can answer;
         with the null driver the customer gets honest manual entry below. -->
    <div v-if="lookupEnabled" class="search search-lookup" style="margin-bottom: 12px">
      <span class="lead ico"><Icon name="search" :size="18" /></span>
      <label class="sr-only" :for="id('addr-search')">Search for your address by postcode</label>
      <input
        :id="id('addr-search')"
        v-model="lookupPostcode"
        type="text"
        autocomplete="off"
        placeholder="Enter your postcode"
        style="text-transform: uppercase"
        @keydown.enter.prevent="findAddresses"
      />
      <button type="button" class="search-btn" :disabled="lookupBusy" @click="findAddresses">
        {{ lookupBusy ? 'Searching…' : 'Find address' }}
      </button>
    </div>

    <p v-if="lookupError" class="help" role="alert" style="color: var(--error-700)">
      {{ lookupError }}
    </p>

    <div v-if="lookupResults.length" class="lookup-results" style="margin-bottom: 12px">
      <p class="lookup-results-caption">
        <Icon name="map-pin" :size="15" />
        {{ lookupResults.length }}
        {{ lookupResults.length === 1 ? 'address' : 'addresses' }}
        found for {{ lookupPostcode.trim().toUpperCase() }}
      </p>
      <div class="fl">
        <select :id="id('addr-results')" @change="chooseAddress($event.target.value)">
          <option value="">Choose from the list</option>
          <option v-for="(address, index) in lookupResults" :key="index" :value="index">
            {{ [address.line1, address.line2, address.city].filter(Boolean).join(', ') }}
          </option>
        </select>
        <label :for="id('addr-results')">Select your address</label>
        <span class="chev ico"><Icon name="chevron-down" :size="18" /></span>
      </div>
    </div>

    <div class="stack">
      <FloatingField
        :id="id('line1')"
        v-model="form.line1"
        label="Address"
        autocomplete="address-line1"
        :error="fieldError('line1')"
      />
      <FloatingField
        :id="id('line2')"
        v-model="form.line2"
        label="Apartment, suite, etc."
        autocomplete="address-line2"
        optional
        :error="fieldError('line2')"
      />
      <div class="row2">
        <FloatingField
          :id="id('city')"
          v-model="form.city"
          label="Town / city"
          autocomplete="address-level2"
          :error="fieldError('city')"
        />
        <FloatingField
          :id="id('postcode')"
          v-model="form.postcode"
          label="Postcode"
          autocomplete="postal-code"
          style="text-transform: uppercase"
          :error="fieldError('postcode')"
        />
      </div>
      <p v-if="onlyCountry" class="country-fixed">
        Country / region: <strong>{{ onlyCountry.name }}</strong>
      </p>
      <div v-else>
        <div class="fl" :class="{ 'has-error': fieldError('country_code') }">
          <select :id="id('country')" v-model="form.country" autocomplete="country">
            <option v-for="country in countries" :key="country.code" :value="country.code">
              {{ country.name }}
            </option>
          </select>
          <label :for="id('country')">Country / region</label>
          <span class="chev ico"><Icon name="chevron-down" :size="18" /></span>
        </div>
        <div class="err-msg" :class="{ show: fieldError('country_code') }" role="alert">
          <span class="ico"><Icon name="alert-circle" :size="14" /></span
          ><span class="t">{{ fieldError('country_code') }}</span>
        </div>
      </div>
      <FloatingField
        :id="id('phone')"
        v-model="form.phone"
        :label="phoneLabel"
        type="tel"
        autocomplete="tel"
        inputmode="tel"
        optional
        :error="fieldError('phone')"
      />
    </div>
  </div>
</template>
