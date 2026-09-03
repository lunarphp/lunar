import { createApp, h } from 'vue'
import { registerElement } from './composables/elements.js'
import ExpressWalletsHost from './components/ExpressWalletsHost.vue'
import StripeExpress from './components/payments/StripeExpress.vue'

// Standalone express mount for host pages outside the checkout bundle
// (spec 0012 SF). The host passes the server-side Express::projection()
// output, the checkout start URL and the cart total; session minting stays
// lazy (first wallet interaction, see StripeExpress's ensureSession()).
//
// This is its own Vite entry/bundle: a separate page load to the checkout
// app's own app.js, so components register into a registry instance of
// their own rather than sharing one with a running checkout app.
registerElement('stripe-express', StripeExpress)

export function mountExpress(el, { methods, startUrl, amount, currency = 'GBP' }) {
  const app = createApp({
    render: () => h(ExpressWalletsHost, { methods, startUrl, amount, currency }),
  })
  app.mount(el)
  return () => app.unmount()
}
