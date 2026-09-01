# 0004 — Checkout Elements: checkout session

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-05
- TODO item: "Checkout Elements — checkout_session (cart→checkout object, enables hosted checkout)"

## Problem

Today the only transition is cart → order, a single opaque step. There is nowhere to anchor an
**in-progress checkout**: no addressable handle a client (or a Lunar-hosted page) can resume, no
pinned currency/total to stop the payment amount drifting as the cart mutates, no home for the
"freeze while payment is confirming" guard (spec 0021 deferred a Cart state machine), and no
self-contained record from which Lunar could serve a **hosted checkout** the way Stripe serves
its hosted Checkout page. The element model ([[0001-core-element-model]]) currently treats
"checkout state" as a derived read-model — fine for an embedded storefront, insufficient to host,
resume, or guarantee amount integrity.

## Proposal

A `CheckoutSession` model created from a cart at the moment checkout begins. It carries a public
UUID (a capability token), **pins channel/currency/locale and mirrors the live cart by
fingerprint** (storing nothing derivable — [[0010-cart-session-reconciliation]]), owns the data
the checkout collects, is driven by its own state machine, and produces an order on completion. It is
the addressable thing the REST API ([[0003-transport-projections]]) and the hosted checkout
([[0005-hosted-checkout]]) operate on.

The session is **created and finalised by a swappable checkout driver** (§ "The checkout driver").
The driver owns the two backend-specific ends — turning *a* cart (not necessarily a Lunar cart)
into a session, and finalising a session into *an* order (not necessarily a Lunar order). The
`checkout_sessions` record between them — its pinned snapshot, UUID, state machine, and the element
model — is backend-neutral and Lunar-owned. Lunar ships a default `LunarCheckoutDriver`.

### A. Model & table

`checkout_sessions`:

> **Refined by [[0010-cart-session-reconciliation]].** The table is
> **backend-neutral — no Lunar FK**. `cart_id` → `cart_reference` (driver-opaque
> string), `channel_id` → `channel_handle` (string), `customer_id` →
> `customer_reference` (string); an `element_data` bag and `cart_fingerprint` are
> added. The session **stores only what it cannot derive** (the integrity anchor,
> identity, lifecycle, pinned context, non-derivable element data); lines,
> addresses and shipping options are **derived through the driver**, never stored.
> The `order_type`/`order_id` morph is likewise replaced by a **driver-opaque
> `order_reference`** string, renders never write the session row, and completion
> re-verifies the pinned fingerprint before the order exists
> ([[0010-cart-session-reconciliation]] §D/§E.2). 0010 §A also adds the
> concurrency/reconciliation columns (`active_cart_reference`,
> `payment_processing_at`, `reconciliation_attempts`, `pruned_at`) and the index
> set. The rows below are updated to match.

