# Checkout Elements — review overview

A reading guide and summary of the checkout spec set (`0000`–`0010`), for review. Each spec stands
on its own and lands independently; this file is the map. For the normative umbrella (problem framing,
cross-cutting decisions, package map) read **[0000-overview](0000-overview.md)** — this digest
summarises *what each spec decides* and *what to focus a review on*.

## The one decision everything hangs on

**`lunar/checkout` is a self-contained, Lunar-owned Inertia + Vue application that ships prebuilt and
serves its own route.** It is **not** embedded into a consumer's frontend build. Any storefront stack
(React, Blade, Next, Astro, plain HTML, or no JS) links/redirects to it; Inertia/Vue/Vite are internal
implementation detail. This is the Cashier/Telescope/Jetstream pattern: working prebuilt defaults out of
the box, publish-to-own for customisation, and — the piece that makes it extensible — **runtime
contribution** of third-party element/gateway UI without a rebuild.

Three delivery/customisation tiers:

1. **Theme** — rebind `CheckoutTheme` (tokens). No build.
2. **Add/replace an element or gateway** — ship a prebuilt ESM chunk that self-registers at runtime over
   a shared Vue + SDK. No fork, no consumer rebuild. (Spec 0009.)
3. **Publish & own** — publish the app source, disable the package route, edit, run the app's own build.

## Package map

| Package | Role |
|---|---|
| `lunar/checkout` | Everything checkout: the transport-agnostic element model, `CheckoutData` DTO, `CheckoutSession` + the swappable `CheckoutDriver` (default `LunarCheckoutDriver`), `PaymentMethod` registry — **and** the self-contained Inertia app: `<LunarCheckout>`, registry, `CheckoutProvider`, composables, built-in elements, the `CheckoutAssets` extension seam, prebuilt `dist/`. (A transport-free `checkout-core` split is an open question — 0000.) |
| `lunar/core` | The seams checkout rides: `Cart::set*`, `ShippingManifest`, `Payments`/`AbstractPayment` — plus the `SupportsPaymentIntents` capability contract (0010). No checkout code. |
| `lunar/api` | REST projection of the same DTO (JSON:API envelope, Sanctum, headers). |
| `storefront-ui` | Reference host app with `lunar/checkout` installed (demos install-and-go). |
| hosted (future) | The **same** app under a Lunar-operated deployment, rendering a session by UUID. |

## The specs

