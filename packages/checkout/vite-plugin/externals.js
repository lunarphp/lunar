import * as Vue from 'vue'

// Externalise `vue` to the checkout app's single shared runtime (window.Vue),
// spec 0009 §B/§D. A contributed element/gateway chunk MUST NOT bundle its own
// copy of Vue — it has to share the exact instance the prebuilt checkout app
// booted, so a chunk component can `inject` the same CheckoutProvider
// (useCheckout) as a built-in element.
//
// This rewrites every `import ... from 'vue'` to read `window.Vue`, in BOTH dev
// and build, so the chunk stays a normal ES module (Vite HMR keeps working in
// dev — unlike an IIFE lib build) while shipping zero second Vue at prod.
//
// Same mechanism Statamic uses for its addon SDK (@statamic/cms/vite-plugin);
// see /Volumes/Dev/statamic/packages/cms/src/vite-plugin/externals.js.
export default function lunarCheckoutExternals() {
  const RESOLVED_VIRTUAL_MODULE_ID = '\0lunar-vue-external'
  const vueExports = Object.keys(Vue).filter(
    (key) => key !== 'default' && /^[a-zA-Z_$][a-zA-Z0-9_$]*$/.test(key),
  )

  return {
    name: 'lunar-checkout-externals',
    enforce: 'pre',

    // Dev + SSR: resolve `vue` to a virtual module that re-exports window.Vue,
    // so the dev server serves a chunk that shares the app's runtime.
    resolveId(id) {
      return id === 'vue' ? RESOLVED_VIRTUAL_MODULE_ID : null
    },

    load(id) {
      if (id !== RESOLVED_VIRTUAL_MODULE_ID) {
        return null
      }

      return `
        const Vue = window.Vue;
        export default Vue;
        export const { ${vueExports.join(', ')} } = Vue;
      `
    },

    // Build: rewrite any `import ... from 'vue'` Rollup left in the output
    // (covers default, named and mixed import forms).
    configResolved(resolved) {
      resolved.build.rollupOptions.plugins = resolved.build.rollupOptions.plugins || []
      resolved.build.rollupOptions.plugins.push({
        name: 'lunar-checkout-externals-render',
        renderChunk(code) {
          code = code.replace(
            /import\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*,\s*(\{[^}]+\})\s+from\s+['"]vue['"];?/g,
            'const $1 = window.Vue;\nconst $2 = window.Vue;',
          )

          return code.replace(
            /import\s+(.+?)\s+from\s+['"]vue['"];?/g,
            'const $1 = window.Vue;',
          )
        },
      })
    },
  }
}
