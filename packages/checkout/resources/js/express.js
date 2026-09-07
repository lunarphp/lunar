import { createApp, h, reactive } from 'vue'
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

/**
 * Mounts the wallet region on a host page. `fulfilment` is the cart's own
 * mode (spec 0013 §G): there is no <LunarCheckout> ancestor to read it from
 * here, and a wallet that assumes delivery asks for a shipping address and a
 * courier rate, which overwrites a customer's collect choice. The host owns
 * that switch and can flip it after the mount, so the mode rides in a
 * reactive holder and the returned unmount function carries an `update()`
 * the host calls when its own projection changes.
 */
export function mountExpress(el, { methods, startUrl, amount, currency = 'GBP', fulfilment = 'delivery' }) {
  const cart = reactive({ fulfilment })

  const app = createApp({
    render: () => h(ExpressWalletsHost, { methods, startUrl, amount, currency, fulfilment: cart.fulfilment }),
  })
  app.mount(el)

  const unmount = () => app.unmount()

  unmount.update = ({ fulfilment: mode }) => {
    if (mode !== undefined) {
      cart.fulfilment = mode
    }
  }

  return unmount
}
