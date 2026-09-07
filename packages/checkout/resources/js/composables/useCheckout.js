import { computed, inject, provide, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { money } from '../utils/money.js'

export const CHECKOUT_KEY = Symbol('lunar-checkout')

// Shown wherever a collecting cart has no collect option to store, so both the
// pay boundary's refusal and the CTA hint read the same (spec 0013 §F).
export const COLLECT_UNAVAILABLE = 'Collection is not available for this address. Switch to delivery to continue.'

// Plain JSON POST outside Inertia: the pay boundary and gateway calls are
// request/response, not page visits. Module-level (not tied to a checkout
// store) so a component without a <LunarCheckout> ancestor, like the
// host-page express mount (spec 0012 SF), can use it too. `fallbackMessage`
// is caller-supplied: a hardcoded "Payment could not be started." made no
// sense surfacing on the delivery step's address lookup.
export async function postJson(url, body, fallbackMessage = 'The request could not be completed.') {
  const xsrf = decodeURIComponent(document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)?.[1] ?? '')

  const response = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-XSRF-TOKEN': xsrf,
    },
    body: JSON.stringify(body),
  })

  const payload = await response.json().catch(() => ({}))

  if (!response.ok) {
    const message = Object.values(payload.errors ?? {}).flat()[0] ?? payload.message
    throw new Error(message || fallbackMessage)
  }

  return payload
}

/**
 * Instance-scoped checkout store (spec 0003 §F — provide/inject, never a
 * module singleton, so concurrent SSR requests don't bleed). Created once per
 * <LunarCheckout> and injected by the section components.
 *
 * The pricing engine mirrors the Tender v2 prototype's calc(): everything
 * derives from items + fulfilment + shipping + discount. Replaced by the
 * server-driven CheckoutData breakdown once spec 0001/0004 land.
 */
