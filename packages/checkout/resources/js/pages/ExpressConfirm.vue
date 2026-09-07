<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { router, useHttp } from '@inertiajs/vue3'
import Icon from '../components/primitives/Icon.vue'
import FloatingField from '../components/primitives/FloatingField.vue'
import BrandHead from '../components/BrandHead.vue'
import OrderSummary from '../components/OrderSummary.vue'
import { COLLECT_UNAVAILABLE, createCheckout } from '../composables/useCheckout.js'
import { useCheckoutTheme } from '../composables/useCheckoutTheme.js'
import { resolveElement } from '../composables/elements.js'

/**
 * The express confirm ("squeeze") page (spec 0012 SD): a wallet already
 * authorised a hold, so this page reviews what it handed back as collapsed,
 * edit-in-place rows and captures only the blanks the wallet left (a name,
 * plus the optional order-details fields) before Confirm & pay pins the hold.
 *
 * Mirrors Show.vue's page contract exactly (checkout / theme / branding);
 * the extra `hold` figure lives on the `checkout` prop rather than in
 * useCheckout's shared state, since only this route ever projects it.
 */
const props = defineProps({
  checkout: { type: Object, required: true },
  theme: { type: Object, default: () => ({}) },
  branding: { type: Object, default: () => ({}) },
})

const store = createCheckout(props.checkout)
const {
  state,
  fmt,
  totalLabel,
  shippingMethod,
  deliveryMethods,
  collectOption,
  pickupPoint,
  pickupPointRequired,
  collectUnavailable,
  selectPickupPoint,
  storeShippingAddress,
  storeElement,
  selectShipping,
  registerPendingWrite,
  flushPendingWrites,
} = store

// Partial reloads from every inline edit below re-render THIS same route
// (CheckoutController::confirm), so `checkout.hold` (projected only here)
// stays fresh right alongside the shared store state.
watch(
  () => props.checkout,
  (fresh) => store.sync(fresh),
  { deep: true },
)

const root = ref(null)
useCheckoutTheme(root, () => props.theme)

const hold = computed(() => props.checkout.hold ?? {})
const holdLabel = computed(() => hold.value.walletLabel || 'your wallet')

const contactElement = computed(() => state.elements.find((el) => el.handle === 'contact') ?? null)
const orderDetailsElement = computed(() => state.elements.find((el) => el.handle === 'order-details') ?? null)

// The Express Checkout Element covers every wallet in one component, so
// there is exactly one express-capable payment method to re-open: found the
// same way ExpressWallets.vue picks which methods to render.
const expressMethod = computed(() => state.paymentMethods.find((m) => m.supportsExpress && m.expressComponent) ?? null)

// Only one row expands at a time, mirroring the design's accordion.
const editingRow = ref(null)
const editing = (row) => editingRow.value === row
const openEdit = (row) => (editingRow.value = row)
const closeEdit = () => (editingRow.value = null)

// ── Contact row ──────────────────────────────────────────────────────────
const contactEmail = ref(contactElement.value?.props?.email ?? '')
const contactSaving = ref(false)
const contactError = ref('')

function saveContact() {
  if (!contactElement.value?.props?.contactUrl || contactSaving.value) return

  router.post(
    contactElement.value.props.contactUrl,
    { email: contactEmail.value },
    {
      preserveScroll: true,
      preserveState: true,
      only: ['checkout'],
      onStart: () => {
        contactSaving.value = true
        contactError.value = ''
      },
      onSuccess: () => closeEdit(),
      onError: (err) => {
        contactError.value = err.email ?? ''
      },
      onFinish: () => {
        contactSaving.value = false
      },
    },
  )
}

// ── Deliver-to row ───────────────────────────────────────────────────────
// The wallet already supplied line1/city/postcode (a delivery-mode hold
// cannot exist without them, see StripeExpress.vue's persistWalletData),
// so unlike the "few details" name field below, the name here is never a
// gap: it round-trips from the address the wallet posted.
const addr = reactive({
  line1: state.shippingAddress?.line1 ?? '',
  line2: state.shippingAddress?.line2 ?? '',
  city: state.shippingAddress?.city ?? '',
  postcode: state.shippingAddress?.postcode ?? '',
  phone: state.shippingAddress?.phone ?? '',
})
const addrSaving = ref(false)
const addrErrors = ref({})
const addrFieldError = (...keys) => keys.map((key) => addrErrors.value[key]).find(Boolean) || ''

