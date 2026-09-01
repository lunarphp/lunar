# 0003 — Checkout Elements: transport projections

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-05
- TODO item: "Checkout Elements — REST + Inertia projections of the element DTO"

## Problem

The core element model ([[0001-core-element-model]]) produces a pure `CheckoutData` DTO. Multiple
clients consume it without leaking into core or duplicating serialization: the headless REST API
(`draft-storefront-api.md`), and the **self-contained Inertia checkout app** `lunar/checkout` —
a Lunar-owned Inertia/Vue application that ships prebuilt and serves its own route ([[0008-checkout-ui-and-theming]]);
the consumer's storefront (any stack) links or redirects to it. The future Lunar-hosted checkout
([[0005-hosted-checkout]]) is the **same app** under a Lunar-operated deployment. The prototype made
an Inertia prop "the contract", which forced the REST layer to re-wrap or re-serialize. This spec
defines the REST and Inertia projections plus the shared transport concerns (cart/session identity,
validation/error rendering, sync, render, a11y); the hosted deployment is its own spec but renders
the same DTO through the same app.

## Proposal

`CheckoutData` is the single contract. Each transport is a thin projection; generated TS types
come off the DTO so every client shares them.

### A. REST projection — `lunar/api`

A `CheckoutResource` projects `CheckoutData` into the API envelope (`{ data, meta, links }`,
`meta.channel`/`currency`), backing the draft API's checkout surface — reframed around the
**checkout session** ([[0004-checkout-session]]):

- `POST /checkout` — **create a `CheckoutSession`** from the current cart (snapshots + pins) via
  the active **checkout driver's** `createSession` ([[0004-checkout-session]]); returns the session
  uuid + `CheckoutData`. The endpoint is driver-neutral — the source cart need not be a Lunar cart.
- `GET /checkout/{uuid}` — read the session's `CheckoutData`. The payload is the amended
  top-level DTO shape ([[0001-core-element-model]] §J): the session `uuid`, its `status`, the
  **live** summary (`subTotal`/`total`/`currencyCode`), the current **confirmation token** (the
  rendered fingerprint), and the `hasDrifted` flag — all computed through the driver on the fly.
  A render never persists anything — a read never writes the session row; divergence is
  **surfaced** via `hasDrifted`, not acted on ([[0010-cart-session-reconciliation]] §D).
- `POST /checkout/{uuid}/contact` — guest email/phone, mapped to the checkout driver's
  `storeContact` verb ([[0010-cart-session-reconciliation]] §B). Contact is not an address
  write ([[0001-core-element-model]] §D).
- `POST /checkout/{uuid}/shipping-address`, `.../billing-address`, `.../shipping-option`,
  `.../coupons` — the **coarse cart writes** each `CapturesData::store()` maps to, routed through
  the active checkout driver's store verbs (which re-sync the session,
  [[0010-cart-session-reconciliation]] §B). The element store is not a generic server RPC; it is
  these named sub-resource writes.
- `POST /checkout/{uuid}/elements/{handle}` — the **element-bag write** for custom price-neutral
  elements ([[0010-cart-session-reconciliation]] §C): resolves the element by `{handle}`,
  validates the payload against the element's `rules()` (Gate 1), and stores via the context's
  `putElementData($handle, $data)` ([[0001-core-element-model]] §C). This is the REST path for
  elements that map to no coarse cart write; the bag is session-backed, so it exists only on the
  session flow.
- `POST /checkout/{uuid}/payment-intent` — delegates to `Payments::driver(...)` for gateways that
  need a pre-confirmation intent ([[0002-payment-methods-and-driver]]); the intent's amount is
  advisory until the pay gate re-pins it ([[0010-cart-session-reconciliation]] §E).
