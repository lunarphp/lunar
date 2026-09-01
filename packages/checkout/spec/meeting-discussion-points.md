# Checkout Elements — meeting discussion points

- Purpose: walk the boss through the Checkout Elements design (specs `0000`–`0007`) and land the
  decisions that gate building it.
- Audience: design/architecture review.
- For each section: **Direction** (what we're proposing) · **Lunar today** (supports / doesn't,
  verified against `lunar/lunar` `2.x`) · **Caveats / problems / scaling** · **→ Decision needed**.

> Support facts below were checked against the `2.x` branch on 2026-06-08, not assumed.

> **Superseded in part (2026-06-12).** This document predates `0010-cart-session-reconciliation`.
> Since it was written: clone-vs-claim is **resolved** (neither — live `cart_reference` +
> fingerprint reconciliation), the session table went fully backend-neutral (`order_reference`
> replaces the order morph), the integrity model gained the completion re-verify + refund
> invariant + void-first invalidation + one-active-session-per-cart + bounded
> `PaymentProcessing` reconciliation, and `requiresIntent()` landed on the `PaymentMethod`
> interface. Treat the 0004 decision points below as historical; see 0010 + the amended 0004.

---

## Headline decisions to leave the meeting with

1. **Build scope & sequencing** — core element model (`0001`) + session (`0004`) are the load-bearing
   builds; everything else projects off them. Confirm we build core-first, projections second.
2. **New core surface** — a new top-level `Checkout/` folder, new public `Contracts/Checkout/*`, a new
   `checkout_sessions` table + state machine. All become BC-protected contract on landing. Approve the
   surface or trim it.
3. **`laravel-data` dependency** — the DTO wants it; core does **not** currently depend on it. Add the
   dependency, or build the DTO on the existing core mechanism?
4. **New published package** — `lunar/checkout` is a new composer + npm Vue library to release and
   maintain. Approve the packaging/maintenance commitment.
5. **External dependencies** — design leans on two *planned* items that have **not landed**: `public_id`
   (ULID) cart identity and the Region / `StorefrontContext` concept. Are those on the roadmap, and do
   they block us?
6. **Spec home** — do these port to `lunar/lunar/specs/` as `0022+`, or live in `lunar/api`'s set?
7. **Hosted checkout** (`0005`) — product decision on deployment model (Cloud SaaS vs self-host); not a
   near-term build, but it constrains the session + package design now.

---

## 0000 — Overview / architecture

- **Direction.** Transport-agnostic element model + pure DTO in `lunar/core`, anchored by a
  `CheckoutSession`, projected by three consumers (`lunar/api` REST, `lunar/checkout` Inertia,
  future hosted). One contract, many transports — the PHP analogue of Stripe Elements.
- **Lunar today.**
  - *Supports:* `StorefrontSession` + `CartSession` contracts exist; gateway-agnostic `Payments` /
    `AbstractPayment` registry exists; spatie `OrderState` machine + `OrderStateConfig` seam exist;
    cart write+recalc pipeline exists.
  - *Doesn't:* no checkout abstraction of any kind today — cart → order is one opaque step. No
    `CheckoutSession`. No first-party storefront checkout UI.
- **Caveats / scaling.**
  - Four packages to keep in lockstep via one DTO + generated TS types; drift risk if the type-gen /
    CI key-check isn't enforced from day one.
  - The "one DTO serves all transports" claim is the whole bet — if a transport needs to bend the
    contract, the design leaks.
- **→ Decision:** endorse the package split (core / api / checkout / hosted) and the core-first build
  order. Confirm spec home (`lunar/lunar/specs/0022+`?).

---

## 0001 — Core element model

- **Direction.** A `Checkout/` concern in core: capability interfaces (small required core +
  opt-in `CapturesData` / `ReportsCompletion` / `ControlsVisibility` / …), a manager that resolves an
  ordered pipeline, two validation gates that write **through the cart API**, and a pure DTO.
- **Lunar today.**
  - *Supports:* cart-API writes with validators + recalc (`setShippingAddress`, `setBillingAddress`,
    `setShippingOption`, `canCreateOrder`) — all verified present; `ShippingManifest::getOption()`.
  - *Doesn't:* no element/step abstraction; no `Checkout/` folder; no `Contracts/Checkout/*`. The DTO
    layer — core does **not** depend on `laravel-data` today.