export function createCheckout(data) {
  const state = reactive({
    // Server state (spec 0013 §E): a reload must not fall back to delivery
    // while the cart holds the collect option.
    fulfilment: data.fulfilment ?? 'delivery', // 'delivery' | 'collect'
    pickupPoints: data.pickupPoints ?? [], // [{ id, name, lines }]
    pickupPointId: data.pickupPointId ?? null,
    method: 'card', // card | paypal | clearpay | klarna
    items: data.items ?? [],
    currency: data.currency ?? 'GBP',
    vatRate: data.vatRate ?? 0.2,
    shippingMethods: data.shippingMethods ?? [],
    // Selection is server state (the cart's stored option) — no client-side
    // default; nothing is "chosen" until the cart says so.
    shippingId: data.shippingId ?? null,
    shippingAddress: data.shippingAddress ?? null,
    // The signed-in customer's address book (empty for guests), offered for
    // one-click selection; writes still go through the shipping-address route.
    savedAddresses: data.savedAddresses ?? [],
    // Server-derived money figures (minor units). When present they are the
    // single source of truth for the summary; the client calc below is the
    // prototype fallback for payloads without them.
    totals: data.totals ?? null,
    urls: data.urls ?? {},
    // Server-projected custom elements (spec 0001). Rendered via the frontend
    // component registry; see composables/elements.js.
    elements: data.elements ?? [],
    // Registered payment methods (spec 0002) — empty when the host has
    // enabled no gateway, in which case the payment region renders empty.
    paymentMethods: data.paymentMethods ?? [],
    // The fingerprint of the cart state being shown; echoed to the pay
    // boundary so the server pins exactly what the customer confirmed.
    fingerprint: data.fingerprint ?? null,
    billingSame: true,
    payError: '',
    discount: null, // { code, type, value, label }
    discountError: '',
    addressValid: Boolean(data.shippingAddress?.postcode),
    processing: false,
    paid: false,
  })

  state.method = state.paymentMethods[0]?.handle ?? null

  // Bounced back from the processing page: the gateway reported the charge
  // failed and the session was reopened for another attempt.
  if (new URLSearchParams(window.location.search).get('payment') === 'failed') {
    state.payError =
      'Your payment could not be completed and you have not been charged. Check your payment details and try again.'
    window.history.replaceState({}, '', window.location.pathname)
  }

  // Re-sync from a fresh `checkout` prop after an Inertia partial reload —
  // options, selection and totals are all server-owned.
  function sync(fresh) {
    state.fulfilment = fresh.fulfilment ?? 'delivery'
    state.pickupPoints = fresh.pickupPoints ?? []
    state.pickupPointId = fresh.pickupPointId ?? null
    state.items = fresh.items ?? []
    state.shippingMethods = fresh.shippingMethods ?? []
    state.shippingId = fresh.shippingId ?? null
    state.shippingAddress = fresh.shippingAddress ?? null
    state.savedAddresses = fresh.savedAddresses ?? []
    state.totals = fresh.totals ?? null
    state.urls = fresh.urls ?? {}
    state.elements = fresh.elements ?? []
    state.paymentMethods = fresh.paymentMethods ?? []
    state.fingerprint = fresh.fingerprint ?? null
    state.addressValid = Boolean(fresh.shippingAddress?.postcode)

    if (!state.paymentMethods.some((m) => m.handle === state.method)) {
      state.method = state.paymentMethods[0]?.handle ?? null
    }
  }

  const validCodes = data.validCodes ?? {}

  const fmt = (minor) => money(minor, state.currency)

  const subtotal = computed(() => state.items.reduce((s, i) => s + i.price * i.qty, 0))
  const itemCount = computed(() => state.items.reduce((s, i) => s + i.qty, 0))
  const shippingMethod = computed(() => state.shippingMethods.find((m) => m.id === state.shippingId))
  const baseShipping = computed(() => (state.fulfilment === 'collect' ? 0 : (shippingMethod.value?.price ?? 0)))

  const breakdown = computed(() => {
    // Server totals win outright — shipping, VAT and discounts are cart
    // calculations, not client arithmetic.
    if (state.totals) {
      return {
        subtotal: state.totals.sub_total,
        baseShipping: state.totals.shipping_total,
        shipping: state.totals.shipping_total,
        discGoods: state.totals.discount_total,
        discShip: 0,
        total: state.totals.total,
        vat: state.totals.tax_total,
      }
    }

    const d = state.discount
    let discGoods = 0
    let discShip = 0
    if (d) {
      if (d.type === 'pct') discGoods = Math.round((subtotal.value * d.value) / 100)
      else if (d.type === 'fixed') discGoods = Math.min(d.value, subtotal.value)
      else if (d.type === 'freeship') discShip = baseShipping.value
      else if (d.type === 'shippct') discShip = Math.round((baseShipping.value * d.value) / 100)
    }
    const shipping = Math.max(0, baseShipping.value - discShip)
    const total = Math.max(0, subtotal.value + shipping - discGoods)
    const vat = Math.round((total * state.vatRate) / (1 + state.vatRate))
    return { subtotal: subtotal.value, baseShipping: baseShipping.value, shipping, discGoods, discShip, total, vat }
  })

  const totalLabel = computed(() => fmt(breakdown.value.total))

  function applyDiscount(raw) {
    const code = (raw || '').trim().toUpperCase()
    state.discountError = ''
    if (!code) return
    const found = validCodes[code]
    if (!found) {
      state.discountError = `"${code}" isn't a valid code.`
      return
    }
    state.discount = { code, ...found }
  }

  function removeDiscount() {
    state.discount = null
    state.discountError = ''
  }

  // Registered elements placed in a given layout region ('main' | 'summary').
  function elementsIn(region) {
    return state.elements.filter((el) => el.region === region)
  }

  // Persist an element's captured data to the checkout session via its store
  // route, then reload only the `checkout` prop so element data() round-trips.
  // Returns a promise that settles once the visit finishes (success or
  // error), so callers, namely the pending-write flush below, can await the
  // write actually landing rather than firing and forgetting it.
  function storeElement(element, data, options = {}) {
    return new Promise((resolve) => {
      router.post(element.storeUrl, data, {
        preserveScroll: true,
        preserveState: true,
        only: ['checkout'],
        ...options,
        onFinish: (...args) => {
          options.onFinish?.(...args)
          resolve()
        },
      })
    })
  }

  // Components with their own debounced local writes (e.g. OrderDetails'
  // PO reference / delivery notes) register a flush callback here. `pay()`
  // awaits every registered flush before it posts to the pay boundary, so a
  // customer who types a reference and clicks Pay inside the debounce window
  // doesn't lose it. Returns an unregister function for symmetry, though
  // nothing currently unmounts mid-checkout.
  const pendingWrites = new Set()
  function registerPendingWrite(fn) {
    pendingWrites.add(fn)
    return () => pendingWrites.delete(fn)
  }

  // Each flush is isolated: a synchronous throw or a rejected promise from
  // one registrant is swallowed rather than left to reject Promise.all and
  // block payment on a failure that's unrelated to the charge itself.
  async function flushPendingWrites() {
    await Promise.all(
      Array.from(pendingWrites).map((fn) =>
        Promise.resolve()
          .then(() => fn())
          .catch(() => {}),
      ),
    )
  }

  // Options a courier delivers; collection renders through the fulfilment
  // toggle, not the radio list.
  const deliveryMethods = computed(() => state.shippingMethods.filter((m) => !m.collect))
  const collectOption = computed(() => state.shippingMethods.find((m) => m.collect) ?? null)

  const collectAvailable = computed(() => collectOption.value !== null)
  const pickupPoint = computed(
    () => state.pickupPoints.find((p) => p.id === state.pickupPointId) ?? null,
  )
  // Collect chosen, an address saved, and still no collect option (outside the
  // zone, say): the order cannot be created, and the only thing the customer
  // can do about it is switch to delivery. Say that instead of asking for a
  // branch that would not help. Before an address exists Lunar offers no
  // options at all, so that state is "not yet", never "unavailable".
  const collectUnavailable = computed(
    () => state.fulfilment === 'collect' && state.addressValid && !collectAvailable.value,
  )
  // Several points on offer and none chosen: pay is blocked until one is.
  const pickupPointRequired = computed(
    () =>
      state.fulfilment === 'collect' &&
      !collectUnavailable.value &&
      state.pickupPoints.length > 1 &&
      !state.pickupPointId,
  )

  // Store the delivery address on the cart. Options are address-dependent, so
  // the partial reload re-projects them (and the totals) fresh.
  function storeShippingAddress(payload, options = {}) {
    router.post(state.urls.shippingAddress, payload, {
      preserveScroll: true,
      preserveState: true,
      only: ['checkout'],
      ...options,
    })
  }

  // Select a shipping option. Optimistic highlight, server-confirmed — the
  // reload brings back the cart's stored selection and recalculated totals.
  function selectShipping(id) {
    const previous = state.shippingId
    state.shippingId = id

    router.post(
      state.urls.shippingOption,
      { shipping_option: id },
      {
        preserveScroll: true,
        preserveState: true,
        only: ['checkout'],
        onError: () => {
          state.shippingId = previous
        },
      },
    )
  }

  // The mode is cart state (spec 0013 §B). The server stores or releases
  // the collect option itself, so this no longer posts a shipping option.
  function setFulfilment(mode) {
    if (state.fulfilment === mode) return

    const previous = state.fulfilment
    state.fulfilment = mode

    router.post(
      state.urls.fulfilment,
      { fulfilment: mode },
      {
        preserveScroll: true,
        preserveState: true,
        only: ['checkout'],
        onError: () => {
          state.fulfilment = previous
        },
      },
    )
  }

  function selectPickupPoint(id) {
    const previous = state.pickupPointId
    state.pickupPointId = id

    router.post(
      state.urls.pickupPoint,
      { pickup_point: id },
      {
        preserveScroll: true,
        preserveState: true,
        only: ['checkout'],
        onError: () => {
          state.pickupPointId = previous
        },
      },
    )
  }

  const activePaymentMethod = computed(() => state.paymentMethods.find((m) => m.handle === state.method) ?? null)

  // The active method's component registers how to confirm with the gateway
  // (e.g. stripe.confirmPayment). Null means nothing client-side to confirm.
  let paymentConfirm = null
  function registerPaymentConfirm(fn) {
    paymentConfirm = fn
  }

  // Unpin a session whose gateway confirmation failed. The server reopens it
  // only after the gateway confirms no money was captured; a captured charge
  // completes the order instead, which lands here as `completed`.
  async function releasePaymentPin() {
    try {
      const result = await postJson(state.urls.paymentRelease, {}, 'The payment could not be completed.')

      if (result.outcome === 'completed') {
        window.location.reload()

        return
      }

      if (result.fingerprint) {
        state.fingerprint = result.fingerprint
      }
    } catch {
      // Leave it frozen: the reconciliation sweep owns unconfirmable outcomes.
    }
  }

  async function pay() {
    if (state.processing || !state.addressValid || !activePaymentMethod.value) return

    if (collectUnavailable.value) {
      state.payError = COLLECT_UNAVAILABLE
      return
    }

    if (pickupPointRequired.value) {
      state.payError = 'Choose where you would like to collect your order.'
      return
    }

    state.processing = true
    state.payError = ''

    try {
      // Let any debounced element write in flight (or still pending) land
      // before we pin the fingerprint and post to the pay boundary.
      await flushPendingWrites()

      // Billing defaults to the delivery address until a billing element
      // captures its own.
      if (state.billingSame && state.shippingAddress) {
        const a = state.shippingAddress
        const billing = await postJson(
          state.urls.billingAddress,
          {
            first_name: a.firstName,
            last_name: a.lastName,
            company_name: a.companyName,
            line1: a.line1,
            line2: a.line2,
            city: a.city,
            state: a.state,
            postcode: a.postcode,
            country_code: a.countryCode,
            phone: a.phone,
          },
          'Your billing address could not be saved.',
        )

        // The billing address is a fingerprint input, so this write just
        // changed the live fingerprint. Pin against the one the server
        // computed after the write, or the pay boundary rejects the stale
        // page-load fingerprint on every first pay.
        if (billing?.fingerprint) {
          state.fingerprint = billing.fingerprint
        }
      }

      // Pin the session against exactly what the customer confirmed. A method
      // needing no gateway confirmation (offline / pay-on-collection) — or a
      // zero total — completes server-side right here instead.
      const result = await postJson(
        state.urls.pay,
        {
          fingerprint: state.fingerprint,
          payment_method: state.method,
        },
        'Payment could not be started.',
      )

      if (result.completed) {
        state.paid = true
        window.location.reload()

        return
      }

      // Hand over to the gateway component (3DS, wallet sheets…). A failed
      // or abandoned confirmation leaves the session pinned server-side, so
      // release it before surfacing the error — otherwise every retry 409s
      // against a frozen session until the reconciliation sweep runs.
      if (paymentConfirm) {
        try {
          await paymentConfirm()
        } catch (confirmError) {
          await releasePaymentPin()
          throw confirmError
        }
      }

      // The gateway accepted the confirmation. Hand off to the processing
      // page, which settles the session against the gateway's ACTUAL outcome
      // server-side and forwards to the store's success URL — never trust
      // this client-side signal alone, and never leave the customer parked on
      // the checkout watching a spinner that depends on a webhook arriving.
      state.paid = true
      window.location.assign(state.urls.processing)
    } catch (error) {
      state.payError = error?.message || 'Payment failed — you have not been charged.'
      state.paid = false
    } finally {
      state.processing = false
    }
  }

  const store = {
    state,
    sync,
    fmt,
    subtotal,
    itemCount,
    shippingMethod,
    deliveryMethods,
    collectOption,
    collectAvailable,
    collectUnavailable,
    pickupPoint,
    pickupPointRequired,
    selectPickupPoint,
    baseShipping,
    breakdown,
    totalLabel,
    applyDiscount,
    removeDiscount,
    elementsIn,
    storeElement,
    storeShippingAddress,
    selectShipping,
    setFulfilment,
    activePaymentMethod,
    registerPaymentConfirm,
    registerPendingWrite,
    // Exposed so a page that posts to the pay boundary itself (the express
    // confirm squeeze page, spec 0012 SD) can await the same in-flight
    // element writes `pay()` does below, rather than duplicating the set.
    flushPendingWrites,
    postJson,
    pay,
  }

  provide(CHECKOUT_KEY, store)
  return store
}

// `optional: true` returns null instead of throwing when there is no
// <LunarCheckout> ancestor: the host-page express mount (spec 0012 SF) reads
// this to fall back to its own props rather than requiring the full checkout
// provider.
export function useCheckout({ optional = false } = {}) {
  const store = inject(CHECKOUT_KEY, null)

  if (!store && !optional) {
    throw new Error('useCheckout() must be used inside <LunarCheckout>')
  }

  return store
}
