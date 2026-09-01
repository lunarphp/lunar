# 0000 — Checkout Elements: overview

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-05
- TODO item: "Checkout Elements — composable, provider-agnostic checkout building blocks"

> This is the umbrella for a set of numbered specs under `checkout/`. It frames the problem,
> fixes the cross-cutting architecture and conventions, and indexes the per-concern specs. Each
> numbered spec stands on its own and lands independently. Specs are written in present tense
> describing the proposed end state, following the Lunar v2 house format (these are authored in
> the storefront repo for now; they port to `lunar/lunar/specs/NNNN-*` when the design settles).

## Problem

A Lunar headless storefront has no first-party way to assemble a checkout. Every consumer
rebuilds the same flow — contact, addresses, shipping selection, payment — wiring the cart
pipeline, validation, and a payment gateway by hand, and each ships an incompatible result. The
recurring need is a **composable, provider-agnostic set of checkout building blocks** (the
PHP/Lunar analogue of Stripe Elements): the server declares self-describing elements; a client
renders them and posts data back; payment is pluggable.

An earlier prototype proved the shape but was Inertia-coupled, special-cased Stripe, wrote
models directly (bypassing the cart pipeline), invented its own payment/checkout state, and
duplicated payment-integrity logic. v2 has since introduced state machines, a service-layer DI
mandate, a folder/contract convention, and a draft headless REST API — the design must align to
those rather than reinvent them.

## Proposal

A **transport-agnostic element model in `lunar/checkout`** (`Lunar\Checkout\…` — everything
checkout lives in `packages/checkout` for now; a transport-free `checkout-core` split is an open
question below), anchored by a **checkout session** (cart→checkout object) and projected by
**several consumers**:

```
lunar/checkout     Everything checkout. The element model: capability interfaces, registry,
                   manager + collaborators, PaymentMethod registry over core's existing
                   Payments/AbstractPayment seam, enums, events, a PURE DataObject DTO — and
                   the CheckoutSession (UUID, pinned currency/channel, driver-owned-
                   fingerprint-reconciled live cart, own state machine) created/finalised by
                   a swappable CheckoutDriver (default LunarCheckoutDriver). PLUS the
                   self-contained Inertia/Vue checkout app — Lunar-owned end-to-end, ships
                   prebuilt (own build, Inertia root, page resolver, dist/) and serves its own
                   route. Install-and-go: the consumer's storefront (ANY stack) links/redirects
                   to it; Inertia/Vue/Vite are internal. The dev registers elements in a service
                   provider (Checkout::add(...)); publish-and-own to customise (0008). Third-party
                   elements/gateways contribute Vue UI via runtime chunks, no rebuild (0009).
   │
   ├── lunar/core       The seams checkout rides: the cart API (Cart::set*), ShippingManifest,
   │                    Payments/AbstractPayment — plus ONE addition, the SupportsPaymentIntents
   │                    capability contract (fetch/void/refund by intent) gateway drivers adopt
   │                    for the integrity protocol (0010). No checkout code.
   │
   ├── lunar/api        REST projection — POST /checkout creates a CheckoutSession (returns uuid
   │                    + DTO), GET /checkout/{uuid} reads it, POST /checkout/{uuid}/pay,
   │                    /payment-intent, /complete; StorefrontSession / CartSession, Sanctum,
   │                    JSON:API envelope + errors. (Consumes the session model from
   │                    lunar/checkout until the checkout-core split — open question below.)
   │
   ├── storefront-ui    Reference host app — a thin demo that has lunar/checkout installed,
   │                    showing the install-and-go path.
   │
   └── hosted (future)  The SAME lunar/checkout app under a Lunar-operated deployment, rendering a
                        session by UUID for merchants with no app to host it in (Stripe-hosted-style).
                        Spec 0005 — a deployment mode, not a new render stack; built last.
```

The **CheckoutSession is the central object**: `cart → session → order` (UUID), which pins
currency/channel/locale and mirrors the live cart by fingerprint (storing nothing derivable —
[[0010-cart-session-reconciliation]]), owns the data checkout collects, runs its own state
machine, and produces an order on completion. Both ends are owned by a **swappable checkout driver** — Lunar
ships a default `LunarCheckoutDriver`, but the source cart and destination order need not be Lunar's
(a non-Lunar backend can front `lunar/checkout` via its own driver, [[0004-checkout-session]]); the
session, its snapshot, UUID, state machine, and the element model between them are backend-neutral
and Lunar-owned. The element model + DTO are the contract;
the consumers are projections sharing generated types. `component()` keys and the layout
descriptor are **rendering hints** — load-bearing for a Vue registry, ignorable by a headless
client.

### Specs in this set

