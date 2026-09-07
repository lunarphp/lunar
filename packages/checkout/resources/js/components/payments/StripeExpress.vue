<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { postJson, useCheckout } from '../../composables/useCheckout.js'

/**
 * Stripe Express Checkout Element (spec 0012 SC). The sheet quotes rates
 * through the non-persisting quote endpoint mid-sheet; nothing is written
 * to the cart until onConfirm, and the hold is minted deferred (after the
 * writes) so the authorised amount is the final sheet total.
 *
 * Also mounts standalone on host pages outside the checkout bundle (spec
 * 0012 SF, e.g. the cart's ExpressWalletsHost): `urls`/`amount`/`currency`
 * override useCheckout() state when given, and `startUrl` lets the wallet
 * mint its own checkout session lazily, on first interaction, rather than
 * requiring one to already exist.
 */
const props = defineProps({
  method: { type: Object, required: true },
  // Set from the express confirm page's re-open-wallet panel (spec 0012 SD):
  // an existing hold is live, so the intent request must renew it in place
  // rather than mint a second, competing hold.
  renew: { type: Boolean, default: false },
  urls: { type: Object, default: null },
  amount: { type: Number, default: null },
  currency: { type: String, default: null },
  startUrl: { type: String, default: null },
  // Host mode only (spec 0013 §G): the cart's fulfilment mode, which there is
  // no checkout state to read. Without it the sheet assumes delivery, asks
  // for a shipping address and a rate, and writing that rate flips a
  // collecting cart to delivery, dropping the customer's branch.
  fulfilment: { type: String, default: null },
})

// No <LunarCheckout> ancestor on a host page: `optional: true` returns null
// there instead of throwing, and every read below falls back to props.
const checkout = useCheckout({ optional: true })
const state = checkout?.state ?? { elements: [], fulfilment: 'delivery' }
const currency = computed(() => props.currency || state.currency || 'GBP')
const amount = computed(() => props.amount ?? checkout?.breakdown?.value?.total ?? 0)

// Session urls: already present (checkout mode) or minted lazily on first
// wallet interaction against `startUrl` (host mode). `ensureSession()` is a
// no-op once one set of urls exists, so a host page's second click reuses
// the same session rather than minting another.
const sessionUrls = ref(props.urls || state.urls || null)
// The start response projects the cart's own mode (spec 0013 §E), which is
// how a host that mounts the region without passing one still gets a sheet
// that matches the cart.
const startedFulfilment = ref(null)
let mintPromise = null

function ensureSession() {
  if (sessionUrls.value?.confirm) return Promise.resolve(sessionUrls.value)
  if (!props.startUrl) return Promise.resolve(sessionUrls.value ?? {})

  if (!mintPromise) {
    // A transient failure (network blip, momentary 500) must not wedge every
    // later wallet interaction behind the same rejected promise: clear it so
    // the next call mints fresh instead of replaying a dead attempt forever.
    mintPromise = postJson(props.startUrl, {}, 'Could not start checkout.')
      .then((data) => {
        sessionUrls.value = data.urls
        startedFulfilment.value = data.fulfilment ?? null

        return data.urls
      })
      .catch((error) => {
        mintPromise = null
        throw error
      })
  }

  return mintPromise
}

const el = ref(null)
const error = ref('')
const loading = ref(true)

let stripe = null
let elements = null
let expressElement = null
let quotedAddress = null

function loadStripeJs() {
  if (window.Stripe) return Promise.resolve()

  return new Promise((resolve, reject) => {
    const script = document.createElement('script')
    script.src = 'https://js.stripe.com/v3'
    script.onload = resolve
    script.onerror = () => reject(new Error('Could not load express checkout.'))
    document.head.appendChild(script)
  })
}

// The three "back()" checkout writes (contact, shipping address, shipping
// option) never answer JSON, redirect or not, so they can't reuse postJson's
// response.json() parsing. redirect: 'manual' turns a successful back()
// redirect into an opaque response instead of a followed HTML fetch; a
// validation failure still comes back as ordinary 422 JSON because the
// request's Accept header makes Laravel's exception handler render JSON
// regardless of the controller's own redirect.
async function postRedirect(url, body) {
  const xsrf = decodeURIComponent(document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)?.[1] ?? '')

  const response = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    redirect: 'manual',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-XSRF-TOKEN': xsrf,
    },
    body: JSON.stringify(body),
  })

  if (response.type === 'opaqueredirect' || response.ok) {
    return
  }

  const payload = await response.json().catch(() => ({}))
  const message = Object.values(payload.errors ?? {}).flat()[0] ?? payload.message
  throw new Error(message || 'The request could not be completed.')
}

// The host page's own projection first: it is kept current by whatever owns
// the switch, where the start response is a snapshot of the cart as it was
// when the session was minted. That snapshot covers a host that projects the
// methods but not the mode, and the checkout's own state covers checkout mode.
const collectMode = () => (props.fulfilment ?? startedFulfilment.value ?? state.fulfilment) === 'collect'

function splitName(name) {
  const trimmed = (name || '').trim()
  const at = trimmed.lastIndexOf(' ')

  return at === -1 ? { first: trimmed, last: trimmed } : { first: trimmed.slice(0, at), last: trimmed.slice(at + 1) }
}

function addressPayload(name, address, phone) {
  const { first, last } = splitName(name)

  return {
    first_name: first,
    last_name: last,
    line1: address?.line1 || '',
    line2: address?.line2 || undefined,
    city: address?.city || '',
    state: address?.state || undefined,
    postcode: address?.postal_code || '',
    country_code: address?.country || '',
    phone: phone || undefined,
  }
}

