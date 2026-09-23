const p = "/lunar/search/events", c = {
  searchId: "data-lunar-search-id",
  productId: "data-lunar-product-id",
  position: "data-lunar-position",
  source: "data-lunar-source"
}, l = () => {
  var t;
  return typeof document > "u" ? null : ((t = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : t.content) ?? null;
};
function a(t, e = {}) {
  const n = e.endpoint ?? p, r = e.token === void 0 ? l() : e.token, o = new FormData();
  o.append("search_id", String(t.search_id)), o.append("product_id", String(t.product_id)), o.append("position", String(t.position)), o.append("source", t.source ?? "organic");
  const i = t.session_id ?? e.sessionId;
  i && o.append("session_id", i), r && o.append("_token", r), !(typeof navigator < "u" && typeof navigator.sendBeacon == "function" && navigator.sendBeacon(n, o)) && fetch(n, { method: "POST", body: o, keepalive: !0, credentials: "same-origin" }).catch(() => {
  });
}
function u(t, e) {
  var o, i, d, s;
  const n = (o = t.meta) == null ? void 0 : o.search_id, r = (i = e.document) == null ? void 0 : i.id;
  return !n || r === void 0 || r === null ? null : {
    search_id: n,
    product_id: r,
    position: Number(((d = e.meta) == null ? void 0 : d.position) ?? 0),
    source: ((s = e.meta) == null ? void 0 : s.source) ?? "organic"
  };
}
function h(t, e, n = {}) {
  const r = u(t, e);
  r && a(r, n);
}
function m(t, e) {
  const n = u(t, e);
  return n ? {
    [c.searchId]: n.search_id,
    [c.productId]: String(n.product_id),
    [c.position]: String(n.position),
    [c.source]: n.source ?? "organic"
  } : {};
}
function f(t) {
  const e = t.getAttribute(c.searchId), n = t.getAttribute(c.productId);
  return !e || n === null ? null : {
    search_id: e,
    product_id: n,
    position: Number(t.getAttribute(c.position) ?? 0),
    source: t.getAttribute(c.source) ?? "organic"
  };
}
function g(t = {}, e = document) {
  const n = (r) => {
    const o = r.target instanceof Element ? r.target.closest(`[${c.searchId}]`) : null, i = o ? f(o) : null;
    i && a(i, t);
  };
  return e.addEventListener("click", n, !0), () => e.removeEventListener("click", n, !0);
}
const I = g;
export {
  c as ATTRIBUTES,
  p as DEFAULT_ENDPOINT,
  I as attach,
  g as attachSearchTracking,
  u as eventFor,
  f as payloadFromElement,
  a as sendSearchEvent,
  h as trackHit,
  m as trackingAttributes
};