| # | Spec | Scope |
|---|------|-------|
| 0001 | Core element model | Capability interfaces, manager + collaborators, registration/placement, validation gates (via cart actions), completeness, dependencies, DTO, enums, events, folder/DI/Octane conventions |
| 0002 | Payment methods & driver | `PaymentMethod` registry over the gateway-agnostic `Payments`/`AbstractPayment` seam; intent lifecycle reconciled with the API's `POST /checkout/{uuid}/payment-intent`; integrity delegated to the driver; Stripe as an *optional* reference |
| 0003 | Transport projections | The pure DTO + REST (`lunar/api`) and the Inertia render inside the self-contained `lunar/checkout` app; cart identity; error/validation projection; hybrid render; sync model; a11y |
| 0004 | Checkout session | The cart→checkout object: UUID capability token, pinned currency/channel + fingerprint-reconciled live cart ([[0010-cart-session-reconciliation]]), own spatie state machine (Open/PaymentProcessing/Completed/Expired/Cancelled), idempotent completion → order; the integrity + freeze + resumability anchor. **Driver-based** — a swappable `CheckoutDriver` (default `LunarCheckoutDriver`, resolved by `config('lunar.checkout.driver')`) ingests any cart and finalises to any order, so a non-Lunar backend can front the checkout |
| 0005 | Hosted checkout (future) | The same `lunar/checkout` app under a Lunar-operated deployment, rendering a session by UUID; gateway-agnostic; return-URL redirects; UUID-capability security. A deployment mode, not a new render stack; built last |
| 0008 | UI delivery & theming | The self-contained Inertia app delivery (prebuilt `dist/`, own build/root/route); install-and-go vs publish-and-own; swappable `CheckoutTheme` bound in the container; sanitization chokepoint |
| 0009 | Frontend element extension | How third-party elements/gateways contribute Vue UI into the prebuilt app at runtime — `CheckoutAssets` registry, contributed ES-module chunks served same-origin, shared Vue + SDK via `window.Vue`/`window.Lunar` externals, build preset, version/fallback; first-party gateways prebuilt |
| 0006 | Worked example (guide) | End-to-end walkthrough of one reference checkout through the Inertia spine — element impl, registration, session, DTO, Vue render, payment method, completion. Illustrative, not normative |
| 0007 | Discount element | Resolves 0001's open question: discount is a `Summary`-region `CapturesData` element; adds an additive `Cart::setCoupon()` so it persists through the cart API |
| 0010 | Cart–session reconciliation | Backend-neutral session data model (no Lunar FKs; store only non-derivable); the full `CheckoutDriver` store/read surface; element bag; **driver-owned fingerprint + sync-while-Open + pin-and-confirm-at-pay** integrity with the completion re-verify + refund invariant; void-first invalidation on every terminalizing transition; one-active-session-per-cart via guarded unique-column concurrency; the `SupportsPaymentIntents` gateway seam; bounded `PaymentProcessing` reconciliation + stall protocol; lifecycle events |

### Cross-cutting decisions (apply to all specs)

1. **Persistence goes through the cart API, never direct model writes.** `store()` lands on the
   context's typed write verbs, driver-mediated when a session exists
   ([[0010-cart-session-reconciliation]] §B); the Lunar driver routes them to
   `Cart::setShippingAddress()` / `setBillingAddress()` / `setShippingOption()`, which run the
   configured validators and `recalculate()`. Direct `fill()->save()` is forbidden — it skips
   validation and tax/total recomputation.
2. **Context is resolved, never re-derived in checkout.** Channel, currency, customer-group, and
   customer come from `StorefrontSession` + `CartSession` for a session-less embedded flow; once a
   `CheckoutSession` exists they come from its **pinned** snapshot (overriding the live session —
   the amount-integrity anchor, [[0004-checkout-session]]). Either way checkout re-resolves none
   of them; the future Region concept and `StorefrontContext` are the forward seam.
3. **No new *order/payment* lifecycle state; the *session* gets one.** Payment success is the
   existing `AwaitingPayment → InProcess` `OrderState` transition + `placed_at` + `transactions`
   rollup — there is no `PaymentStatus` enum and no `paid` state, and bespoke order states are
   added only via the `OrderStateConfig` seam. The in-progress *checkout* lifecycle lives on the
   `CheckoutSession` state machine ([[0004-checkout-session]]) — Open/PaymentProcessing/
   Completed/Expired/Cancelled — which is the home for the payment freeze and supersedes the
   earlier "derived read-model" framing. Element *completeness* stays derived; *session status*
   is persisted. The `AwaitingPayment → InProcess` order transition is specifically the **default
   `LunarCheckoutDriver`'s** `complete()` ([[0004-checkout-session]]); a non-Lunar checkout driver
   owns its own order semantics while the session machine stays universal. Order fulfilment status
   is deferred to the Lunar order-fulfilments spec.
4. **Payment is gateway-agnostic and optional.** The payment layer rides Lunar's existing
   `Payments` manager / `AbstractPayment` registry. `lunar/stripe` is one optional reference
   implementation, not a dependency; core checkout works with any registered gateway or none.
   Payment integrity (amount/currency re-verify, idempotent webhooks, transaction recording,
   orphan reconciliation) is **driver-owned** — the spec references the Stripe reference's
   existing implementation rather than re-specifying it.