// Same last-space split DeliverySection.vue's own save() uses, so a
// single-word name lands fully in first_name rather than guessing.
function splitName(value) {
  const trimmed = value.trim()
  const splitAt = trimmed.lastIndexOf(' ')
  return {
    first_name: splitAt === -1 ? trimmed : trimmed.slice(0, splitAt),
    last_name: splitAt === -1 ? trimmed : trimmed.slice(splitAt + 1),
  }
}

// The wallet already gave us a name for this address (delivery-mode holds
// always carry one, see the comment above); the gap field is the fallback
// for the rare case it didn't.
function nameForAddress() {
  if (state.shippingAddress?.firstName || state.shippingAddress?.lastName) {
    return { first_name: state.shippingAddress.firstName, last_name: state.shippingAddress.lastName }
  }
  return splitName(fullName.value)
}

function saveAddress() {
  if (addrSaving.value) return

  storeShippingAddress(
    {
      ...nameForAddress(),
      company_name: state.shippingAddress?.companyName || null,
      line1: addr.line1,
      line2: addr.line2 || null,
      city: addr.city,
      postcode: addr.postcode,
      country_code: state.shippingAddress?.countryCode || 'GB',
      phone: addr.phone || null,
    },
    {
      onStart: () => {
        addrSaving.value = true
        addrErrors.value = {}
      },
      onSuccess: () => closeEdit(),
      onError: (err) => {
        addrErrors.value = err
      },
      onFinish: () => {
        addrSaving.value = false
      },
    },
  )
}

// ── Payment row / re-open wallet ────────────────────────────────────────
const walletReopened = ref(false)
const needsReauth = ref(false)

function startPaymentEdit() {
  openEdit('payment')
  walletReopened.value = false
}

function cancelPaymentEdit() {
  closeEdit()
  walletReopened.value = false
  // A dismissed re-authorisation still needs doing before Confirm & pay can
  // work again, so the amber banner and disabled CTA stay in place: only a
  // full page reload (the express element's own confirm handler) clears it.
}

function reopenWallet() {
  walletReopened.value = true
}

// ── "A few details to finish" ───────────────────────────────────────────
const fullName = ref([state.shippingAddress?.firstName, state.shippingAddress?.lastName].filter(Boolean).join(' '))
const fullNameValid = computed(() => Boolean(fullName.value.trim()))
const nameSaving = ref(false)
const nameError = ref('')

let nameDebounce = null
let nameInFlight = null

function scheduleNameSave() {
  clearTimeout(nameDebounce)
  nameDebounce = setTimeout(() => {
    nameDebounce = null
    saveName()
  }, 500)
}

function saveName() {
  if (nameDebounce !== null) {
    clearTimeout(nameDebounce)
    nameDebounce = null
  }

  const trimmed = fullName.value.trim()
  if (!trimmed || !state.shippingAddress) return Promise.resolve()
  if (nameSaving.value) return nameInFlight ?? Promise.resolve()

  const a = state.shippingAddress

  nameInFlight = new Promise((resolve) => {
    storeShippingAddress(
      {
        ...splitName(trimmed),
        company_name: a.companyName || null,
        line1: a.line1,
        line2: a.line2 || null,
        city: a.city,
        postcode: a.postcode,
        country_code: a.countryCode || 'GB',
        phone: a.phone || null,
      },
      {
        onStart: () => {
          nameSaving.value = true
          nameError.value = ''
        },
        onError: (err) => {
          nameError.value = err.first_name || err.last_name || ''
        },
        onFinish: () => {
          nameSaving.value = false
          nameInFlight = null
          resolve()
        },
      },
    )
  })

  return nameInFlight
}

function flushName() {
  if (nameDebounce === null && !nameSaving.value) return Promise.resolve()
  return saveName()
}

const unregisterName = registerPendingWrite(flushName)
onBeforeUnmount(unregisterName)

