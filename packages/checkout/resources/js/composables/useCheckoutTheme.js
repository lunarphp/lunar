import { onMounted, watch } from 'vue'

/**
 * Apply a CheckoutTheme token map ({ '--accent': '#…', … }) to the checkout
 * root element via inline custom properties.
 *
 * Setting the properties from JS (not an inline style="" attribute or an
 * injected <style>) keeps a strict style-src CSP happy — no 'unsafe-inline'.
 * Values are already validated/sanitised server-side by CheckoutTheme::tokens().
 *
 * @param {import('vue').Ref<HTMLElement|null>} rootRef
 * @param {() => Record<string, string>} getTokens
 */
export function useCheckoutTheme(rootRef, getTokens) {
  const apply = () => {
    const el = rootRef.value
    if (!el) return
    const tokens = getTokens() || {}
    for (const [name, value] of Object.entries(tokens)) {
      el.style.setProperty(name, value)
    }
  }

  onMounted(apply)
  watch(getTokens, apply, { deep: true })
}
