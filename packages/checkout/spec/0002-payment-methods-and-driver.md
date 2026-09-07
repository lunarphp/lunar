# 0002 — Checkout Elements: payment methods & driver

- Status: draft
- Author: Alec Ritson
- Created: 2026-06-05
- TODO item: "Checkout Elements — gateway-agnostic payment methods"

## Problem

A checkout needs payment, but Lunar must not assume a gateway. **Stripe is optional** — most
consumers bring their own gateway (or several), and `lunar/stripe` is one reference package, not
a dependency of core checkout. The reference design shows a payment region with method tabs
(card / wallet / BNPL) plus express wallet buttons; those methods may come from different
providers. The model must let any number of gateways register payment methods that the checkout
renders and that drive submission — with zero gateway-specific code in core, and no new payment
state machine.

## Proposal

A `PaymentMethod` registry layered over Lunar's **existing gateway-agnostic payment seam**
(`Payments` manager + `PaymentTypes\AbstractPayment`). The payment host element ([[0001-core-element-model]])
renders registered methods; the active method drives submission; integrity is owned by the
gateway driver.

### A. Reuse the existing payment seam — do not build a parallel one

Lunar already has a gateway registry: the `Payments` manager resolves a `PaymentTypes\AbstractPayment`
driver (`authorize()`, `capture(Transaction, $amount)`, `refund(Transaction, $amount, $notes)`).
This spec does **not** introduce a parallel `PaymentDriver` abstraction. A `PaymentMethod` is a
thin presentation+capability descriptor that **delegates to a registered `AbstractPayment`
driver**:

```
interface PaymentMethod {                       // Lunar\Checkout\Contracts\, no `Interface` suffix
    handle(): string;
    label(): string;                            // translation key
    icon(): string;
    driver(): string;                           // key of an AbstractPayment registered with Payments
    requiresIntent(): bool;                     // needs a pre-confirmation intent ⇒ session routes
                                                // Open → PaymentProcessing; false ⇒ synchronous
                                                // Open → Completed (0004 §C)
    component(): string;                         // inline Vue component (a hint)
    supportsExpress(): bool;                     // eligible for the express region
    expressComponent(): ?string;
}
```

`createIntent`/`confirm`/`capture`/`refund`/webhook handling map onto the existing
`AbstractPayment` verbs and the gateway package's own controllers/jobs. Where a gateway needs an
intent created before confirmation, that is the API's `POST /checkout/{uuid}/payment-intent`
([[0003-transport-projections]]) delegating to `Payments::driver($method->driver())`.

Path selection is by capability **and** amount: `amount_total == 0` forces the **synchronous**
path regardless of `requiresIntent()`; authorize-now-capture-later is **async** — an
authorization is in flight even though capture is deferred; only merchant-marks-paid / offline
methods are synchronous.

The existing verbs are not enough on their own. `PaymentTypes\AbstractPayment` exposes only
`authorize()` / `capture(Transaction, $amount)` / `refund(Transaction, $amount, $notes)` — all
keyed to an existing `Transaction` row — while checkout needs three **intent-keyed** operations:
fetch-intent, void-by-intent, and refund-by-intent-ref (pre-order, when no `Transaction` row
exists yet). This spec adds one **core capability interface**, opt-in via `instanceof` exactly
like the element capability interfaces ([[0001-core-element-model]] §B):

```php
namespace Lunar\Core\Contracts;

interface SupportsPaymentIntents
{
    public function fetchIntent(string $ref): PaymentIntentStatus; // enum: Pending, RequiresCapture,
                                                                   //       Captured, Voided, Failed
    public function voidIntent(string $ref): void;
    public function refundIntent(string $ref, int $amountMinor, string $idempotencyKey): string; // refund ref
}
```

A gateway that does not implement it **cannot be voided or reconciled**: its sessions stay in
`PaymentProcessing` and flow into the stall protocol ([[0010-cart-session-reconciliation]] §F).
The interface is also the test seam — the contract-test fake gateway implements it (§E,
acceptance checks).

### B. Registration

Methods register against the payment subsystem in a provider `boot()`, type-checked against
`PaymentMethod` at boot; registry keys are a fixed allowlist validated at boot (no dynamic
server-string → component resolution). Core checkout ships **no** method by default — a store
registers the methods for whichever gateway(s) it installs:

```php
// in a gateway package or the host app — NOT in lunar/core
Payment::registerMethod(StripeCardMethod::class);     // from an optional lunar/stripe
Payment::registerMethod(PayPalMethod::class);
```