| Column | Purpose |
|--------|---------|
| `id` | internal autoincrement |
| `uuid` | **public capability token** — unguessable, the external identifier (resolves the `public_id` need for checkout); never expose `id` |
| `cart_reference` | **driver-opaque** handle to the source cart the session drives (string — not an FK; the source may be non-Lunar / non-Eloquent). The Lunar driver stores the cart id ([[0010-cart-session-reconciliation]] §A) |
| `cart_fingerprint` | the **driver-owned** integrity fingerprint of the source cart (an HMAC over line content, address + shipping-option identity, coupon, `amount_total`, `currency_code` — [[0010-cart-session-reconciliation]] §D); the confirmation token + drift reference. **Not** core's draft-order dedupe fingerprint (`Cart::fingerprint()`) |
| `channel_handle`, `currency_code` | **pinned** context at creation (strings, not FKs); part of the integrity anchor |
| `locale` | **pinned** at creation; hosted page + notifications render in it regardless of a later browser locale (one of the 16) |
| `amount_subtotal`, `amount_total` | the current payable in minor units (canonical to the Lunar currency's `decimal_places`; the gateway driver owns the translation to/from gateway units in **both** directions); first-class, not buried in `meta`. A **live reflection** of the cart while `Open`, **pinned at the pay boundary** and confirmed against the live cart before the gateway is charged ([[0010-cart-session-reconciliation]] §D/§E) — not frozen at creation |
| `status` | `CheckoutSessionState` (§C). **No `payment_status` — deliberate (§A.1)** |
| `customer_reference` | nullable, **driver-opaque** customer handle (string — not an FK); set when the buyer is/becomes known |
| `customer_email` | nullable; pinned/prefilled contact for guest checkout, resume, and order notifications |
| `element_data` | json (`jsonb` where the platform supports it) **element bag** keyed by element handle — the home for non-derivable custom-element data ([[0010-cart-session-reconciliation]] §C) |
| `payment_intent_ref` | nullable, **gateway-agnostic** string; written when the intent is **created** — by default at the pay boundary, earlier (and advisory) only for gateways that opt in to a while-`Open` mounted payment UI — and rotated **void-first** on a retry in the same guarded write ([[0010-cart-session-reconciliation]] §E); the reconciliation/idempotency anchor (§E). Unique (nullable) so a webhook resolves to exactly one session. The driver owns the intent itself ([[0002-payment-methods-and-driver]]) |
| `client_reference_id` | nullable; the **merchant's own** correlation id (their order ref / external cart id), echoed back — for headless/hosted callers |
| `expires_at`, `completed_at`, `cancelled_at` | lifecycle timestamps; `expires_at` defaults to a configurable window (24h, Stripe-aligned) and is re-armed with a grace window (default 30 minutes) on any return `→ Open` ([[0010-cart-session-reconciliation]] §E.2) |
| `active_cart_reference` | mirrors `cart_reference` while `Open`/`PaymentProcessing`, set `NULL` in the same UPDATE as any terminal transition; plain **unique** index — the one-active-session mechanism ([[0010-cart-session-reconciliation]] §F.2) |
| `payment_processing_at` | set in the pay-boundary pin UPDATE; the reconciliation sweep's age anchor ([[0010-cart-session-reconciliation]] §A/§F) |
| `reconciliation_attempts` | unsigned tinyint, default 0; the bounded-reconciliation counter ([[0010-cart-session-reconciliation]] §F) |
| `pruned_at` | retention-prune idempotency marker ([[0010-cart-session-reconciliation]] §F) |
| `order_reference` | nullable, **driver-opaque** handle to the order the driver produced on completion (string — not a morph or FK: a non-Lunar driver's order may not be an Eloquent model, the same reasoning that makes `cart_reference` a string). The default `LunarCheckoutDriver` stores the Lunar `Order` id; the model resolves through the driver |
| `success_url`, `cancel_url` | hosted-checkout return URLs (validated, §F) |
| `metadata` | **merchant-supplied** arbitrary key/value, passed at creation and echoed back (Stripe `metadata` analogue) — distinct from `meta` |
| `meta` | **Lunar-internal** snapshot extras (creation-time breakdown, pins not given their own column) |

The line-item set, addresses and shipping options are **not** snapshotted onto the
table — they are derived through the driver's read verbs
([[0010-cart-session-reconciliation]] §B). A read-only **`url` accessor** (not a
column) derives the hosted-checkout URL from `uuid` + the hosted host
([[0005-hosted-checkout]]), mirroring Stripe's `url`. The model lives in the
checkout package (`packages/checkout`, `Lunar\Checkout\…` —
[[0001-core-element-model]] §A); DTOs under its `src/DataObjects/`
(`Lunar\Checkout\DataObjects\…`).

### A.1 Inspired by Stripe's Checkout Session — alignment & deliberate divergence

The shape borrows from Stripe's `checkout.session` object, with Lunar conventions taking precedence:

- **`status` only — no `payment_status`.** Stripe carries both `status` (`open`/`complete`/`expired`)
  and `payment_status` (`unpaid`/`paid`/`no_payment_required`). Lunar keeps the session-`status`
  parallel (`Open`/`Completed`/`Expired` + `Cancelled`/`PaymentProcessing`) but **omits
  `payment_status` by design**: payment success is the order's `AwaitingPayment → InProcess` transition
  plus the `transactions` rollup, never a session flag (cross-cutting decision #3,
  [[0002-payment-methods-and-driver]] §D). Stripe's `no_payment_required` maps to Lunar's **synchronous**
  completion path (zero-total / offline / on-account, §C) — the session simply reaches `Completed`.
- **Stripe's config fields are Lunar *elements*, not columns.** `custom_fields`, `custom_text`,
  `consent_collection`, `billing_address_collection`, `shipping_address_collection`, `shipping_options`,
  `allow_promotion_codes`/`discounts` — in Lunar these are all expressed through the element model
  ([[0001-core-element-model]]) and the cart (the discount element is [[0007-discount-element]]). The
  session table stays lean; arbitrary captured data lives on elements, not bespoke session columns.
- **`mode`** (`payment`/`setup`/`subscription`) is **not** modelled now — v2 checkout is payment-only;
  a `mode`/subscription seam is deferred (§Open).
- **Kept from Stripe:** the pinned totals + currency as the integrity anchor, `expires_at` with a default
  window, `success_url`/`cancel_url`, the hosted `url`, `client_reference_id`, `metadata`,
  `customer`/`customer_email`, pinned `locale`, and the expiry-recovery idea (§Open).

### The checkout driver (the swap seam)

The session is **driver-based**. A `CheckoutDriver` contract owns the two backend-specific ends of
the lifecycle; everything between them — the `checkout_sessions` table, the UUID/capability token,
the state machine, the element model — is backend-neutral and Lunar-owned. Two mandatory verbs:

| Verb | Responsibility |
|------|----------------|
| `createSession($source): CheckoutSession` | **Ingest** an arbitrary source cart → a session. Reads whatever the backend calls a cart (a Lunar `Cart`; or a Statamic simple-product basket; a custom cart — the source type is the driver's concern), produces the **pinned context** (currency, channel, locale) + the initial synced totals (`amount_subtotal`/`amount_total` in minor units) + `cart_fingerprint`, stores the opaque `cart_reference` (no clone, no frozen-line copy — [[0010-cart-session-reconciliation]]), and returns the `uuid`. The default impl is §B |
| `complete(CheckoutSession $session): mixed` | **Finalise** a session → an order. Re-verifies the pinned fingerprint ([[0010-cart-session-reconciliation]] §E.2), creates the order in whatever system the backend owns, and links it via the driver-opaque `order_reference` (§A). MUST be **idempotent keyed on the session `uuid`** (create-or-fetch) — a non-Lunar driver cannot share a transaction with a remote order system ([[0010-cart-session-reconciliation]] §E.2). For the Lunar driver this is §E (Lunar `Order` + `AwaitingPayment → InProcess`); a non-Lunar driver defines its own order creation |

**Why driver-based.** The pinned snapshot is **backend-neutral** — minor-unit totals, a currency,
a locale, a content fingerprint — so the session, element model, payment layer, and hosted page behave
identically whether the source is a Lunar cart or a Statamic/headless basket. The driver is the
*only* place that knows the source-cart shape and the destination-order shape; the session is the
neutral contract between them. This is what lets `lunar/checkout` front a **non-Lunar** commerce
backend without the rest of the spec set knowing.

**Resolution & swap (Lunar conventions).** The active driver is selected **by name** from
`config('lunar.checkout.driver')` — a *value*, the standard Laravel Manager pattern, not a
`config(...class)` class-swap (which the conventions forbid). A thin `CheckoutSessionManager`
resolver (mirroring the `Payments` manager) resolves the named driver and lets packages register
their own via `extend('statamic', fn () => …)`. Default driver name: `lunar`. The resolver is
**distinct from** the element `CheckoutManager` of [[0001-core-element-model]] — that one composes
elements; this one ingests carts and finalises orders. Bind the contract + manager in `register()`
(Octane-safe, no runtime rebind).

**What the driver does *not* get to redefine** (universal, Lunar-owned):

- The `checkout_sessions` table + UUID capability token (§A, §F).
- The session state machine Open/PaymentProcessing/Completed/Expired/Cancelled (§C) — it tracks the
  **checkout attempt**, independent of any order system.
- The element model + DTO (§D). Element `store()` *persistence* is driver-mediated only where it
  touches the backend cart: the Lunar driver routes through `Cart::setShippingAddress()` /
  `setShippingOption()` (their validators + `recalculate()`); another driver persists its own way
  behind the same element contract.

**OrderState coupling is the Lunar driver's, not universal.** Cross-cutting decision #3 (payment
success = the order's `AwaitingPayment → InProcess` transition + `transactions`) describes
`LunarCheckoutDriver::complete()`. A non-Lunar driver owns its own order semantics; the session only
records *"this checkout produced an order"* via the driver-opaque `order_reference` and its own `Completed` state.

### B. Creation and the live-cart reference

This is the **default `LunarCheckoutDriver`'s `createSession`** — the Lunar-cart ingest path. A
`CreateCheckoutSession` action (DI'd, `execute()`-only, spec 0016) takes the live Lunar cart +
`StorefrontSession` and:

1. Pins `channel_handle` + `currency_code` + `locale` from `StorefrontSession`, and writes the
   initial synced `amount_subtotal` / `amount_total` + `cart_fingerprint` from the cart's
   computed totals ([[0010-cart-session-reconciliation]] §A/§D).
2. Stores the **opaque `cart_reference`** to the live source cart — no clone, no frozen-line
   copy ([[0010-cart-session-reconciliation]]): the session mirrors the live cart while `Open`,
   and the pay-boundary gate + completion re-verify
   ([[0010-cart-session-reconciliation]] §E/§E.2) carry the amount-integrity guarantee that
   creation-time copies carried in the first draft.
3. **Enforces one active session per cart** ([[0010-cart-session-reconciliation]] §F.2): an
   existing `Open` session for the cart is superseded (void-first when it holds an advisory
   intent; `→ Cancelled`, `CheckoutSessionSuperseded`); a `PaymentProcessing` sibling refuses
   creation (`409` — with a `stalled` reason code once reconciliation has stalled, until a
   sanctioned stall exit applies). The mechanism is the unique `active_cart_reference` column +
   guarded supersede-then-insert — never a partial unique index (MySQL has none).
4. Pins `customer_reference` (if the cart is associated) and `customer_email` (if known), for
   guest resume and order notifications.
5. Sets `expires_at` from a configurable default window (24h), and (if hosted)
   `success_url`/`cancel_url`. Echoes any caller-supplied `client_reference_id` + `metadata`.
6. Returns the `uuid`.

**Why a live reference rather than a frozen copy:** the address/shipping/billing data the
checkout collects still flows through the existing cart write path — the driver's store verbs
wrap `Cart::setShippingAddress()` / `setShippingOption()`, which run their validators and
`recalculate()` ([[0010-cart-session-reconciliation]] §B, [[0001-core-element-model]] §D) — so
tax/total computation stays correct as checkout progresses, and legitimate price changes
(coupon, donation, shipping) are absorbed rather than bouncing the customer out. The pinned
currency/channel come from the **session**, overriding live `StorefrontSession`; a divergence of
the live cart's currency/channel from the pinned values invalidates the session
([[0010-cart-session-reconciliation]] §D).

### C. State machine

A spatie machine under `States/CheckoutSession/`, configured via a `CheckoutSessionStateConfig`
contract (the single seam, mirroring `OrderStateConfig`; bound in `register()`, Octane-safe, no
runtime rebind):

| State | Meaning |
|-------|---------|
| `Open` | default; elements editable, stores allowed |
| `PaymentProcessing` | an **async gateway confirmation** is in flight — **the freeze guard**: element stores are rejected. Entered **only** by methods that need a pre-confirmation intent (§ below); synchronous methods never enter it |
| `Completed` | order created; terminal |
| `Expired` | passed `expires_at` without completing; terminal |
| `Cancelled` | abandoned/cancelled; terminal |

Transitions:

| From | To |
|------|----|
| `Open` | `Completed` (**synchronous** completion — offline / on-account / merchant-marks-paid / zero-total), `PaymentProcessing` (**async** completion — methods needing a pre-confirmation intent, including authorize-now-capture-later), `Cancelled`, `Expired` |
| `PaymentProcessing` | `Completed`, `Open` (on failure, to retry — only after a gateway-confirmed void/refund; the return clears `payment_intent_ref`/`payment_processing_at` and re-arms `expires_at`, [[0010-cart-session-reconciliation]] §E.2), `Cancelled` |
| `Completed` / `Expired` / `Cancelled` | — (terminal) |

`Completed` is reachable from **`Open` or `PaymentProcessing`** — spatie expresses this as a
transition allowed from multiple source states (`allowTransition([Open::class,
PaymentProcessing::class], Completed::class)`), not a session holding two states at once (a model is
always in exactly one state).

**Legality vs concurrency.** The spatie machine is the declarative *legality* layer only —
`transitionTo()` validates the transition in memory and then plain-saves; it carries no concurrency
predicate. Concurrency comes from a **guarded-transition helper**: `UPDATE checkout_sessions SET
status = ?, … WHERE id = ? AND status IN (<allowed sources>)`; 0 affected rows means the call-site
lost the race — re-read and resolve per call-site (return the existing order, `409`, or no-op),
then refresh the model. **Every** transition call-site — the pay gate, `complete()`, expiry,
supersede, invalidation, reconciliation — goes through it. The `ExpireCheckoutSessions` command as
built (select-then-`transitionTo`) can expire a session that entered `PaymentProcessing` between
the select and the save; it moves to the rework list (guarded helper behind a `scopeExpirable`
scope).

**Which path a checkout takes is the method/driver's choice, not the session's.** A `PaymentMethod`
that needs an async pre-confirmation intent (Stripe card, redirect/3DS gateways) routes
`Open → PaymentProcessing → Completed` so the freeze guard protects the in-flight window.
**Authorize-now-capture-later is async**: an authorization is in flight — it gets an intent ref,
the freeze, and the reconciliation sweep. A method that settles **synchronously** — only
merchant-marks-paid/offline settlement: Lunar's `OfflinePayment`, pay-on-account/invoice — has
nothing in flight to protect and routes `Open → Completed` directly; it never enters
`PaymentProcessing`. The branch keys off the **same capability flag** the open question in
[[0002-payment-methods-and-driver]] (§A / Open questions) proposes: `requiresIntent() ⇒
PaymentProcessing`, else direct — and `amount_total == 0` **forces** the synchronous path
regardless of `requiresIntent()`. The synchronous path is never a gate bypass: the **same**
pay-boundary gate (confirmation-token verify, pinned currency/channel check, pin) runs **inside
`complete()`**, in the order-creation transaction, under the consistent-read protocol — one
transaction: verify → pin → Gate 2 → order → guarded `Open → Completed`
([[0010-cart-session-reconciliation]] §E/§E.2). **On-account specifically:** the session reaches `Completed` the
moment the order is created; settlement (the invoice paid later) is recorded as an offline
`Transaction` on the **order**, not tracked on the session — the session answers "did this checkout
produce an order?", not "is it paid" (there is no `paid` state, [[0002-payment-methods-and-driver]] §D).

A scheduled job transitions stale **`Open`** sessions to `Expired`; a stale **`PaymentProcessing`**
session is *not* blindly expired (payment may have succeeded) — it is **force-reconciled on a
bounded schedule** ([[0010-cart-session-reconciliation]] §F): the gateway driver's intent lookup
resolves it to `Completed` (after the completion re-verify,
[[0010-cart-session-reconciliation]] §E.2), to `Open` on failure/void, or — captured but no
longer completable — refund + `Open` (each return `→ Open` only after the gateway confirms the
void/refund); exhausted attempts fire `CheckoutSessionReconciliationStalled` **once** and drop
the session to a low-frequency retry tier with sanctioned operator exits
([[0010-cart-session-reconciliation]] §F) rather than waiting forever. The freeze that the prototype
put on the cart lives here instead — there is no cart `status` column.

### D. Pinned context feeds the element model

`CheckoutContext` ([[0001-core-element-model]] §C) is **resolved from the session** when one
exists: channel/currency from the session's pinned values (not live `StorefrontSession`), and
cart reads/writes routed **through the active driver** against the opaque `cart_reference`
([[0010-cart-session-reconciliation]] §B). Element `store()` lands on the context's typed write
verbs ([[0001-core-element-model]] §D): the session-backed flow delegates them to the driver's
store verbs (which re-sync the session), the session-less embedded flow routes them straight to
the Lunar cart. So the same element model serves both flows with no element change — and no
element ever touches a backend cart model directly.

### E. Completion

This is the **default `LunarCheckoutDriver`'s `complete`**. On confirmed payment
([[0002-payment-methods-and-driver]]): the session transitions `PaymentProcessing → Completed`, a
Lunar order is created at `AwaitingPayment` (then `→ InProcess` + `placed_at` per 0002), and
`order_reference` is written. (A non-Lunar driver reaches `Completed` the same way but creates its
own order — §"The checkout driver".)

Before the order exists, `complete()` **re-verifies** the live cart against the session's pinned
fingerprint inside the order-creation transaction; a mismatch or creation failure resolves under
the **refund invariant** — no charge survives without an order
([[0010-cart-session-reconciliation]] §E.2).

Completion is **idempotent and guarded**: the `→ Completed` transition is an atomic guarded write
(`WHERE status IN (open, payment_processing)` semantics), so a double callback, a
webhook+redirect race, or a double-submitted **synchronous** completion (offline / zero-total —
no intent ref to key on) produces exactly one order; the loser reads the completed session and
returns the existing order. The gateway's idempotency + `payment_intent_ref` cover the async
side. `payment_intent_ref` is written when the intent is **created** — by default at the pay
boundary inside the pin UPDATE, earlier (advisory) only for gateways that opt in to a
while-`Open` mounted payment UI ([[0010-cart-session-reconciliation]] §E) — the gateway-agnostic
handle the reconciliation backstop matches a late webhook to
([[0002-payment-methods-and-driver]] §E); the driver still owns the intent's lifecycle. A
gateway success event resolving to a session **not** in `PaymentProcessing`, or to an intent
that is not the session's current `payment_intent_ref`, is **refunded — never completed**
([[0010-cart-session-reconciliation]] §E.2). The guarded `→ Completed` writes `order_reference`
in the same statement, after the order exists; a non-Lunar `complete()` is idempotent keyed on
the session `uuid` (create-or-fetch). The session is the natural idempotency anchor the
prototype lacked.

### F. UUID as a capability token (security)

The `uuid` grants access to the hosted checkout and to session reads/writes without a logged-in
user (guest checkout), so it is a bearer capability:

- Unguessable (UUIDv4 or random ULID), never sequential, never derived from `cart_reference`.
- Enforced `expires_at`; an expired/terminal session is not operable (the hosted page returns a
  terminal view, the API a `410`/`404`).
- Rate-limited reads/writes; not enumerable.
- `success_url`/`cancel_url` validated against an allowlist (host app config) to prevent open
  redirects.

### G. Relationship to the deferred Cart state machine

This partially fills the gap spec 0021 left: in-progress *checkout* lifecycle now has a real home
(the session machine), so a Cart `Active/Abandoned/Converted` machine is no longer urgent. The
session machine and `OrderState` coordinate at completion; the cart machine stays a separate
follow-up.

## Alternatives considered

- **Keep checkout state derived (no persisted session).** Rejected: you cannot host, resume, or
  pin amounts off a derived read-model; this is the whole reason for the concept.
- **Put the lifecycle on a Cart state machine instead.** Rejected: the cart is the editable
  basket; a checkout *attempt* is the right grain, is Stripe-aligned, and 0021 deferred the cart
  machine anyway. Cart 1:N sessions retains per-attempt history.
- **Session references the live cart with no integrity machinery.** Rejected: a raw live
  reference reintroduces amount/total drift. The adopted design
  ([[0010-cart-session-reconciliation]]) keeps the live reference but adds the
  fingerprint-reconciled sync plus the pay-boundary and completion gates — delivering the
  integrity a frozen snapshot bought, without a second cart that drifts from the real one.
- **Session-owned address/shipping columns.** Rejected: would re-implement the cart
  write/validate/recalculate pipeline and create a second source of truth that drifts; the driver
  store verbs reuse the cart's own pipeline instead ([[0010-cart-session-reconciliation]] §B).

## Migration impact

- New `checkout_sessions` table (with a driver-opaque `order_reference`, not an order FK or
  morph) + `States/CheckoutSession/` folder + `CheckoutSessionStateConfig` contract.
- New `CheckoutDriver` contract + default `LunarCheckoutDriver` + a `CheckoutSessionManager`
  resolver (`config('lunar.checkout.driver')` default `lunar`); all new public surface (future breaking
  changes need a Rector rule per the package rule).
- Reconciles `draft-storefront-api.md`: `POST /checkout` becomes "create a checkout session" and
  returns the session (uuid + DTO); `GET /checkout/{uuid}` reads it. The cart→order-direct shape
  in the draft is superseded by cart→session→order.
- Depends on the existing cart pipeline, `StorefrontSession`, and the `OrderState` machine; adds a
  scheduled expiry + bounded-reconciliation job ([[0010-cart-session-reconciliation]] §F) and a
  **retention prune**: terminal sessions past a configurable window (default 90 days) have
  `element_data`, `customer_email`, and `meta` PII purged — anchored on `completed_at` /
  `cancelled_at` / `expires_at`, marked via `pruned_at`; a stalled session is pruned only after
  it reaches a terminal state through a sanctioned stall exit
  ([[0010-cart-session-reconciliation]] §F).
- 16-locale keys for `CheckoutSessionState` labels and hosted terminal views.

## Acceptance checks

- `CreateCheckoutSession` pins channel/currency/locale and writes the initial synced totals +
  fingerprint; re-checkout supersedes a prior `Open` session for the same cart; creation is
  refused (`409`) while a sibling session is in `PaymentProcessing`
  ([[0010-cart-session-reconciliation]] §F.2).
- State transitions are guarded (illegal transitions throw); a stale session expires via the job;
  `PaymentProcessing` rejects element stores.
- Completion creates exactly one order even under duplicate callbacks **and double-submitted
  synchronous completions** (atomic guarded transition), re-verifies the pinned fingerprint
  before the order insert, writes `order_reference`, and (Lunar driver) transitions the order
  `AwaitingPayment → InProcess`; a post-capture mismatch or creation failure refunds and returns
  the session to `Open` ([[0010-cart-session-reconciliation]] §E.2).
- The active driver resolves by name from `config('lunar.checkout.driver')` (default `lunar`); a
  package-registered driver can `createSession` from a non-Lunar source cart and `complete` to its
  own order model, with the session, UUID, and state machine behaving identically.
- Payment integrity verifies against the session's **pinned** currency/total, not the live cart.
- An expired/terminal session is not operable; `uuid` is unguessable and non-enumerable;
  `success_url`/`cancel_url` outside the allowlist are rejected.
- A pinned currency/channel divergence at pay is rejected (`409`) with a void-first invalidation
  (gate step 0); a render only surfaces the divergence and writes nothing
  ([[0010-cart-session-reconciliation]] §E).
- A zero-total or offline (synchronous) completion runs the full pay-boundary gate inside
  `complete()`; `amount_total == 0` forces the synchronous path regardless of `requiresIntent()`.
- Every transition call-site uses the guarded helper: the expiry command cannot expire a session
  that concurrently entered `PaymentProcessing`; a store-verb re-sync racing the pay pin affects
  zero rows and is dropped; superseding or expiring an `Open` session that holds an advisory
  intent voids the intent first ([[0010-cart-session-reconciliation]] §D/§F).
- `active_cart_reference` is set `NULL` in the same UPDATE as every terminal transition;
  concurrent first-creates for the same cart yield exactly one session (unique index).
- Completion verifies and builds the order from one consistent locked read; a
  completion-boundary refund carries an intent-derived idempotency key, and `→ Open` happens
  only after the gateway confirms the refund/void ([[0010-cart-session-reconciliation]] §E.2).
- A stalled `PaymentProcessing` session fires `CheckoutSessionReconciliationStalled` once,
  refuses re-checkout with a `stalled` reason code, and exits only via a sanctioned stall exit
  ([[0010-cart-session-reconciliation]] §F).
- `ArchitectureTest`: the state classes extend the abstract base + declare `$name`; the action
  implements a contract + `execute()` + no facade imports. PHPStan L0 + Pint pass.

## Open questions

- ~~**Clone vs claim** the checkout-scoped cart.~~ **Resolved ([[0010-cart-session-reconciliation]]):**
  neither — the session references the live source cart by an opaque `cart_reference` (no clone) and
  reconciles drift via a fingerprint rather than isolating a frozen-line cart.
- Whether quantity edits are allowed on an open session (Stripe: no) or a limited set is. **Largely
  resolved ([[0010-cart-session-reconciliation]] §E):** out-of-band line/qty edits make the session
  stale and require acknowledge-and-re-sync (or a new session); the exact in-checkout edit policy is
  deferred to the element/UI work. (Owner: design.)
- ~~Guest → authenticated association of a session (carry uuid through login).~~ **Resolved
  ([[0010-cart-session-reconciliation]] §B/§D):** the driver's `associateCustomer()` verb,
  triggered by a **host-app login listener**; deliberately fingerprint-neutral — a guest→customer
  association never invalidates a session or flips the confirmation token.
- `success_url`/`cancel_url` allowlist source (host config vs channel). (Owner: package maintainer.)
- **Expiry recovery** (Stripe's `after_expiration.recovery`): on `Expired`, optionally mint a fresh
  session + a recovery link (e.g. for an abandoned-checkout email) rather than dead-ending. Forward
  seam — model now (nullable recovery columns) or defer entirely? (Owner: design.)
- **`mode` seam** (Stripe `payment`/`setup`/`subscription`): v2 is payment-only; whether to reserve a
  `mode` column now for a future setup/subscription checkout or add it when subscriptions land.
  (Owner: design.)
- ~~**Driver capability surface beyond ingest+finalise.**~~ **Resolved
  ([[0010-cart-session-reconciliation]] §B):** the `CheckoutDriver` surface is defined — lifecycle
  (`createSession`/`complete`), reconciliation reads (`snapshot`/`fingerprint`/`assertReadyForPayment`),
  typed store verbs (`storeShippingAddress`/`storeBillingAddress`/`setShippingOption`/`associateCustomer`)
  and read verbs. Custom-element persistence is **not** a driver concern — the element owns it via the
  session element bag (§C). (Contract segregation **resolved**: one interface; third-party drivers
  extend the mandatory `AbstractCheckoutDriver` base class — [[0010-cart-session-reconciliation]] §B.)
- ~~**Snapshot ingest contract for a non-Lunar source.**~~ **Resolved
  ([[0010-cart-session-reconciliation]] §B):** the neutral `CartSnapshot` DTO (`amountSubtotal`,
  `amountTotal`, `currencyCode`, `channelHandle`, `fingerprint`, `hasAppliedDiscount`, `couponCode`)
  under `src/DataObjects/` (`Lunar\Checkout\DataObjects\…`) is the shape every driver maps its source
  cart onto.

## References

- [[0000-overview]], [[0001-core-element-model]], [[0002-payment-methods-and-driver]],
  [[0003-transport-projections]], [[0005-hosted-checkout]]
- [[0021-state-machines]] — `OrderStateConfig` seam pattern + Octane rule the session machine mirrors.
- `draft-storefront-api.md` — `POST /checkout` reconciled to session creation.
- `Lunar\Core\Contracts\StorefrontSession`, `CartSession`; `Models\Cart`; `States\Order\OrderState`.
- The `Payments` manager / `AbstractPayment` registry — the by-name, `extend()`-able Manager pattern
  the `CheckoutSessionManager` driver resolver mirrors ([[0002-payment-methods-and-driver]]).
- Stripe Checkout Session object (https://docs.stripe.com/api/checkout/sessions/object) — shape
  inspiration (§A.1); pinned totals, `expires_at`, return URLs, `client_reference_id`, `metadata`,
  `customer_email`, `locale`, expiry recovery. Lunar omits `payment_status` and `mode` by design.