// Order reference / delivery notes: the OrderDetails element bag (reuses its
// endpoint and debounce pattern; the fields are re-laid-out here rather than
// embedding OrderDetails.vue's numbered-step markup, which this page doesn't
// use anywhere else).
const orderReference = ref(orderDetailsElement.value?.data?.reference ?? '')
const orderNotes = ref(orderDetailsElement.value?.data?.notes ?? '')
const detailsSaving = ref(false)

let detailsDebounce = null
let detailsInFlight = null

function scheduleDetailsSave() {
  clearTimeout(detailsDebounce)
  detailsDebounce = setTimeout(() => {
    detailsDebounce = null
    saveDetails()
  }, 500)
}

function saveDetails() {
  if (detailsDebounce !== null) {
    clearTimeout(detailsDebounce)
    detailsDebounce = null
  }

  if (!orderDetailsElement.value?.storeUrl) return Promise.resolve()
  if (detailsSaving.value) return detailsInFlight ?? Promise.resolve()

  detailsInFlight = storeElement(
    orderDetailsElement.value,
    { reference: orderReference.value, notes: orderNotes.value },
    {
      onStart: () => {
        detailsSaving.value = true
      },
      onFinish: () => {
        detailsSaving.value = false
        detailsInFlight = null
      },
    },
  )

  return detailsInFlight
}

function flushDetails() {
  if (detailsDebounce === null && !detailsSaving.value) return Promise.resolve()
  return saveDetails()
}

const unregisterDetails = registerPendingWrite(flushDetails)
onBeforeUnmount(unregisterDetails)

const newsOptIn = ref(false) // presentational, same as ContactSection's marketing checkbox

// ── Confirm & pay ────────────────────────────────────────────────────────
const confirming = ref(false)
const payError = ref('')
const canConfirm = computed(() => fullNameValid.value && !needsReauth.value && !confirming.value)
const payHint = computed(() => {
  if (needsReauth.value) return 'Re-open your wallet to confirm the new total'
  if (!fullNameValid.value) return 'Add your name to confirm'
  return ''
})

function focusHint() {
  if (needsReauth.value) {
    startPaymentEdit()
    reopenWallet()
    return
  }
  document.getElementById('gap-name')?.focus()
}

const payForm = useHttp({ payment_method: '', fingerprint: '' })

async function confirmAndPay() {
  if (confirming.value || !canConfirm.value) return

  if (collectUnavailable.value) {
    payError.value = COLLECT_UNAVAILABLE
    return
  }

  if (pickupPointRequired.value) {
    payError.value = 'Choose where you would like to collect your order.'
    return
  }

  confirming.value = true
  payError.value = ''

  try {
    // Let any debounced row write (name, order details, an in-flight address
    // save) land before the fingerprint is pinned: the same guarantee
    // store.pay() gives the main checkout page (spec 0010 §E).
    await flushPendingWrites()

    payForm.payment_method = expressMethod.value?.handle ?? state.method
    payForm.fingerprint = state.fingerprint

    const response = await payForm.post(state.urls.pay, { onHttpException: () => {} })

    if (payForm.hasErrors) {
      if (payForm.errors.hold === 'hold_reauthorization_required') {
        needsReauth.value = true
        startPaymentEdit()
        reopenWallet()
      } else {
        payError.value =
          payForm.errors.fingerprint || payForm.errors.payment_method || 'Payment could not be confirmed.'
      }
      confirming.value = false
      return
    }

    const target = (response?.data ?? response)?.processing
    window.location.assign(target || state.urls.processing)
    // Deliberately still `confirming`: the pay is pinned and the browser is
    // navigating to the processing page. Re-arming the button here opens a
    // double-submit window for as long as that navigation takes.
  } catch {
    payError.value = 'Something went wrong confirming your payment. Please try again.'
    confirming.value = false
  }
}
</script>