| # | Title | Status | Decides |
|---|---|---|---|
| [0000](0000-overview.md) | Overview | draft | Problem framing, package map, 5 cross-cutting decisions, spec index. |
| [0001](0001-core-element-model.md) | Core element model | draft | Capability interfaces, the element manager + ordered pipeline, registration/placement, validation gates (via cart actions), completeness, dependencies, the pure DTO, enums, events. The transport-neutral contract everything else projects. |
| [0002](0002-payment-methods-and-driver.md) | Payment methods & driver | draft | `PaymentMethod` registry **over the existing `Payments`/`AbstractPayment` seam** (no parallel abstraction); host element + express region + submit seam; payment success = `OrderState` transition (no new state); integrity is driver-owned. Stripe optional. Its `component()` is delivered as a chunk (0009). |
| [0003](0003-transport-projections.md) | Transport projections | draft | One `CheckoutData`, many thin projections. REST surface (`POST /checkout` → session, coarse cart writes, `/complete`); the Inertia render inside the self-contained app + the `/checkout/elements` multiplexer; cart identity via `CartSession`; rendering-hints-vs-contract; hybrid registry; frontend state/sync; a11y; per-adapter error rendering. |
| [0004](0004-checkout-session.md) | Checkout session | draft | The cart→checkout object: UUID capability token, **pinned** currency/channel/locale + fingerprint-reconciled live cart (0010 — no frozen copy), own state machine (Open/PaymentProcessing/Completed/Expired/Cancelled), idempotent guarded completion → order. The integrity + freeze + resumability anchor. **Driver-based**: a swappable `CheckoutDriver` (default `LunarCheckoutDriver`, by-name via `config('lunar.checkout.driver')`) ingests *any* cart → session and finalises session → *any* order (driver-opaque `order_reference`), so a non-Lunar backend can front the checkout. |
| [0005](0005-hosted-checkout.md) | Hosted checkout (future) | draft | The same self-contained app under a **Lunar-operated deployment** — a deployment mode, not a new render stack. Session-by-UUID, return-URL redirects, default element set + method-from-config, UUID-capability + strict-CSP security. Built last. |
| [0006](0006-worked-example.md) | Worked example (guide) | draft (non-normative) | End-to-end walkthrough of one reference checkout (contact → address → shipping → payment) through the Inertia spine — element impl, registration, session, DTO, Vue render, payment method as a runtime chunk, completion. Illustrative skeleton. |
| [0007](0007-discount-element.md) | Discount element | draft | Resolves 0001's open question: discount is a `Summary`-region `CapturesData` element; adds an additive `Cart::setCoupon()` so it persists through the cart API. |
| [0008](0008-checkout-ui-and-theming.md) | UI delivery & theming | draft (rewritten) | The self-contained app delivery (prebuilt `dist/`, own build/Inertia-root/route); install-and-go vs publish-and-own; the three customisation tiers; swappable `CheckoutTheme` bound in the container; one sanitization chokepoint. **Supersedes the earlier "embed into the consumer's Vue build" framing.** |
| [0009](0009-frontend-element-extension.md) | Frontend element extension | draft | How third-party elements/gateways contribute Vue UI into the prebuilt app **at runtime, no rebuild, no app publish**: `CheckoutAssets` registry, contributed ESM chunks served same-origin (asset route, no `vendor:publish`) self-registering into the component registry, **shared Vue + SDK via `window.Vue`/`window.Lunar` externals** (the load-bearing piece), the `@lunarphp/checkout-element` build preset, version/compat + dev fallback; first-party gateways prebuilt into `dist/`. |
| [0010](0010-cart-session-reconciliation.md) | Cart–session reconciliation | draft | The session's **backend-neutral data model** (no Lunar FKs; store only non-derivable, derive the rest through the driver), the full `CheckoutDriver` store/read surface, the element bag, and the integrity model: **sync-while-Open + pin-and-confirm-at-pay + re-verify-at-complete**, the refund invariant (no charge without an order), void-first invalidation, one-active-session-per-cart, bounded `PaymentProcessing` reconciliation, lifecycle events. Supersedes 0004's frozen-line scoped cart. |
| [0011](0011-address-lookup-and-element-bag.md) | Address lookup & the element bag | draft | An `AddressLookup` driver seam (manager + config by name, `NullLookup` default, `IdealPostcodes` as the first real driver, gated + throttled + cached lookup route) and the work that makes 0010 §C's element bag real: `ElementDataStore` rename, row-backed store under a lock, session-scoped + owned element route, bag carry-over on supersede, `OrderPlacing` fired before **placement** (amending 0001 §I for the webhook-first race), and `OrderDetails` — PO reference + notes — as the first bag-backed element. |

## Cross-cutting decisions (apply to all specs)

1. **Persistence goes through the cart API**, never direct model writes — `store()` lands on the
   context's typed write verbs, driver-mediated when a session exists (0010 §B); the Lunar driver
   routes them to `Cart::setShippingAddress()` / `setShippingOption()` / etc., which run validators +
   `recalculate()`.
2. **Context is resolved, never re-derived** — from `StorefrontSession`/`CartSession` pre-session, from
   the session's **pinned** context once it exists; amount integrity is fingerprint-reconciled at the
   pay and completion boundaries (0010 §D–E.2).
3. **No new order/payment lifecycle state; the *session* gets one** — payment success is the existing
   `AwaitingPayment → InProcess` transition + `transactions`; the in-progress lifecycle lives on the
   `CheckoutSession`. That order transition is the default `LunarCheckoutDriver`'s `complete()`; a
   non-Lunar checkout driver owns its own order semantics, the session machine stays universal.
4. **Payment is gateway-agnostic and optional** — rides `Payments`/`AbstractPayment`; Stripe is one
   optional reference; integrity is driver-owned.
5. **Lunar conventions are non-negotiable** — service-layer DI, container-as-swap-seam (no `config(...class)`
   swaps), folder responsibilities, Octane build-once, 16-locale translations, PHPStan L0 + Pint + Pest,
   `ArchitectureTest`, ULID public ids, Rector rules for breaking changes.

## What changed in this round (for reviewers who saw the earlier drafts)

- **0008 rewritten.** The "ship Vue source, consumer's Vite compiles, embed in consumer's app" model is
  **abandoned**. The package is now a self-contained prebuilt app. This is the headline change; everything
  below follows from it.