- `POST /checkout/{uuid}/pay` — the **asynchronous pay boundary**, for methods where
  `requiresIntent()` is true ([[0002-payment-methods-and-driver]] §A). The client calls this
  **before** confirming with the gateway SDK. The request carries the **confirmation token** (the
  fingerprint the client rendered); the server runs `assertReadyForPayment` — including step 0:
  the live currency/channel must equal the pinned values
  ([[0010-cart-session-reconciliation]] §E) — pins the amount + fingerprint, transitions the
  session `Open → PaymentProcessing`, re-pins the gateway intent to the pinned amount, and
  returns a "confirm now" response carrying the intent's client-secret reference. Only then does
  the client confirm with the gateway. A stale token returns `409` per §H.
- `POST /checkout/{uuid}/complete` — placement. On the **synchronous** path (no intent —
  `requiresIntent()` false, or zero-total) the request carries the confirmation token and the
  same pay-boundary gate (`assertReadyForPayment` + pin, [[0010-cart-session-reconciliation]] §E)
  runs **inside `complete()`**, in the order-creation transaction — the gate language here is
  scoped to the synchronous path; on the **asynchronous** path the gate and pin already ran at
  `POST .../pay`. Either way the checkout driver's `complete()` runs Gate 2, re-verifies the
  pinned fingerprint at the completion boundary ([[0010-cart-session-reconciliation]] §E.2),
  transitions the session `→ Completed`, and (default Lunar driver) creates the order at
  `AwaitingPayment`. A non-Lunar driver creates its own order. A stale token returns `409`
  per §H.

Context (`StorefrontSession`) resolves from `X-Lunar-Channel`/`X-Lunar-Currency` headers via the
API's `ResolveStorefrontSession` middleware; auth is the API's `public`/`protected` lists
(Sanctum default) with `throttle:api`. Validation failures render as JSON:API `errors[]`.

### B. Inertia render — the self-contained `lunar/checkout` app

This is the self-contained app: the developer `composer require`s it and it works at its own route
with no publish and no build ([[0008-checkout-ui-and-theming]] §B). The developer registers their
elements in a service provider (`Checkout::add(...)`, [[0001-core-element-model]] §G); the
prebuilt app renders `<LunarCheckout/>` on its own page. Inertia/Vue/Vite are internal to the app —
the consumer's storefront needs none of them. The render machinery below ships in `lunar/checkout`
and is owned by Lunar end-to-end.

- The `checkout` Inertia prop carries `CheckoutData`, **page-scoped** (not a global middleware
  share), `Cache-Control: no-store` (it holds addresses/email/phone and any intent secret).
- `POST /checkout/elements {handle, ...data}` is an **Inertia-only convenience multiplexer**: it
  resolves the element, runs Gate 1, and dispatches to the matching coarse cart write **or**, for
  bag-backed elements, the element-bag write (`putElementData`) — the same writes the REST
  projection exposes (§A) — then returns the recomputed `CheckoutData` in the **same response**
  (no second `router.reload`). The response is **scoped** to the stored element plus any element whose
  `enabled()`/`visible()` flipped; deferred props (intent secret, shipping options, country list)
  are not re-sent.
- Validation failures render to the Inertia error bag.
- The Pay action submits the **confirmation token** rendered with the page. On the asynchronous
  path it posts to the **same `POST /checkout/{uuid}/pay` route** as the REST projection (§A) —
  gate, pin, `Open → PaymentProcessing`, intent re-pin — **before** confirming with the gateway
  SDK; the synchronous path goes straight to `/complete`. A `409` (changed-since-confirmed)
  re-renders the new total + a fresh token (§H).
- CSRF applies via Inertia's XHR client (same-origin web session). This clause is Inertia-only.

### C. Cart identity — shared, via `CartSession`

Two identifiers, by stage:

- **Pre-checkout cart** (browsing/basket) — resolved through `Lunar\Core\Contracts\CartSession`,
  never a request-supplied id: REST uses an opaque signed `X-Lunar-Cart` token + `ResolveCart`
  middleware → `CartSession::use(...)`, associated on login via `CartSession::associate(...)`;
  Inertia uses the same `CartSession` with a cookie/same-origin variant. Cookies are not the
  default (native/cross-origin clients).