With no method registered, the payment region renders empty (and Gate 2 reports the order not
yet placeable) — core remains gateway-free and Stripe-free.

### C. Host element, express region, submit seam

- The **payment host element** (placed `region(Main)->last()`) renders the registered methods as
  tabs. It is contributed by this spec, registered like any other element ([[0001-core-element-model]] §G),
  and is `required(false)` for tick purposes.
- The **express region** projects methods where `supportsExpress()` is true as wallet buttons —
  the same method objects, a different placement, declared (not a frontend special-case).
- **Submit** is transport-layer ([[0003-transport-projections]]): the active method's client
  component exposes a typed submit API; the generic Pay action invokes it. Core only declares
  which method is active-eligible.
- **The method's `component()` reaches the client as a contributed chunk, not a consumer rebuild.**
  The gateway package ships a prebuilt ES-module chunk that self-registers its Vue component into the
  checkout app's registry at runtime ([[0009-frontend-element-extension]]); `component()` is the join
  key. First-party gateways are prebuilt into the shipped app; third-party/bespoke gateways load via
  the runtime chunk — either way, no fork and no consumer build.

### D. Payment success is an OrderState transition, not a new state

There is **no `PaymentStatus` enum and no `paid` state** (spec 0021 deferred the payment/fulfilment
decomposition; payment progress is a rollup of `transactions`). On confirmed payment the
**checkout session** transitions `PaymentProcessing → Completed` ([[0004-checkout-session]] §E) and
the **checkout driver's `complete()`** creates the order. For the default `LunarCheckoutDriver` that
order transitions `AwaitingPayment → InProcess` with `placed_at` stamped; payment evidence is
recorded as `Transaction` rows by the gateway driver. Completion is idempotent off the session uuid.
A gateway needing a bespoke order state adds it via the `OrderStateConfig` seam (subclass + bind in
`register()`), never an enum or a new column.

> **Two distinct "drivers".** This spec's *gateway driver* (`PaymentTypes\AbstractPayment` resolved
> by the `Payments` manager) authorises/captures money. The *checkout driver* ([[0004-checkout-session]])
> turns a cart into a session and a session into an order. The OrderState transition above is the
> **Lunar** checkout driver's; a non-Lunar checkout driver owns its own order semantics, while still
> driving the same gateway drivers for payment.

### E. Integrity is driver-owned — reference, don't reinvent

Payment-integrity guarantees are the gateway driver's responsibility, with the order placement
re-verifying against the **session** total/currency before transition:

- Recompute the session cart's total and verify `intent.amount`/`currency` match before
  capture/transition; on mismatch, fail and void/refund — routed through
  `SupportsPaymentIntents` (§A).
- Idempotent webhook handling (atomic claim), transaction recording, and intent reconciliation —
  the fetch/void/refund-by-intent calls route through `SupportsPaymentIntents` (§A).
- **Advisory intents.** `payment_intent_ref` is recorded at intent **creation**. A gateway
  success event that resolves to a non-current intent, or to a session not in
  `PaymentProcessing`, is **refunded** (`refundIntent`) — it never completes. Terminalizing an
  `Open` session **voids its advisory intent first** (`voidIntent`)
  ([[0010-cart-session-reconciliation]] §E/§F).
- The webhook is a **reconciliation backstop over a shared idempotent `authorize()` path**, not
  a separate writer of success. The Lunar checkout driver runs that same `authorize()` itself
  when it completes a `PaymentProcessing` session (`->order($order)->withData(['payment_intent'
  => $ref])`), so the `Transaction` rows exist whether or not a webhook ever arrives; a webhook
  that lands afterwards finds the order placed and is a no-op. Completion never fails on a
  recording error: the order is placed and the failure is reported, because a captured charge
  without an order is the one outcome the boundary must not produce (2026-09-07, EDW2-161).
- **Void-first invalidation and the refund invariant.** When checkout invalidates a
  `PaymentProcessing` session ([[0010-cart-session-reconciliation]] §F) the gateway driver first
  attempts to abort the intent (`voidIntent`); an unabortable/captured intent resolves through the completion
  boundary — complete on fingerprint match, **refund otherwise**
  ([[0010-cart-session-reconciliation]] §E.2). A captured payment that produced no order is
  always refunded, and the backstop runs on a bounded schedule — never an indefinite wait.

