// The self-contained checkout app's client entry (spec 0008 §A).
//
// This boots the OWN Inertia/Vue app, then exposes a single shared Vue runtime
// and the checkout SDK as globals so contributed element/gateway chunks can
// self-register WITHOUT bundling their own Vue (spec 0009 §B). One Vue runtime
// is load-bearing: it is what lets a gateway component `inject` the same
// CheckoutProvider (useCheckout) as a built-in element.

import * as Vue from 'vue'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import {
  registerCheckoutElement,
  resolveCheckoutElement,
  hasCheckoutElement,
} from './composables/elements.js'
import { useCheckout } from './composables/useCheckout.js'
import ContactSection from './components/ContactSection.vue'
import StripeCard from './components/payments/StripeCard.vue'
import '../css/checkout.css'

// Built-in element components (spec 0009 §A). Registered before boot so the
// server's component hints resolve on first render; a consumer swaps one by
// registering a different element (server) + component (here) for the region.
registerCheckoutElement('contact-information', ContactSection)
// First-party gateway components are prebuilt into the app (spec 0002 §C);
// third-party gateways self-register via a contributed runtime chunk.
registerCheckoutElement('stripe-card', StripeCard)

// --- The shared runtime contributed chunks build against -----------------------
//
// Contributed chunks are built with `vue` and `@lunarphp/checkout` EXTERNALISED;
// the build preset rewrites those imports to `window.Vue` / `window.Lunar`, so
// the whole checkout shares exactly one Vue runtime and one SDK. (Statamic uses
// the same window.Vue-externals approach; it is simpler and more robust than an
// import map — see spec 0009 §B, which is being reconciled to this mechanism.)

let booted = false
const bootingCallbacks = []

window.Vue = Vue
window.Lunar = Object.assign(window.Lunar ?? {}, {
  registerCheckoutElement,
  resolveCheckoutElement,
  hasCheckoutElement,
  useCheckout,
  // Run a callback once the app has booted (or immediately if it already has).
  booting(callback) {
    booted ? callback(window.Lunar) : bootingCallbacks.push(callback)
  },
})

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./pages/**/*.vue', { eager: true })
    return pages[`./pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
    app.use(plugin)

    bootingCallbacks.forEach((callback) => callback(window.Lunar))
    booted = true

    app.mount(el)
  },
})