- **Caveats / problems / scaling.**
  - **DTO mechanism is unresolved** and it's a real fork: adopt `laravel-data` (clean TS-type
    generation, new core dependency) vs roll the existing core DTO mechanism (no new dep, more
    hand-work). Open question in the spec.
  - `CheckoutContext` shape + its test fake are undefined — blocks `accepted` and blocks writing
    testable elements.
  - **Built-in elements only partially specced.** `ShippingOptions` (`0006`) and `Discount` (`0007`)
    are worked out; `ContactInformation` and `AddressElement` are referenced everywhere but never
    fleshed out — a gap before "reference checkout completes end-to-end."
  - Octane: pipeline must be built once at boot, never rebound per request — discipline, not free.
  - Scaling: capability-interface model is additive (new capability ≠ breaking change) — good for
    long-term surface growth.
- **→ Decision:** (a) DTO mechanism — `laravel-data` or not? (b) approve the new `Checkout/` folder +
  `Contracts/Checkout/*` public surface. (c) who fleshes out contact/address elements?

---

## 0002 — Payment methods & driver

- **Direction.** A thin `PaymentMethod` presentation/capability descriptor layered over the
  **existing** `Payments` / `AbstractPayment` seam — *not* a parallel payment abstraction. Stripe is
  one **optional** reference, never a core dependency. Payment success = `OrderState` transition, not a
  new state.
- **Lunar today.**
  - *Supports:* `Payments` manager + `AbstractPayment` with `authorize()` / `capture()` / `refund()`
    verbs (verified on the `OfflinePayment` driver); `OrderStateConfig` seam for bespoke states.
  - *Doesn't:* no `createIntent` / `confirm` verbs in core — those are gateway-specific (the API's
    `payment-intent` endpoint delegates to the driver). No `PaymentStatus` enum, no `paid` state (spec
    `0021` deferred payment/fulfilment decomposition) — **by design**, but worth stating plainly to the
    boss since it surprises people.
- **Caveats / problems / scaling.**
  - "Payment success is just `AwaitingPayment → InProcess` + `transactions` rollup" — reporting/
    analytics that expect a `paid` flag must derive it. Flag this expectation gap.
  - Integrity (amount re-verify, idempotent webhooks, orphan reconciliation) is **driver-owned**; we
    reference the Stripe implementation rather than re-spec it. Risk: a poorly-written gateway driver
    silently lacks these guarantees. Mitigation = the payment-method contract test fixture.
  - Scaling: any number of gateways register methods; express wallets are the same objects in a
    different region. Clean. But "multiple simultaneous express methods + ordering" is an open question.
- **→ Decision:** confirm we do **not** build a new payment state machine; confirm the contract-test
  fixture is a ship requirement for gateway authors.

---

## 0003 — Transport projections (REST + Inertia)

- **Direction.** `CheckoutData` is the single contract; REST (`lunar/api`) and Inertia
  (`lunar/checkout`) are thin projections. Inertia adds a `/checkout/elements` multiplexer
  (Gate 1 + coarse cart write + recomputed DTO in one response). Cart identity via `CartSession`,
  never a request-supplied id.
- **Lunar today.**
  - *Supports:* `CartSession` resolution; a `draft-storefront-api.md` already sketches `/cart/*` +
    `POST /checkout`.
  - *Doesn't:* the REST `X-Lunar-Cart` token wants `public_id` (ULID) which is **not on the cart yet**
    — interim is server-side `CartSession` resolution. No Inertia render layer / Vue registry exists.
- **Caveats / problems / scaling.**
  - **SSR state-bleed is a real PII risk** — module-level singletons in the Vue layer would leak
    addresses/email across concurrent SSR requests. The spec mandates instance-scoped
    `CheckoutProvider`; this must be enforced, not assumed.
  - Deferred props (intent secret, shipping options, country list) must stay off the serialization
    path — `props()` calling `ShippingManifest::getOptions()` would fire the modifier pipeline (maybe
    external rate APIs) on every render.
  - Scoped-reload granularity (which flipped elements to return) is unmeasured — "measure before
    tuning."
  - Scaling: generated TS types + a CI key-drift check are the guardrails; without them the multi-
    transport promise rots.
- **→ Decision:** accept the `public_id` dependency (or the interim server-resolved token); confirm
  SSR-safety + the CI key-check are acceptance gates.

---

## 0004 — Checkout session (the central, biggest build)

- **Direction.** A `CheckoutSession` model created from the cart at checkout start: public UUID
  capability token, **snapshots lines + pins channel/currency** (amount-integrity anchor), own spatie
  state machine (Open / PaymentProcessing / Completed / Expired / Cancelled), idempotent completion →
  order. The freeze guard and resumability live here.