- **In-checkout** — once `POST /checkout` creates a session, the **session `uuid`** is the
  addressable capability token ([[0004-checkout-session]] §F); the session's source cart is
  internal (an opaque `cart_reference` resolved only by the driver,
  [[0010-cart-session-reconciliation]] §A) and never addressed directly by the client. This is what makes the hosted page
  ([[0005-hosted-checkout]]) shareable by URL and guest-resumable.

The core never resolves a cart or session from an enumerable/request-supplied integer id; the
session uuid supersedes the `public_id` need for the checkout stage.

### D. Rendering hints vs contract

`component()` and the `CheckoutLayout` (region → ordered handles) are **rendering hints** in the
DTO — load-bearing for the Inertia/Vue registry, ignorable by a headless JSON client (a native
app or SSG has no Vue registry; `component: "stripe-card"` is advisory). The contract is the
element data, completeness, validation, and dependencies — meaningful to any client.

### E. Inertia render — hybrid registry (Sub-spec C detail)

- `registerCheckoutElement(key, Component)` maps a `component()` key → Vue component; built-in
  keys registered by default. `<LunarCheckout>` renders from `CheckoutLayout`, looping each
  region's handles into registry-mapped components, wrapping `Main` elements in a section shell.
- Elements are `v-if`-guarded on presence (an element flipping `visible()=false` or dropping
  unmounts cleanly); `element` is typed `CheckoutElementData | undefined` and bodies treat it as
  optional. An unknown `component` key renders a visible dev fallback + console error; a CI check
  diffs backend keys against registered Vue keys (this check lives in `lunar/checkout`).
- **Hybrid escape hatch:** per-region and per-handle named slots; a dev can override a registry
  key or hand-place components. This lives in the **publish-and-own** tier ([[0008-checkout-ui-and-theming]] §E):
  the dev publishes the app source, edits the registry/slots, and rebuilds — there is no
  "swap a component in your own storefront's Vite", since the app is self-contained.
  `CheckoutLayout` stays the single source of placement truth.

### F. Frontend state & sync (Sub-spec C detail)

- **Instance-scoped state** via a `CheckoutProvider` (`provide/inject`) scoped to the
  `<LunarCheckout>` subtree — form map, mounted map, active payment method, submit status. **No
  module-level singletons** (they bleed across concurrent SSR requests — a PII leak). `usePage()`
  is never called at module scope; state clears on unmount / cart change.
- Stores are **debounced**; server values **merge** into the form without clobbering
  dirty/focused fields (no blanket `reset()` mid-typing). Optimistic tick/completion with rollback
  on validation error.
- The active payment method exposes a typed `{ submit(): Promise<void>; ready: Ref<boolean> }`
  via `defineExpose`, registering into the `CheckoutProvider` on mount / deregistering on unmount.
  The Pay action stays disabled (with an `aria-describedby` reason) until the active method is
  `ready`. Express wallets lazy-load their SDKs and hide buttons that fail eligibility.
- Deferred props (intent secret, country list, shipping options) render with animated skeletons.

### G. Accessibility

`aria-live="polite"` announces section enable/complete transitions; an assertive region
announces store errors; the disabled Pay action carries a reason via `aria-describedby`; focus
moves to the first newly-enabled/errored field after a reload; completion is not icon-only.

### H. Validation/error rendering is per-adapter — one status table

`validate()`/`rules()` produce a transport-neutral `ValidationException` in core
([[0001-core-element-model]] §H). Rendering is the adapter's job: Inertia error bag in
`lunar/checkout`, JSON:API `errors[]` (`source.pointer`/`parameter`) in `lunar/api`. The table
below is the **single normative status mapping** for both adapters, matching
[[0004-checkout-session]] §F; the rest of this spec (and the other checkout specs)
cross-reference it rather than restating codes:

| Status | Condition |
|--------|-----------|
| `409` | Confirmation-token mismatch at the pay/complete boundary; a write rejected by the freeze (session in `PaymentProcessing`); a sibling-session conflict on create — including the post-stall case, with the `stalled` reason code ([[0010-cart-session-reconciliation]] §F) |
| `410` | Expired session |
| `404` | Unknown session uuid |
| `422` | Gate-1 element validation failure (invalid incoming data) |