5. **Lunar conventions are non-negotiable.** Service-layer DI (spec 0016: constructor-inject
   collaborators, bind seams to `Contracts/`, no `app()`/facade for collaborators, no
   `config(...class)` swaps, Actions expose only `execute()`); folder responsibilities (spec
   0013, mirrored inside `packages/checkout`: DTO → `DataObjects/`, enums → `Enums/`,
   contracts → `Contracts/` with the `Interface` suffix dropped); Octane (build the element
   pipeline once, bind config in `register()`, no runtime rebind); 16-locale translations;
   PHPStan level 0 + Pint + Pest; `ArchitectureTest` coverage; `public_id` (ULID) for any
   externally-addressable cart identity; future breaking changes need a Rector rule in the
   `upgrade` package.

## Alternatives considered

- **One monolithic spec.** Rejected: the concern spans several packages (core, api, the self-contained
  checkout app, the storefront, an optional gateway) with different review gates and BC profiles;
  the core contract must land before the projections.
- **Keep the Inertia-first design, add a REST shim later.** Rejected: it bakes transport into
  the contract (an Inertia prop as "the contract"), forcing the REST package to re-wrap or
  re-serialize — the exact drift the DTO is meant to prevent.
- **Ship a payment abstraction parallel to `Payments`/`AbstractPayment`.** Rejected: Lunar
  already has a gateway-agnostic payment registry; a second one makes every gateway implement
  two abstractions. Stripe-specific design is rejected outright — Stripe is optional.
- **Model payment/checkout as new state machines now.** Rejected: spec 0021 deliberately
  deferred the payment/fulfilment decomposition; a derived read-model plus the single
  `OrderState` machine is sufficient and avoids decompose-then-recompose churn.

## Migration impact

- Greenfield in v2 (the prototype is not on `2.x`). The new public surface — element interfaces,
  `Contracts\CheckoutManager`, the `PaymentMethod` registry, the DTO — becomes contract on
  landing; future breaking changes require a spec + Rector rule per the package rule.
- The checkout concern lives in its own `packages/checkout` sub-package (`Lunar\Checkout\…`), not
  a new core `Checkout/` folder — core's spec-0013 list is untouched; core gains only the
  `SupportsPaymentIntents` capability contract (`Contracts/`, additive).
- Depends on the planned `public_id` (ULID) and Region/`StorefrontContext` TODO items; until they
  land, cart identity stays resolved server-side via `CartSession` and the country list stays
  channel-scoped.
- Reconciles with `draft-storefront-api.md`: `POST /checkout` is reframed to **create a checkout
  session** (cart→session→order, superseding the draft's cart→order-direct shape); `GET
  /checkout/{uuid}` reads it, `POST /checkout/{uuid}/pay` runs the pay-boundary gate, and
  `POST /checkout/{uuid}/payment-intent` backs gateway intents.
  This set supplies the element model, the session, and the integrity detail behind those endpoints.

## Acceptance checks

- Each numbered spec (0001–0009) reaches `accepted` with its own acceptance checks.
- A reference checkout (contact + address + shipping + a payment method) renders and completes
  end-to-end against the host app, with **no** core dependency on `lunar/stripe`.
- The same element model + DTO serves an Inertia render, a REST response, and the hosted page.

## Open questions

- Final home: do these port to `lunar/lunar/specs/` as `0022+`, or live in `lunar/api`'s spec
  set? (Owner: package maintainer.)
- **Resolved (amended after the 2026-06-12 panel):** the element model + DTO + `CheckoutSession`
  live in **`packages/checkout`** (`Lunar\Checkout\…`) — *not* `lunar/core` — alongside the render
  layer (`<LunarCheckout>`, registry, provider, composables), the built-in element
  implementations, and the registration facade, all in the **self-contained `lunar/checkout`
  Inertia app** — Lunar-owned end-to-end, prebuilt, serving its own route
  ([[0008-checkout-ui-and-theming]]). It is **not** embedded into a consumer's Vue build;
  any storefront stack links to it. `storefront-ui` is a reference host with it installed. The
  Lunar-hosted page ([[0005-hosted-checkout]]) is the same app under a Lunar-operated deployment.
  Core gains only the `SupportsPaymentIntents` capability contract.
- A transport-free **`checkout-core` split** (element model + session + driver, no Inertia/Vue) so
  a REST-only consumer (`lunar/api`) need not depend on the Inertia app package — recorded as an
  open question, not done now. (Owner: package maintainer.)

## References

- `draft-storefront-api.md` — the REST projection's transport, cart token, and checkout endpoints.
- [[0021-state-machines]] — single `OrderState` machine; `OrderStateConfig` seam; Octane rule.
- [[0016-service-layer-di]] — DI, container-as-swap-seam, Actions `execute()`-only.
- [[0013-base-directory-reorganisation]] — folder responsibilities; `Contracts/` no-suffix rule.
- `Lunar\Core\Contracts\StorefrontSession`, `CartSession`, `LunarUser`; `Manifests\ShippingManifest`;
  `PaymentTypes\AbstractPayment` + the `Payments` facade.
