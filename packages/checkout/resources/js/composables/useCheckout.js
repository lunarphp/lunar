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
    discount: null, // { code, type, value, label }
    discountError: '',
    addressValid: Boolean(data.shippingAddress?.postcode),
    processing: false,
    paid: false,
  })

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
    state.addressValid = Boolean(fresh.shippingAddress?.postcode)
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

  function pay() {
    if (state.processing) return
    state.processing = true
    // Cosmetic — real placement gates on the checkout session (spec 0004).
    setTimeout(() => {
      state.processing = false
      state.paid = true
    }, 1400)
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
