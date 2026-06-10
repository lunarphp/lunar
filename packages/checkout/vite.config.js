import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

// The checkout app's OWN Vite config (spec 0008 §A). It is a self-contained
// application built with the Laravel Vite plugin, exactly like an addon chunk —
// one mechanism for everything. `npm run build` emits a manifested bundle into
// resources/dist/; that dir ships prebuilt in the composer package and is
// published to the host's public/ (tag: lunar-checkout-assets), where Laravel's
// own Vite class serves it (dev hot file vs build manifest). No bespoke asset
// route, no manifest reader — the framework already does this.
export default defineConfig({
  base: './',
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      publicDirectory: 'resources/dist',
      hotFile: 'resources/dist/hot',
      refresh: false,
      // When developing the checkout app against a consuming site, set
      // CHECKOUT_DEV_HOST to that site's host (e.g. lunar-two.test) so the dev
      // server uses its Herd/Valet TLS cert + host. The consumer's checkout page
      // is https, so the dev server must be https on the same host — otherwise
      // the browser blocks the module as mixed content. Defaults off (http).
      //   CHECKOUT_DEV_HOST=lunar-two.test npm run dev
      detectTls: process.env.CHECKOUT_DEV_HOST || false,
    }),
    vue(),
    tailwindcss(),
  ],
})