<template>
  <div ref="root" class="lunar-checkout xc-shell">
    <!-- Mobile · collapsible summary bar -->
    <div class="m-summary-bar">
      <button class="m-summary-toggle" :aria-expanded="false">
        <span class="lft"
          ><span class="ico"><Icon name="shopping-bag" :size="18" /></span> Order summary</span
        >
        <span class="rgt">
          <span class="tot mono">{{ totalLabel }}</span>
        </span>
      </button>
    </div>

    <div class="page">
      <div class="form-side">
        <div class="form-col">
          <BrandHead
            :merchant="checkout.merchant"
            :logo="branding.logo"
            :logo-alt="branding.logoAlt"
            :back-url="state.urls.back"
          />

          <div class="xc-intro">
            <span class="xc-eyebrow"
              ><span class="ico"><Icon name="zap" :size="14" /></span> Express checkout</span
            >
            <h1 class="xc-h1">Confirm your order</h1>
            <p class="xc-sub">Review the details below and fill in the last few we still need.</p>
          </div>

          <!-- Status band -->
          <div class="xc-auth" :class="{ 'is-reauth': needsReauth }">
            <template v-if="needsReauth">
              <span class="xc-auth-ico"><Icon name="alert-triangle" :size="18" /></span>
              <div class="xc-auth-main">
                <p class="xc-auth-title">Your total has changed</p>
                <p class="xc-auth-sub">
                  Your <span class="wallet-name">{{ holdLabel }}</span> hold can't stretch to cover the new total.
                  Re-open your wallet below to re-authorise.
                </p>
                <div class="xc-auth-delta mono">
                  <span class="from">{{ fmt(hold.amountAuthorised) }}</span>
                  <span class="arr ico"><Icon name="arrow-right" :size="14" /></span>
                  <span class="to">{{ totalLabel }}</span>
                </div>
              </div>
            </template>
            <template v-else>
              <div class="xc-auth-main">
                <p class="xc-auth-title">Your order isn't placed yet</p>
                <p class="xc-auth-sub">
                  Your card's authorised with <span class="wallet-name">{{ holdLabel }}</span
                  >: a hold for <span class="hold mono">{{ fmt(hold.amountAuthorised) }}</span
                  >, charged only when you tap <strong>Confirm &amp; pay</strong>.
                </p>
              </div>
            </template>
          </div>

          <!-- From your wallet -->
          <section class="xc-group" aria-label="Details from your wallet">
            <div class="xc-group-head">
              <h2 class="xc-group-title">From your wallet</h2>
              <span class="xc-group-meta"
                ><span class="ico"><Icon name="check" :size="13" /></span>Confirmed</span
              >
            </div>

            <div class="xc-list">
              <!-- Contact -->
              <div v-if="contactElement" class="xc-row" :class="{ editing: editing('contact') }">
                <div class="xc-row-head">
                  <span class="xc-row-ico"><Icon name="mail" :size="15" /></span>
                  <div class="xc-row-main">
                    <div class="xc-row-label">Contact</div>
                    <div class="xc-row-value">{{ contactEmail || 'Not provided' }}</div>
                  </div>
                  <button type="button" class="xc-row-edit-btn" @click="openEdit('contact')">
                    <Icon name="pencil" :size="14" />Edit
                  </button>
                </div>
                <div class="xc-row-edit">
                  <FloatingField
                    id="rv-contact-email"
                    v-model="contactEmail"
                    label="Email address"
                    type="email"
                    autocomplete="email"
                    :error="contactError"
                  />
                  <div class="xc-edit-actions">
                    <button
                      type="button"
                      class="btn btn-primary xc-edit-save"
                      :disabled="contactSaving"
                      @click="saveContact"
                    >
                      {{ contactSaving ? 'Saving…' : 'Save' }}
                    </button>
                    <button type="button" class="xc-edit-cancel" @click="closeEdit">Cancel</button>
                  </div>
                </div>
              </div>

              <!-- Deliver to -->
              <div v-if="state.fulfilment === 'delivery'" class="xc-row" :class="{ editing: editing('address') }">
                <div class="xc-row-head">
                  <span class="xc-row-ico"><Icon name="map-pin" :size="15" /></span>
                  <div class="xc-row-main">
                    <div class="xc-row-label">Deliver to</div>
                    <div class="xc-row-value">
                      <span class="ln">{{
                        [state.shippingAddress?.line1, state.shippingAddress?.line2].filter(Boolean).join(', ')
                      }}</span>
                      <span class="ln muted">{{
                        [state.shippingAddress?.city, state.shippingAddress?.postcode].filter(Boolean).join(', ')
                      }}</span>
                    </div>
                  </div>
                  <button type="button" class="xc-row-edit-btn" @click="openEdit('address')">
                    <Icon name="pencil" :size="14" />Edit
                  </button>
                </div>
                <div class="xc-row-edit">
                  <div class="stack">
                    <FloatingField
                      id="xc-line1"
                      v-model="addr.line1"
                      label="Address"
                      autocomplete="address-line1"
                      :error="addrFieldError('line1')"
                    />
                    <FloatingField
                      id="xc-line2"
                      v-model="addr.line2"
                      label="Apartment, suite, etc."
                      autocomplete="address-line2"
                      optional
                      :error="addrFieldError('line2')"
                    />
                    <div class="row2">
                      <FloatingField
                        id="xc-city"
                        v-model="addr.city"
                        label="Town / city"
                        autocomplete="address-level2"
                        :error="addrFieldError('city')"
                      />
                      <FloatingField
                        id="xc-postcode"
                        v-model="addr.postcode"
                        label="Postcode"
                        autocomplete="postal-code"
                        style="text-transform: uppercase"
                        :error="addrFieldError('postcode')"
                      />
                    </div>
                    <FloatingField
                      id="xc-phone"
                      v-model="addr.phone"
                      label="Phone"
                      autocomplete="tel"
                      inputmode="tel"
                      optional
                      :error="addrFieldError('phone')"
                    />
                  </div>
                  <div class="xc-edit-actions">
                    <button
                      type="button"
                      class="btn btn-primary xc-edit-save"
                      :disabled="addrSaving"
                      @click="saveAddress"
                    >
                      {{ addrSaving ? 'Saving…' : 'Save' }}
                    </button>
                    <button type="button" class="xc-edit-cancel" @click="closeEdit">Cancel</button>
                  </div>
                </div>
              </div>

              <!-- Collect from (spec 0013 §F) -->
              <div v-else class="xc-row" :class="{ editing: editing('pickup') }">
                <div class="xc-row-head">
                  <span class="xc-row-ico"><Icon name="store" :size="15" /></span>
                  <div class="xc-row-main">
                    <div class="xc-row-label">Collect from</div>
                    <div class="xc-row-value">
                      <template v-if="pickupPoint">
                        <span class="ln">{{ pickupPoint.name }}</span>
                        <span v-for="line in pickupPoint.lines" :key="line" class="ln muted">{{ line }}</span>
                      </template>
                      <template v-else-if="state.pickupPoints.length > 1">
                        <span class="ln muted">Choose a branch</span>
                      </template>
                      <template v-else>
                        <span class="ln">{{ collectOption?.name }}</span>
                        <span v-if="collectOption?.sub" class="ln muted">{{ collectOption.sub }}</span>
                      </template>
                    </div>
                  </div>
                  <button
                    v-if="state.pickupPoints.length > 1"
                    type="button"
                    class="xc-row-edit-btn"
                    @click="openEdit('pickup')"
                  >
                    <Icon name="pencil" :size="14" />{{ pickupPoint ? 'Change' : 'Choose' }}
                  </button>
                </div>
                <div v-if="state.pickupPoints.length > 1" class="xc-row-edit">
                  <div role="radiogroup" aria-label="Choose where to collect your order">
                    <button
                      v-for="point in state.pickupPoints"
                      :key="point.id"
                      type="button"
                      class="pick"
                      role="radio"
                      :aria-checked="state.pickupPointId === point.id"
                      @click="(selectPickupPoint(point.id), closeEdit())"
                    >
                      <span class="radio" aria-hidden="true"></span>
                      <span class="pbody">
                        <span class="ptop"><span class="pname">{{ point.name }}</span></span>
                        <span class="pmeta">{{ point.lines.join(' · ') }}</span>
                      </span>
                    </button>
                  </div>
                  <div class="xc-edit-actions">
                    <button type="button" class="xc-edit-cancel" @click="closeEdit">Cancel</button>
                  </div>
                </div>
              </div>

              <!-- Shipping method -->
              <div v-if="state.fulfilment === 'delivery'" class="xc-row" :class="{ editing: editing('shipping') }">
                <div class="xc-row-head">
                  <span class="xc-row-ico"><Icon name="truck" :size="15" /></span>
                  <div class="xc-row-main">
                    <div class="xc-row-label">Shipping method</div>
                    <div class="xc-row-value">
                      <span class="ln">{{ shippingMethod?.name }}</span>
                      <span class="ln muted">{{ shippingMethod?.price ? fmt(shippingMethod.price) : 'Free' }}</span>
                    </div>
                  </div>
                  <button type="button" class="xc-row-edit-btn" @click="openEdit('shipping')">
                    <Icon name="pencil" :size="14" />Edit
                  </button>
                </div>
                <div class="xc-row-edit">
                  <div role="radiogroup" aria-label="Select a shipping method">
                    <button
                      v-for="m in deliveryMethods"
                      :key="m.id"
                      type="button"
                      class="pick"
                      role="radio"
                      :aria-checked="state.shippingId === m.id"
                      @click="(selectShipping(m.id), closeEdit())"
                    >
                      <span class="radio" aria-hidden="true"></span>
                      <span class="pbody">
                        <span class="ptop">
                          <span class="pname">{{ m.name }}</span>
                          <span class="pprice">{{ m.price ? fmt(m.price) : 'Free' }}</span>
                        </span>
                        <span class="pmeta">{{ m.sub }}</span>
                      </span>
                    </button>
                  </div>
                  <div class="xc-edit-actions">
                    <button type="button" class="xc-edit-cancel" @click="closeEdit">Cancel</button>
                  </div>
                </div>
              </div>

              <!-- Payment -->
              <div class="xc-row" :class="{ editing: editing('payment') }">
                <div class="xc-row-head">
                  <span class="xc-row-ico"><Icon name="credit-card" :size="15" /></span>
                  <div class="xc-row-main">
                    <div class="xc-row-label">Payment</div>
                    <div class="xc-row-value">
                      <span class="ln pay-mark"><Icon name="lock" :size="13" />{{ holdLabel }} · authorised</span>
                    </div>
                  </div>
                  <button type="button" class="xc-row-edit-btn" @click="startPaymentEdit">
                    <Icon name="repeat" :size="14" />Change
                  </button>
                </div>
                <div class="xc-row-edit">
                  <template v-if="!walletReopened">
                    <div class="xc-pay-note">
                      <span class="ico"><Icon name="info" :size="18" /></span>
                      <p>
                        Changing how you pay re-opens <strong>{{ holdLabel }}</strong
                        >. Your current hold is released automatically, you won't be charged twice.
                      </p>
                    </div>
                    <div class="xc-edit-actions">
                      <button type="button" class="btn btn-secondary xc-edit-save" @click="reopenWallet">
                        <Icon name="wallet" :size="15" />Re-open wallet
                      </button>
                      <button type="button" class="xc-edit-cancel" @click="cancelPaymentEdit">Keep this</button>
                    </div>
                  </template>
                  <template v-else-if="expressMethod && resolveElement(expressMethod.expressComponent)">
                    <component :is="resolveElement(expressMethod.expressComponent)" :method="expressMethod" renew />
                    <div class="xc-edit-actions">
                      <button v-if="!needsReauth" type="button" class="xc-edit-cancel" @click="cancelPaymentEdit">
                        Cancel
                      </button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </section>

          <!-- A few details to finish -->
          <section class="xc-group">
            <div class="xc-gaps">
              <div class="xc-group-head">
                <h2 class="xc-group-title">A few details to finish</h2>
                <span class="req-tag" :hidden="fullNameValid">1 still needed</span>
              </div>
              <p class="xc-gaps-sub">
                <span class="wallet-name">{{ holdLabel }}</span> didn't share these: add them so we can complete your
                order.
              </p>

              <div class="xc-req-field">
                <FloatingField
                  id="gap-name"
                  v-model="fullName"
                  label="Full name"
                  autocomplete="name"
                  :error="nameError"
                  @blur="scheduleNameSave"
                />
                <p v-if="!nameError" class="xc-field-help"><Icon name="tag" :size="13" />For your delivery label.</p>
              </div>

              <div class="xc-opt-divider"><span class="lbl">Optional</span></div>

              <div class="stack">
                <div v-if="state.fulfilment === 'delivery'">
                  <div class="fl">
                    <textarea
                      id="gap-notes"
                      v-model="orderNotes"
                      rows="2"
                      maxlength="200"
                      placeholder=" "
                      @blur="scheduleDetailsSave"
                      @change="scheduleDetailsSave"
                    ></textarea>
                    <label for="gap-notes">Delivery instructions <span class="opt">(optional)</span></label>
                  </div>
                  <p class="xc-field-help">
                    <Icon name="info" :size="13" />e.g. "Leave with the concierge" or a safe spot if you're out.
                  </p>
                </div>

                <div>
                  <FloatingField
                    id="gap-ref"
                    v-model="orderReference"
                    label="Order reference or PO number"
                    optional
                    maxlength="40"
                    @blur="scheduleDetailsSave"
                    @change="scheduleDetailsSave"
                  />
                  <p class="xc-field-help">
                    <Icon name="file-text" :size="13" />It'll appear on your receipt, handy for expenses.
                  </p>
                </div>

                <label class="check xc-gap-marketing">
                  <input v-model="newsOptIn" type="checkbox" />
                  <span class="box ico"><Icon name="check" /></span>
                  <span class="txt">Email me with order updates and occasional offers.</span>
                </label>
              </div>
            </div>
          </section>

          <div v-if="payError" class="alert a-error" role="alert" style="margin-top: 20px">
            <Icon name="alert-circle" :size="18" />
            <span>{{ payError }}</span>
          </div>

          <div class="cta-wrap desktop-cta">
            <p class="legal">
              By confirming you agree to our <a href="#">terms</a> and <a href="#">refund policy</a>. You can cancel
              anytime before dispatch.
            </p>
          </div>

          <div class="foot">
            <a href="#">Refund policy</a><a href="#">Shipping</a><a href="#">Privacy policy</a
            ><a href="#">Terms of service</a><a href="#">Contact</a>
          </div>
          <div class="powered-by">
            <span>Powered by</span>
            <span class="pw">Lunar<span class="dot">.</span></span>
          </div>
        </div>
      </div>

      <!-- Right · order summary -->
      <aside class="summary-side" aria-label="Order summary">
        <div class="summary-col">
          <div class="summary-sticky"><OrderSummary /></div>
        </div>
      </aside>
    </div>

    <!-- Desktop · persistent confirm bar -->
    <div class="d-pay-bar">
      <div class="d-pay-inner">
        <div class="d-pay-zone d-pay-zone--form">
          <div class="d-pay-left">
            <div class="d-pay-total">
              <span class="lbl">Total due</span> <span class="mono">{{ totalLabel }}</span>
            </div>
            <p class="d-pay-note" :class="{ 'is-warn': needsReauth }">
              <Icon name="shield-check" :size="14" />
              <template v-if="needsReauth">Hold needs a top-up: <strong>re-open your wallet</strong></template>
              <template v-else>Card authorised, <strong>not charged</strong> until you confirm</template>
            </p>
          </div>
        </div>
        <div class="d-pay-zone d-pay-zone--summary">
          <div class="d-pay-right">
            <button v-if="payHint" type="button" class="d-pay-hint" @click="focusHint">
              <Icon name="arrow-up" :size="15" />{{ payHint }}
            </button>
            <button
              type="button"
              id="d-confirm-btn"
              class="btn btn-primary xc-pay-btn"
              :disabled="!canConfirm || pickupPointRequired || collectUnavailable"
              @click="confirmAndPay"
            >
              <span v-if="confirming" class="spinner"></span>
              <template v-else>
                <Icon name="lock" :size="16" />
                <span class="cta-label"
                  >Confirm &amp; pay <span class="mono">{{ totalLabel }}</span></span
                >
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile · sticky pay bar -->
    <div class="m-pay-bar">
      <button type="button" class="btn btn-primary btn-block xc-pay-btn" :disabled="!canConfirm || pickupPointRequired || collectUnavailable" @click="confirmAndPay">
        <span v-if="confirming" class="spinner"></span>
        <template v-else>
          <Icon name="lock" :size="16" />
          <span class="cta-label"
            >Confirm &amp; pay <span class="mono">{{ totalLabel }}</span></span
          >
        </template>
      </button>
    </div>
  </div>
</template>
