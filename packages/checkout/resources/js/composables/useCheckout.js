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
    shippingId: data.shippingMethods?.[0]?.id ?? null,
    // Server-projected custom elements (spec 0001). Rendered via the frontend
    // component registry; see composables/elements.js.
    elements: data.elements ?? [],
    discount: null, // { code, type, value, label }
    discountError: '',
    addressValid: false,
    processing: false,
    paid: false,
  })

  const validCodes = data.validCodes ?? {}

  const fmt = (minor) => money(minor, state.currency)

  const subtotal = computed(() => state.items.reduce((s, i) => s + i.price * i.qty, 0))
  const itemCount = computed(() => state.items.reduce((s, i) => s + i.qty, 0))
  const shippingMethod = computed(() => state.shippingMethods.find((m) => m.id === state.shippingId))
  const baseShipping = computed(() =>
    state.fulfilment === 'collect' ? 0 : (shippingMethod.value?.price ?? 0),
  )

  const breakdown = computed(() => {
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
    fmt,
    subtotal,
    itemCount,
    shippingMethod,
    baseShipping,
    breakdown,
    totalLabel,
    applyDiscount,
    removeDiscount,
    elementsIn,
    storeElement,
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