- **0009 added.** New spec resolving the extensibility gap a sealed prebuilt bundle creates: third-party
  elements/gateways could not contribute UI without a fork/rebuild. Runtime chunk registration over a
  shared Vue + SDK fixes it; first-party gateways are prebuilt for zero-build install-and-go.
- **Contribution made orthogonal to publish-and-own (0008 + 0009).** Adding your own element/gateway +
  JS requires neither publishing the app's source nor rebuilding it — only registering your *own* small
  chunk. Two independent axes, spelled out; the host app is a first-class contributor (bespoke element
  from `AppServiceProvider`), not just packages.
- **Mechanism reconciled to the Statamic-proven approach (0008 §A, 0009 §B), matching the scaffold.**
  Shared runtime is now `window.Vue` / `window.Lunar` globals + build-preset externals (import map +
  `es-module-shims` dropped). Both the app's own bundle and contributed chunks **stream same-origin from
  package routes** (`/checkout/build/{file}`, `/checkout/assets/{package}/{file}`) — so install-and-go and
  adding an element both need **no `vendor:publish`**. `dist/` provenance (commit vs release-download)
  stays open; serving is resolved. Two more Statamic patterns adopted (0009 §C.3/§D): the SDK + build
  preset are **vendored on disk via a `file:` dependency** (npm-publishing optional, no version-skew),
  and a contributor gets **HMR** on their chunk via an optional Vite `hot` file.
- **0004 made driver-based.** The session is created/finalised by a swappable `CheckoutDriver`
  (default `LunarCheckoutDriver`, resolved by name via `config('lunar.checkout.driver')`): `createSession`
  ingests *any* cart, `complete` finalises to *any* order (a driver-opaque `order_reference` string, not
  a fixed Lunar FK). The session/snapshot/UUID/state-machine/element-model stay backend-neutral, so a non-Lunar
  backend (e.g. a Statamic basket) can front `lunar/checkout`. 0000 / 0002 / 0003 reconciled: the
  `AwaitingPayment → InProcess` transition is now scoped as the Lunar driver's `complete()`, and the
  checkout driver is disambiguated from the payment gateway driver.
- **0000 / 0001 / 0002 / 0003 / 0005 / 0006 reconciled** to the self-contained model and the 0009 seam —
  "installable render package embedded in your storefront" language removed; hosted reframed as a
  deployment mode; the worked example's payment component reframed as a runtime chunk.
