import { markRaw } from 'vue'

// Frontend element-component registry. The server projects each registered
// element with a `component` key (a rendering hint, spec 0003); this maps that
// key to the Vue component that renders it. A consumer registers their custom
// element's component once at app boot:
//
//   import { registerElement } from '@/vendor/lunar-checkout'
//   import LogoUpload from './checkout/LogoUpload.vue'
//   registerElement('logo-upload', LogoUpload)
//
// The map is module-level static config (not per-request state), so it is safe
// to share across the single client app instance.
const registry = new Map()

export function registerElement(key, component) {
  registry.set(key, markRaw(component))
}

export function resolveElement(key) {
  return registry.get(key) ?? null
}

export function hasElement(key) {
  return registry.has(key)
}