The Stripe reference already implements all of this (amount/currency verification, atomic webhook
claim on `processing_at`/`event_id`, transaction storage, orphaned-intent detection). This spec
**references** that implementation as the worked example; it does not re-specify it, and it does
not make those behaviours Stripe-specific — each gateway driver provides them. Currency/total
come from the **checkout session's pinned values** — pinned at the pay boundary
([[0010-cart-session-reconciliation]] §E), not at session creation — never a live cart row, so
the verified amount cannot drift. The "freeze while confirming" guard is the
session's `PaymentProcessing` state (which rejects element stores), not a cart flag.

## Alternatives considered

- **A new `PaymentDriver` contract** (`createIntent`/`confirm`/`handleWebhook`). Rejected: Lunar
  already has `AbstractPayment` + the `Payments` registry; a parallel contract makes every
  gateway implement two abstractions. `PaymentMethod` is presentation+capability over the
  existing driver.
- **Stripe-coupled payment element** (the prototype). Rejected outright: Stripe is optional.
- **Re-specify integrity in checkout.** Rejected: integrity is gateway-specific and already
  implemented by the reference; duplicating it invites a divergent second implementation that
  fights the driver (double order creation, conflicting idempotency).
- **A `paid` order state / `PaymentStatus` machine.** Rejected: does not exist in v2 and was
  deliberately deferred; success is the `InProcess` transition + `transactions`.

## Migration impact

- New `Lunar\Checkout\Contracts\PaymentMethod` + a payment-method registry (in `lunar/checkout`,
  gateway-agnostic) and the payment host element. **No dependency on `lunar/stripe`.**
- New **core** capability contract `Lunar\Core\Contracts\SupportsPaymentIntents` + the
  `PaymentIntentStatus` enum (§A) — additive, opt-in via `instanceof`; existing gateways are
  unaffected until they adopt it.
- `lunar/stripe` (optional) gains a `StripeCardMethod implements PaymentMethod` + a prebuilt
  checkout chunk contributed via `CheckoutAssets` ([[0009-frontend-element-extension]]); its
  existing payment type/driver/webhook are reused unchanged.
- No new lifecycle column or enum; no schema change.
- 16-locale keys for method labels.

## Acceptance checks

- With **no** gateway registered, core checkout boots, the payment region is empty, and Gate 2
  reports not-placeable — with no reference to Stripe anywhere in `lunar/core`.
- A registered `PaymentMethod` appears as a tab; `supportsExpress()` methods appear in the
  express region.
- A payment-method **contract test** fixture each gateway runs to prove the seam (method resolves
  a registered `AbstractPayment` driver; submit → `authorize()` → `AwaitingPayment → InProcess` +
  `placed_at` + `Transaction` rows). The fixture's **fake gateway implements
  `SupportsPaymentIntents`** (§A) so the intent-keyed verbs are exercised end-to-end.
- A gateway success event for a **non-current intent**, or for a session **not in
  `PaymentProcessing`**, is refunded via `refundIntent` and never completes the session (§E).
- Terminalizing an `Open` session **voids its advisory intent first** via `voidIntent` (§E,
  [[0010-cart-session-reconciliation]] §E/§F).
- `ArchitectureTest`/PHPStan/Pint pass; no service-facade imports in any new `Actions/`.

## Open questions

- Whether multiple express methods can be simultaneously eligible and how they order. (Owner:
  design.)
- Saved/tokenized methods beyond express wallets — deferred.
- ~~Whether `PaymentMethod` should expose a capability flag for "needs a pre-confirmation
  intent".~~ **Resolved:** `requiresIntent(): bool` is on the interface (§A). It does double
  duty: (a) it tells the API when to expose `POST /checkout/{uuid}/payment-intent`, and (b) it
  selects the **session completion path** — `true ⇒ Open → PaymentProcessing → Completed`, else
  synchronous `Open → Completed` (offline / on-account / zero-total;
  [[0004-checkout-session]] §C).

## References

- [[0000-overview]], [[0001-core-element-model]], [[0003-transport-projections]],
  [[0009-frontend-element-extension]] — how a method's `component()` is delivered as a runtime chunk.
- [[0021-state-machines]] — single `OrderState`, `OrderStateConfig` seam, no payment-status machine.
- `Lunar\Core\PaymentTypes\AbstractPayment`, the `Payments` facade/manager,
  `Lunar\Core\Events\OrderStatusUpdated`, `PaymentAttemptEvent`.
- `lunar/stripe` — optional reference: payment type, webhook controller/job, transaction storage,
  orphaned-intent detection.