- **0010 added + integrity hardened (2026-06-12).** The session is now fully backend-neutral (no Lunar
  FKs; `order_reference` replaces the order morph) and references the **live** cart — the frozen-line
  scoped cart and clone-vs-claim are gone. Integrity is end-to-end: the fingerprint covers the payable
  total (out-of-band address/shipping changes can't slip the gate), the pay gate pins amount +
  fingerprint atomically and re-pins the gateway intent before client confirm, `complete()` re-verifies
  the pinned fingerprint inside the order-creation transaction, and a captured payment that produces no
  order is **always refunded**. Invalidation is void-first (never a terminal session with an unresolved
  charge), one active session per cart (supersede / `409`), `PaymentProcessing` reconciliation is
  bounded, reads never write, and terminal sessions get a PII retention prune. `requiresIntent()` is
  resolved onto `PaymentMethod` (0002) and the confirmation token rides the transports (0003).
- **Round-2 hardening (2026-06-12, post senior-panel review).** The panel's REQUEST-CHANGES applied
  across the set: the fingerprint is **driver-owned** — an HMAC over lines + address identity +
  shipping option + coupon + `amount_total` + `currency_code` (core's `Cart::fingerprint()` does not
  satisfy the 0010 §D property); advisory-intent ownership (`payment_intent_ref` recorded at intent
  creation) + the **refund-never-complete** webhook rule for non-current intents; one-active-session
  concurrency via a nullable `active_cart_reference` column + plain unique index (partial indexes are
  not portable to MySQL); all persisted session writes are single-statement guarded UPDATEs, with the
  guarded-transition helper as the concurrency layer over the spatie machine (the legality layer);
  gate step 0 (live currency/channel must equal pinned); the sync path runs the gate inside
  `complete()`'s order transaction; the `SupportsPaymentIntents` core capability seam
  (fetch/void/refund by intent); the stall protocol + `lunar:checkout:reconcile` operator resolve;
  consistent-read completion + idempotent `complete()`; new transport verbs (`POST /checkout/{uuid}/pay`,
  `/contact`, `/elements/{handle}`); `CheckoutData` gains session-level fields (sessionUuid, status,
  summary, confirmationToken, hasDrifted); and the package home is pinned to `packages/checkout`
  (`Lunar\Checkout\…`) — the transport-free `checkout-core` split stays an open question.

## Suggested review order

1. **0000** — the framing + cross-cutting decisions (everything assumes these).
2. **0001** + **0004** + **0010** — the core contract (element model), its anchor (session), and the
   live data-model + integrity design. The rest are projections.
3. **0008** + **0009** — the UI delivery decision and the extension seam (the most-changed, highest-risk pair).
4. **0002** + **0003** — payment model + transport projections (depend on 0001/0004/0009).
5. **0007** — the discount element (small, self-contained).
6. **0006** — the worked example, last, as a consistency check that the above compose end-to-end.

## Highest-risk things to scrutinise

- **0009 §B — single shared Vue + SDK via `window.Vue`/`window.Lunar` externals.** The load-bearing
  mechanism (the Statamic approach; import map dropped). If two Vue runtimes ever coexist,
  `provide/inject` (so `useCheckout()`) breaks across the boundary. Confirm the build preset's externals
  actually enforce one runtime and that the app bundle assigns the globals before any chunk runs.
- **0008 §A — prebuilt `dist/` provenance.** Assets stream same-origin from a package route (serving is
  resolved — no `public/` publish). Open: whether `dist/` is committed on every UI change or pulled from
  a release by a composer download plugin. Either way consumers trust a prebuilt blob — confirm the
  CI/release build step.
- **Dual-Inertia coexistence** (0008 open question) — if the consumer's storefront also runs Inertia, two
  apps share a page lifecycle. Expected fine (separate root/manifest/namespace) but unverified.
- **0010 — `SupportsPaymentIntents` is new public surface in `lunar/core`.** Small and additive
  (`Contracts/`, fetch/void/refund by intent), but contract on landing — confirm the verb shape
  before gateway drivers adopt it, because changing it later needs a spec + Rector rule.
- **Gateway adoption of the capability interface.** The void-first/reconcile/refund-by-intent
  protocol only works for gateways implementing `SupportsPaymentIntents`; gateways without it flow
  into the stall protocol (stalled event → low-frequency retries → operator resolve). Confirm that
  degraded path is acceptable for the gateways merchants actually run.
- **0000 — the transport-free `checkout-core` split is unresolved.** Until it is, a REST-only
  consumer (`lunar/api`) depends on the package that also ships the Inertia app.
- **0002/0009 — the first-party-bundled vs runtime-chunk split.** Confirm the registry contract is
  identical whether registration happens at build time (bundled) or runtime (chunk).

## Open questions rolled up

- **0000** — final spec home (`lunar/lunar/specs/0022+` vs `lunar/api`); the transport-free
  `checkout-core` split (element model + session + driver without the Inertia app).
- **0002** — multiple simultaneous express methods; saved/tokenized methods (deferred).
  (`requiresIntent()` resolved onto the interface.)
- **0003** — cart token format; `CheckoutLayout` backend-derived vs partly frontend; scoped-reload granularity.
- **0004** — guest → authenticated session association; return-URL allowlist source; expiry recovery
  and the deferred `mode` seam. (Clone-vs-claim, the driver surface, and the ingest DTO are resolved
  by 0010.)
- **0010** — price-affecting element seam; read-verb caching for hosted render; long-gap resume UX.
  (Driver contract segregation resolved: one `CheckoutDriver` interface + a mandatory
  `AbstractCheckoutDriver` base class for third-party drivers.)
- **0005** — deployment model (Cloud SaaS vs self-hostable); domain (Lunar host vs CNAME); frontend-less
  customisation surface.
- **0008** — where prebuilt `dist/` comes from (committed vs release-downloaded; serving via the
  same-origin route is resolved); dual-Inertia coexistence; stale-source upgrade reminder; vendored vs
  CDN fonts; exact themable token set.
- **0009** — exact stable SDK surface + the `window.Lunar` global shape; asset auto-discovery vs explicit
  register; third-party key namespacing/collisions; hosted multi-tenant asset allowlist.
</content>
