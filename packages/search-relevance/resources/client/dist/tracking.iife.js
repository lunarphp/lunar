var LunarSearchRelevance = (function(exports) {
  "use strict";
  const DEFAULT_ENDPOINT = "/lunar/search/events";
  const ATTRIBUTES = {
    searchId: "data-lunar-search-id",
    productId: "data-lunar-product-id",
    position: "data-lunar-position",
    source: "data-lunar-source"
  };
  const csrfToken = () => {
    var _a;
    return typeof document === "undefined" ? null : ((_a = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : _a.content) ?? null;
  };
  function sendSearchEvent(payload, options = {}) {
    const endpoint = options.endpoint ?? DEFAULT_ENDPOINT;
    const token = options.token === void 0 ? csrfToken() : options.token;
    const data = new FormData();
    data.append("search_id", String(payload.search_id));
    data.append("product_id", String(payload.product_id));
    data.append("position", String(payload.position));
    data.append("source", payload.source ?? "organic");
    const sessionId = payload.session_id ?? options.sessionId;
    if (sessionId) {
      data.append("session_id", sessionId);
    }
    if (token) {
      data.append("_token", token);
    }
    if (typeof navigator !== "undefined" && typeof navigator.sendBeacon === "function" && navigator.sendBeacon(endpoint, data)) {
      return;
    }
    void fetch(endpoint, { method: "POST", body: data, keepalive: true, credentials: "same-origin" }).catch(() => void 0);
  }
  function eventFor(results, hit) {
    var _a, _b, _c, _d;
    const searchId = (_a = results.meta) == null ? void 0 : _a.search_id;
    const productId = (_b = hit.document) == null ? void 0 : _b.id;
    if (!searchId || productId === void 0 || productId === null) {
      return null;
    }
    return {
      search_id: searchId,
      product_id: productId,
      position: Number(((_c = hit.meta) == null ? void 0 : _c.position) ?? 0),
      source: ((_d = hit.meta) == null ? void 0 : _d.source) ?? "organic"
    };
  }
  function trackHit(results, hit, options = {}) {
    const payload = eventFor(results, hit);
    if (payload) {
      sendSearchEvent(payload, options);
    }
  }
  function trackingAttributes(results, hit) {
    const payload = eventFor(results, hit);
    if (!payload) {
      return {};
    }
    return {
      [ATTRIBUTES.searchId]: payload.search_id,
      [ATTRIBUTES.productId]: String(payload.product_id),
      [ATTRIBUTES.position]: String(payload.position),
      [ATTRIBUTES.source]: payload.source ?? "organic"
    };
  }
  function payloadFromElement(element) {
    const searchId = element.getAttribute(ATTRIBUTES.searchId);
    const productId = element.getAttribute(ATTRIBUTES.productId);
    if (!searchId || productId === null) {
      return null;
    }
    return {
      search_id: searchId,
      product_id: productId,
      position: Number(element.getAttribute(ATTRIBUTES.position) ?? 0),
      source: element.getAttribute(ATTRIBUTES.source) ?? "organic"
    };
  }
  function attachSearchTracking(options = {}, root = document) {
    const handler = (event) => {
      const target = event.target instanceof Element ? event.target.closest(`[${ATTRIBUTES.searchId}]`) : null;
      const payload = target ? payloadFromElement(target) : null;
      if (payload) {
        sendSearchEvent(payload, options);
      }
    };
    root.addEventListener("click", handler, true);
    return () => root.removeEventListener("click", handler, true);
  }
  const attach = attachSearchTracking;
  exports.ATTRIBUTES = ATTRIBUTES;
  exports.DEFAULT_ENDPOINT = DEFAULT_ENDPOINT;
  exports.attach = attach;
  exports.attachSearchTracking = attachSearchTracking;
  exports.eventFor = eventFor;
  exports.payloadFromElement = payloadFromElement;
  exports.sendSearchEvent = sendSearchEvent;
  exports.trackHit = trackHit;
  exports.trackingAttributes = trackingAttributes;
  Object.defineProperty(exports, Symbol.toStringTag, { value: "Module" });
  return exports;
})({});