- **Lunar today.**
  - *Supports:* the spatie state-machine pattern + config-seam (mirrors `OrderStateConfig`, verified);
    the cart write/recalc pipeline it reuses on the scoped cart; `OrderState`.
  - *Doesn't:* **`CheckoutSession` is greenfield** — new table, model, state machine, scheduled expiry
    job, all net-new. No Cart state machine (0021 deferred) — this partially fills that gap.
- **Caveats / problems / scaling.**
  - **Clone-vs-claim the checkout-scoped cart is unresolved** and it's a storage/complexity fork —
    clone (shopper keeps editing original, more rows) vs claim (reuse current cart, spin a fresh
    basket). Needs deciding before build.
  - Guest → authenticated session association through login is an open question (`CartSession::associate`).
  - `success_url`/`cancel_url` allowlist source (host config vs channel) unresolved — open-redirect
    surface.
  - Scaling: Cart 1:N sessions means session rows accumulate; expiry job + retention policy needed.
    Stale `PaymentProcessing` sessions are deliberately *not* auto-expired (payment may have
    succeeded) — relies on gateway reconciliation backstop being present.
- **→ Decision:** **clone vs claim** (the big one); approve the new table + state machine + expiry job;
  decide the return-URL allowlist source.

---

## 0005 — Hosted checkout (future)

- **Direction.** A Lunar-served standalone page that renders a session by UUID, reusing the **same**
  `<LunarCheckout>` — a fourth projection, not a parallel stack. For merchants with no frontend.
  Awareness now; built last.
- **Lunar today.**
  - *Supports:* nothing yet — entirely future; depends on `0004` + `0003 §B` landing first.
  - *Doesn't:* no hosted infra, no default-element-set registration path, no per-merchant branding/
    method-from-config mechanism.
- **Caveats / problems / scaling.**
  - **Deployment model is a product decision:** Lunar Cloud multi-tenant SaaS vs self-hostable package.
    Big strategic fork — affects domain/CNAME, PCI scope, billing.
  - Rendering stack open (Inertia vs light Vue mount vs Blade+islands).
  - Scaling: multi-tenant hosting = Lunar owns uptime/PCI/CSP for everyone's checkout. Material
    operational commitment.
- **→ Decision:** is hosted a product goal? If yes, SaaS or self-host? (Decides nothing we build now,
  but sets the constraint the session/package must respect.)

---

## 0006 — Worked example (guide)

- **Direction.** Not a spec — an end-to-end walkthrough (one reference checkout, Inertia spine,
  illustrative-skeleton code) to make the abstract design concrete for reviewers/implementers.
- **Lunar today.** N/A — documentation. Code is illustrative; real surfaces it leans on are verified.
- **Caveats.** Skeletons assume the unresolved opens (DTO mechanism, clone-vs-claim) — flagged inline.
  Keep it in sync as decisions land, or it misleads.
- **→ Decision:** none — useful as the meeting's "show me how it actually works" reference.

---

## 0007 — Discount element

- **Direction.** Resolves `0001`'s open question: discount **is an element** (Summary region,
  `CapturesData`, optional), capturing a single coupon code with apply/remove.
- **Lunar today.**
  - *Supports:* `carts.coupon_code` column (`CouponString` cast); `DiscountManager::validateCoupon()`;
    discounts applied during recalc via `DiscountManager::apply()`.
  - *Doesn't:* **no `Cart::setCoupon()` method** — coupon is set by writing the `coupon_code` property
    directly, which collides with `0001`'s "no direct model writes" rule. So the spec **proposes adding
    `Cart::setCoupon(?string, bool $refresh)`** to core (mirrors `setShippingOption`).
- **Caveats / problems / scaling.**
  - The core `Cart::setCoupon()` addition is a small but real core change — could land independently of
    checkout (it's useful generally). Decide ownership.
  - Single coupon only — core models one `coupon_code`; stacked/multiple coupons is out of scope until
    core supports it.
  - Auto-applied / free-shipping discounts (no code) need a display path — open.
- **→ Decision:** approve adding `Cart::setCoupon()` to core, and whether it's part of this work or a
  standalone cart-API improvement.

---

## Cross-cutting risks to name explicitly

- **Two unlanded dependencies** gate the clean version: `public_id` (ULID) and Region /
  `StorefrontContext`. Interim workarounds exist (server-side `CartSession`, channel-scoped country
  list) but they're debt until those land.
- **Convention load:** service-layer DI (spec `0016`), folder rules (spec `0013`), Octane build-once,
  16-locale translations, PHPStan L0 + Pint + Pest + ArchitectureTest — all mandatory on every new
  surface. Real per-feature cost; not optional.
- **Maintenance surface:** a new core concern + a new published Vue/composer package + a REST projection
  + (later) a hosted app. Each is a release+support stream. Confirm the team can carry it.
