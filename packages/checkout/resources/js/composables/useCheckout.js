import { computed, inject, provide, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { money } from '../utils/money.js'

export const CHECKOUT_KEY = Symbol('lunar-checkout')

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
    fulfilment: 'delivery', // 'delivery' | 'collect'
    method: 'card', // card | paypal | clearpay | klarna
    items: data.items ?? [],
    currency: data.currency ?? 'GBP',
    vatRate: data.vatRate ?? 0.2,
    shippingMethods: data.shippingMethods ?? [],
    // Selection is server state (the cart's stored option) — no client-side
    // default; nothing is "chosen" until the cart says so.
    shippingId: data.shippingId ?? null,
    shippingAddress: data.shippingAddress ?? null,
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

  // Re-sync from a fresh `checkout` prop after an Inertia partial reload —
  // options, selection and totals are all server-owned.
  function sync(fresh) {
    state.items = fresh.items ?? []
    state.shippingMethods = fresh.shippingMethods ?? []
    state.shippingId = fresh.shippingId ?? null
    state.shippingAddress = fresh.shippingAddress ?? null
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
  const baseShipping = computed(() =>
    state.fulfilment === 'collect' ? 0 : (shippingMethod.value?.price ?? 0),
  )

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
  function storeElement(element, data, options = {}) {
    router.post(element.storeUrl, data, {
      preserveScroll: true,
      preserveState: true,
      only: ['checkout'],
      ...options,
    })
  }

  // Options a courier delivers; collection renders through the fulfilment
  // toggle, not the radio list.
  const deliveryMethods = computed(() => state.shippingMethods.filter((m) => !m.collect))
  const collectOption = computed(() => state.shippingMethods.find((m) => m.collect) ?? null)

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

    router.post(state.urls.shippingOption, { shipping_option: id }, {
      preserveScroll: true,
      preserveState: true,
      only: ['checkout'],
      onError: () => {
        state.shippingId = previous
      },
    })
  }

  // Switching to click & collect selects the collect-flagged option so the
  // cart carries a real zero-charge shipping choice, not a UI-only mode.
  function setFulfilment(mode) {
    state.fulfilment = mode

    if (mode === 'collect' && collectOption.value && state.shippingId !== collectOption.value.id) {
      selectShipping(collectOption.value.id)
    }
  }

  const activePaymentMethod = computed(
    () => state.paymentMethods.find((m) => m.handle === state.method) ?? null,
  )

  // The active method's component registers how to confirm with the gateway
  // (e.g. stripe.confirmPayment). Null means nothing client-side to confirm.
  let paymentConfirm = null
  function registerPaymentConfirm(fn) {
    paymentConfirm = fn
  }

  // Plain JSON POST outside Inertia — the pay boundary and gateway calls are
  // request/response, not page visits.
  async function postJson(url, body) {
    const xsrf = decodeURIComponent(
      document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
    )

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
      throw new Error(message || 'Payment could not be started.')
    }

    return payload
  }

  // Poll the session URL until the server moves it somewhere terminal (the
  // Completed redirect) — completion arrives via the gateway's webhook.
  async function awaitCompletion() {
    for (let attempt = 0; attempt < 20; attempt++) {
      const response = await fetch(window.location.href, {
        credentials: 'same-origin',
        redirect: 'follow',
        headers: { Accept: 'text/html' },
      })

      if (response.url && response.url !== window.location.href) {
        window.location.assign(response.url)
        return
      }

      await new Promise((resolve) => setTimeout(resolve, 1500))
    }
  }

  async function pay() {
    if (state.processing || !state.addressValid || !activePaymentMethod.value) return

    state.processing = true
    state.payError = ''

    try {
      // Billing defaults to the delivery address until a billing element
      // captures its own.
      if (state.billingSame && state.shippingAddress) {
        const a = state.shippingAddress
        await postJson(state.urls.billingAddress, {
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
        })
      }

      // Pin the session against exactly what the customer confirmed.
      await postJson(state.urls.pay, { fingerprint: state.fingerprint })

      // Hand over to the gateway component (3DS, wallet sheets…).
      if (paymentConfirm) {
        await paymentConfirm()
      }

      state.paid = true
      await awaitCompletion()
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
    postJson,
    pay,
  }

  provide(CHECKOUT_KEY, store)
  return store
}

export function useCheckout() {
  const store = inject(CHECKOUT_KEY)
  if (!store) throw new Error('useCheckout() must be used inside <LunarCheckout>')
  return store
}
