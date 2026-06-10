import vue from '@vitejs/plugin-vue'
import lunarCheckoutExternals from './externals.js'

// The Lunar checkout element build preset (spec 0009 §D) — the Statamic
// `statamic()`-plugin analogue. A storefront/addon that ships a checkout
// element or payment-gateway chunk drops this into its Vite config; it wires
// the Vue plugin and externalises `vue` to the checkout app's shared runtime
// (window.Vue) so the chunk bundles no second copy of Vue.
//
// The consumer still adds its own `laravel()` plugin (for the manifest + dev
// hot file that drives HMR) and, if it uses Tailwind, `@tailwindcss/vite` —
// exactly as a Statamic addon adds `laravel()` alongside `statamic()`. Keeping
// those in the consumer's config (rather than here) lets each storefront own
// its build stack and CSS pipeline.
//
//   import { defineConfig } from 'vite'
//   import laravel from 'laravel-vite-plugin'
//   import tailwindcss from '@tailwindcss/vite'
//   import checkoutElement from '<vendored>/lunarphp/checkout/vite-plugin'
//
//   export default defineConfig({
//     plugins: [
//       laravel({ input: ['resources/js/checkout-elements/logo-upload.js'], ... }),
//       ...checkoutElement(),
//       tailwindcss(),
//     ],
//   })
export default function checkoutElement(options = {}) {
  return [lunarCheckoutExternals(), vue(options.vue || {})]
}

export { lunarCheckoutExternals }