Every `409` carries a machine-readable `reason` code plus — where applicable — the re-synced
`CheckoutData` and a fresh confirmation token ([[0010-cart-session-reconciliation]] §E), so the
client re-renders and retries without a manual refresh.

## Alternatives considered

- **Inertia prop as the contract.** Rejected: forces the REST layer to re-wrap/re-serialize and
  drifts — the very thing the pure DTO prevents.
- **Per-element RPC over the wire for REST too.** Rejected: a headless client can't discover
  server-registered handles; the REST surface stays resource-shaped (coarse cart writes), and
  the element is the client-side grouping.
- **Bespoke session/cookie cart resolution.** Rejected: `X-Lunar-Cart` + `CartSession` already
  exists and works cross-origin; the cookie path is a same-origin swap, not a second implementation.

## Migration impact

- `lunar/api`: a `CheckoutResource` + controllers (net-additive; feeds the draft API's checkout
  endpoints with the element DTO and the payment-intent integrity detail). No core dependency on
  Inertia.
- `lunar/checkout` (the self-contained app): the `checkout` prop adapter, `/checkout/elements`
  multiplexer, Vue registry, `<LunarCheckout>`, `CheckoutProvider`, composables, built-in element
  components, the registration facade — plus the app's own Inertia boot, Vite config, and prebuilt
  `dist/` ([[0008-checkout-ui-and-theming]] §A). Replaces the prototype's module-singleton composables.
- Depends on the planned `public_id` (ULID) for the REST cart token; until it lands, cart identity
  stays session-resolved server-side.
- Generated TS types come off `CheckoutData`; the key-drift CI check lives in `lunar/checkout`.

## Acceptance checks

- One `CheckoutData` serves a REST `GET /checkout/{uuid}` response and an Inertia `checkout` prop;
  generated TS types match the DTO.
- A REST consumer completes contact → address → shipping → payment via `POST /checkout` (create
  session) → the `/checkout/{uuid}/contact` + coarse `/checkout/{uuid}/*` writes → (async path)
  `POST /checkout/{uuid}/pay` → gateway confirm → `POST /checkout/{uuid}/complete`, no Inertia
  involved; an Inertia consumer completes the same via the `/checkout/elements` multiplexer.
- A bag-backed custom element stores via `POST /checkout/{uuid}/elements/{handle}` (REST) and the
  multiplexer (Inertia); the write validates via the element's `rules()` and lands in the session
  element bag.
- Cart identity resolves only through `CartSession`; a request-supplied cart id is never trusted.
- Frontend: no cross-instance state bleed under SSR; an element disappearing unmounts without a
  crash; in-flight keystrokes survive a partial reload; the Pay action gates on active-method
  `ready`; unknown `component` keys render a dev fallback.
- A11y checks for the disabled Pay reason, live-region announcements, and focus management.

## Open questions

- Cart token format (signed blob vs opaque row) — defer to the API spec; `X-Lunar-Cart` is
  identical either way.
- Whether `CheckoutLayout` is fully backend-derived or partially authored in the frontend for the
  composition escape hatch. (Owner: Sub-spec C design.)
- Per-store scoped-reload granularity (which flipped elements to return) — measure before tuning.

## References

- [[0000-overview]], [[0001-core-element-model]], [[0002-payment-methods-and-driver]]
- `draft-storefront-api.md` — envelope, query grammar, `X-Lunar-Cart`/`ResolveCart`,
  `ResolveStorefrontSession`, Sanctum, `POST /checkout` + `POST /checkout/payment-intent`.
- `Lunar\Core\Contracts\CartSession`, `StorefrontSession`; the `Payments` facade.
- Inertia deferred props / partial reloads; the self-contained `lunar/checkout` Inertia app
  ([[0008-checkout-ui-and-theming]]).
