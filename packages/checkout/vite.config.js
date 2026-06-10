import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// The checkout app's OWN Vite config (spec 0008 §A) — it is a self-contained
// application, not wired into any consumer's bundler. `npm run build` emits a
// fingerprinted, manifested bundle into dist/, which ships prebuilt in the
// composer package and is streamed same-origin by the package's build route.
export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      // Relative input → manifest key 'resources/js/app.js' (read by CheckoutBundle).
      input: 'resources/js/app.js',
    },
  },
})