// Persist everything the wallet shared, in order: contact (unless already
// signed in), shipping address, billing address, shipping option. Collect
// mode skips both shipping writes (there is no delivery address to store).
// `session` is the (by now minted) session urls: checkout mode's contact
// write goes through the contact ELEMENT's own props (it may already be
// filled in for a signed-in customer); host mode has no such element, so it
// falls back to the plain `contact` url the start action's JSON answer
// includes (spec 0012 SF).
async function persistWalletData(event, session) {
  const contact = state.elements.find((item) => item.handle === 'contact')
  const contactUrl = contact?.props?.contactUrl ?? session.contact
  const email = event.billingDetails?.email

  if (!contact?.props?.signedIn && contactUrl && email) {
    await postRedirect(contactUrl, { email })
  }

  if (!collectMode() && event.shippingAddress) {
    await postRedirect(
      session.shippingAddress,
      addressPayload(event.shippingAddress.name, event.shippingAddress.address, event.billingDetails?.phone),
    )
  }

  await postJson(
    session.billingAddress,
    addressPayload(event.billingDetails?.name, event.billingDetails?.address, event.billingDetails?.phone),
    'Your billing address could not be saved.',
  )

  if (!collectMode() && event.shippingRate?.id) {
    await postRedirect(session.shippingOption, { shipping_option: event.shippingRate.id })
  }
}

onMounted(async () => {
  try {
    const key = props.method.config?.publishableKey

    if (!key) throw new Error('Express checkout is not configured.')

    await loadStripeJs()

    stripe = window.Stripe(key)
    elements = stripe.elements({
      mode: 'payment',
      capture_method: 'manual',
      currency: currency.value.toLowerCase(),
      amount: amount.value,
    })

    expressElement = elements.create('expressCheckout', {
      // One row of wallet buttons across the region's full width; anything
      // beyond four collapses into the element's own overflow menu.
      layout: { maxColumns: 4, maxRows: 1, overflow: 'auto' },
    })
    expressElement.mount(el.value)

    expressElement.on('click', (event) => {
      // The wallet gesture window only tolerates a synchronous resolve, so
      // the session mint (host mode) fires in parallel rather than being
      // awaited here; later handlers await it once they actually need urls.
      ensureSession()

      event.resolve({
        emailRequired: true,
        shippingAddressRequired: !collectMode(),
        shippingRates: collectMode() ? undefined : [{ id: 'pending', displayName: 'Calculating', amount: 0 }],
      })
    })

    expressElement.on('shippingaddresschange', async (event) => {
      try {
        const session = await ensureSession()
        // The shippingratechange event carries only the chosen rate, not the
        // address, so the address is stashed here for the rate quotes that
        // follow it.
        quotedAddress = event.address
        const quote = await postJson(session.quote, {
          postcode: event.address.postal_code,
          country_code: event.address.country,
          city: event.address.city,
        })

        const rates = quote.methods
          .filter((m) => !m.collect)
          .map((m) => ({ id: m.id, displayName: m.name, amount: m.price }))

        if (!rates.length) {
          event.reject()
          return
        }

        elements.update({ amount: quote.totals.total })
        event.resolve({ shippingRates: rates })
      } catch {
        event.reject()
      }
    })

    expressElement.on('shippingratechange', async (event) => {
      try {
        if (!quotedAddress) {
          event.reject()
          return
        }

        const session = await ensureSession()
        const quote = await postJson(session.quote, {
          postcode: quotedAddress.postal_code,
          country_code: quotedAddress.country,
          city: quotedAddress.city,
          shipping_option: event.shippingRate.id,
        })

        elements.update({ amount: quote.totals.total })
        event.resolve()
      } catch {
        event.reject()
      }
    })

    expressElement.on('confirm', async (event) => {
      error.value = ''

      // Once stripe.confirmPayment has been called the confirm event is
      // settled as far as the element is concerned: calling paymentFailed()
      // after that point is an IntegrationError. Track which side of that
      // line a failure lands on.
      let confirming = false

      try {
        const session = await ensureSession()

        // Persist everything the wallet shared, THEN mint the hold so the
        // authorised amount is the final total (spec 0012 SC step 4).
        await persistWalletData(event, session)

        // Deferred-intent contract: elements.submit() finalises the payment
        // details Elements collected and MUST run before the PaymentIntent
        // exists server-side, not after (the opposite order silently
        // confirms against a stale/absent intent).
        const { error: submitError } = await elements.submit()

        if (submitError) {
          event.paymentFailed({ reason: 'fail' })
          error.value = submitError.message || 'The payment was not completed. Try again or pay another way.'
          return
        }

        const { clientSecret } = await postJson(
          session.paymentIntent,
          {
            payment_method: props.method.handle,
            mode: 'hold',
            ...(props.renew ? { renew: true } : {}),
          },
          'Your payment could not be started.',
        )

        confirming = true
        const { error: confirmError } = await stripe.confirmPayment({
          elements,
          clientSecret,
          redirect: 'if_required',
          confirmParams: { return_url: session.confirm },
        })

        if (confirmError) {
          // A declined wallet closes its sheet with no trace of its own, so
          // this message is the only signal the customer gets.
          error.value = confirmError.message || 'The payment was not completed. Try again or pay another way.'
          return
        }

        window.location.assign(session.confirm)
      } catch (e) {
        if (!confirming) {
          event.paymentFailed({ reason: 'fail' })
        }
        error.value = e?.message || 'The payment was not completed. Try again or pay another way.'
      }
    })
  } catch (e) {
    error.value = e?.message || 'Could not load express checkout.'
  } finally {
    loading.value = false
  }
})

onBeforeUnmount(() => expressElement?.destroy())
</script>

<template>
  <div>
    <p v-if="error" class="help" style="color: var(--error-700)">{{ error }}</p>
    <div ref="el"></div>
  </div>
</template>
