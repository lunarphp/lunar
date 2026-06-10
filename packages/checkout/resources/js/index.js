// The `@lunarphp/checkout` SDK surface — the stable contract a contributed
// element/gateway chunk builds against (spec 0009 §A/§D). Chunks import from
// here; the build preset externalises it to the app's single shared instance
// (window.Lunar), so registration and useCheckout() cross the boundary intact.
//
// This is also the entry a publish-and-own consumer imports the built-in
// components and helpers from.

export {
  registerCheckoutElement,
  resolveCheckoutElement,
  hasCheckoutElement,
  // legacy aliases
  registerElement,
  resolveElement,
  hasElement,
} from './composables/elements.js'

export { createCheckout, useCheckout, CHECKOUT_KEY } from './composables/useCheckout.js'
export { useCheckoutTheme } from './composables/useCheckoutTheme.js'
export { money } from './utils/money.js'

// Built-in components (for publish-and-own composition).
export { default as LunarCheckout } from './components/LunarCheckout.vue'
export { default as OrderSummary } from './components/OrderSummary.vue'
export { default as PaymentSection } from './components/PaymentSection.vue'
export { default as DeliverySection } from './components/DeliverySection.vue'
export { default as ShippingMethods } from './components/ShippingMethods.vue'
