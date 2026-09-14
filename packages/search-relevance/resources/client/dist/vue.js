import { toValue as o } from "vue";
import { sendSearchEvent as u, trackingAttributes as n, trackHit as i } from "./index.js";
import { ATTRIBUTES as S, DEFAULT_ENDPOINT as T, attach as k, attachSearchTracking as m, eventFor as E, payloadFromElement as v } from "./index.js";
function d(t, r = {}) {
  const a = () => o(t) ?? {};
  return {
    /** Send a click event for a hit. Safe to call when the search was not logged. */
    track: (e) => i(a(), e, r),
    /** The `data-lunar-*` attributes for a hit, for `v-bind`. */
    attrs: (e) => n(a(), e),
    /** The raw sender, for events not tied to a rendered hit. */
    send: (e) => u(e, r)
  };
}
const f = {
  mounted(t, r) {
    c(t, r.value), t.addEventListener("click", () => {
      const a = t.__lunarSearchHit;
      a && i(a.results, a.hit, a.options);
    });
  },
  updated(t, r) {
    c(t, r.value);
  }
};
function c(t, r) {
  t.__lunarSearchHit = r;
  for (const [a, e] of Object.entries(n(r.results, r.hit)))
    t.setAttribute(a, e);
}
export {
  S as ATTRIBUTES,
  T as DEFAULT_ENDPOINT,
  k as attach,
  m as attachSearchTracking,
  E as eventFor,
  v as payloadFromElement,
  u as sendSearchEvent,
  i as trackHit,
  n as trackingAttributes,
  d as useSearchTracking,
  f as vLunarSearchHit
};
