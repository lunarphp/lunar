import { markRaw, reactive } from 'vue'

// Frontend element-component registry (spec 0003 §E, promoted to a public,
// runtime-stable contract by spec 0009 §A). The server projects each element
// with a `component` key (a rendering hint); this maps that key to the Vue
// component that renders it.
//
// Built-in keys are registered at app boot. A contributed chunk (a gateway or a
// bespoke element) self-registers its component at RUNTIME:
//
//   import { registerCheckoutElement } from '@lunarphp/checkout'
//   import StripeCard from './StripeCard.vue'
//   registerCheckoutElement('stripe-card', StripeCard)
//
// The map is reactive so a chunk that loads AFTER first render re-renders its
// element in place (an unknown key shows the dev fallback until then — never a
// crash). It is single-app-instance config, not per-request state.
const registry = reactive(new Map())

export function registerCheckoutElement(key, component) {
  registry.set(key, markRaw(component))
}

export function resolveCheckoutElement(key) {
  return registry.get(key) ?? null
}

export function hasCheckoutElement(key) {
  return registry.has(key)
}

// Aliases retained for the built-in components' existing imports.
export {
  registerCheckoutElement as registerElement,
  resolveCheckoutElement as resolveElement,
  hasCheckoutElement as hasElement,
}
