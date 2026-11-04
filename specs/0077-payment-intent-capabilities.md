# 0077 — Pre-order payment intent capabilities

- Status: proposed
- Author: Alec Ritson
- Created: 2026-09-22
- TODO item: Pre-order payment intent capabilities for cart-phase gateway flows

## Problem

Core's payment vocabulary starts at the order. `Lunar\Core\Contracts\PaymentType`
takes a cart only to set context; every verb that moves money is anchored to
something that already exists in the database:

- `authorize(): ?PaymentAuthorize` runs at order-placement time.
- `capture(Transaction $transaction, $amount = 0)` and
  `refund(Transaction $transaction, int $amount, $notes = null)` both take a
  `Transaction`, which belongs to an `Order`.

Modern gateways do not work that way. Stripe, PayPal, Adyen and Mollie all
create a *confirmable object* while the customer is still holding a cart: an
intent, an order, a session, a payment. That object has a lifecycle of its own,
entirely before any `Order` or `Transaction` row is written:

1. It is created when the payment form mounts, or when the customer is sent to
   a hosted payment page.
2. Its amount goes stale as the cart keeps changing (delivery selection,
   address-dependent rates, discounts).
3. It can be abandoned, in which case it must be voided or it holds a
   real authorisation against the customer's card.
4. It can succeed while the order placement that should follow it fails,
   leaving money taken against no order, which has to be refunded by gateway
   reference because there is no transaction to refund against.
5. Wallet flows (Apple Pay, Google Pay) invert the order of events: the wallet
   sheet authorises a hold up front, and capture happens later, after the
   customer confirms the final basket.

Core has no way to express any of that, so there is no way to ask a driver
whether it can do it. A consumer that needs the cart-phase lifecycle has to
reach into a concrete driver's API, which makes the flow gateway-shaped: it
works with Stripe because it was written against Stripe. That is the wrong
dependency direction. `lunarphp/checkout` (and any storefront doing its own
payment step) should be able to ask a driver what it supports and get an
answer in core's vocabulary.

The gap also forces per-gateway branching for a decision that is not
gateway-specific: a driver that cannot report an intent's outcome cannot be
reconciled at all, and the consumer needs to know that up front rather than
discover it at the point of failure.

## Proposal

Four opt-in capability interfaces in `Lunar\Core\Contracts`, two data objects
and two enums. `PaymentType` is untouched, and `PaymentRefund` gains two
trailing optional fields, so every current driver keeps working.

An intent has one of six statuses, and the lifecycle table below defines every
operation per status. The surface is `@experimental` until a second driver
implements it.

### Capabilities

| Capability | Extends | The gateway can... |
| --- | --- | --- |
| `ReconcilesPaymentIntents` | | report, void and refund an intent |
| `CreatesPaymentIntents` | `ReconcilesPaymentIntents` | pre-create an intent for a cart |
| `SyncsPaymentIntents` | `CreatesPaymentIntents` | change the intent's amount as the cart changes |
| `SupportsPaymentHolds` | `ReconcilesPaymentIntents` | authorise now and capture later, confirming cancel and capture synchronously |

Capabilities are discovered with `instanceof`. A driver with none of them keeps
the existing `authorize()` flow.

Holds need synchronous cancel and capture because re-authorising a short hold
voids it and creates another: with an asynchronous cancel the customer briefly
carries two authorisations, and an asynchronous capture cannot report that
money was taken.

### Vocabulary

**Intent.** The gateway's confirmable object, whatever the gateway calls it.

**Reference.** An opaque, driver-owned string for one intent. Core and
consumers store it and pass it back, never parse it.

**Flavour.** A cart has at most one live intent of each flavour: a standard
intent from `createIntent()` and a hold from `createHold()`. Live means
`RequiresAction`, `Processing` or `RequiresCapture`.

**Driver record.** Each driver keeps its own per-cart record of the intents it
created, including the order once placed. Core does not provide one.

## Interfaces

```php
interface ReconcilesPaymentIntents
{
    public function fetchIntent(Cart $cart, string $reference): ?PaymentIntentSnapshot;

    public function voidIntent(Cart $cart, string $reference): void;

    public function refundIntent(Cart $cart, string $reference, PriceValue $amount, string $idempotencyKey): PaymentRefund;
}

interface CreatesPaymentIntents extends ReconcilesPaymentIntents
{
    public function createIntent(Cart $cart): PaymentIntentDescriptor;
}

interface SyncsPaymentIntents extends CreatesPaymentIntents
{
    public function syncIntent(Cart $cart): void;
}

interface SupportsPaymentHolds extends ReconcilesPaymentIntents
{
    public function createHold(Cart $cart): PaymentIntentDescriptor;

    public function adjustHold(Cart $cart, string $reference, PriceValue $amount): HoldAdjustment;

    public function captureHold(Cart $cart, string $reference, PriceValue $amount): void;
}
```

Every method throws `PaymentIntentException` when the gateway refuses or cannot
be reached, and wherever the table says "throw". Every method that takes a
reference also takes the cart, and the driver acts only on references it created
for that cart: `fetchIntent()` returns null for any other, and the rest throw.
An amount in a different currency from the intent throws.

`createIntent()` and `createHold()` are idempotent per cart and flavour, and a
retry after a lost response recovers the first intent rather than creating a
second. How the driver keys that is its own business. `syncIntent()` acts only
on the cart's live standard intent and is a no-op without one. `adjustHold()`
covers gateways that can raise an authorisation in place (Stripe's incremental
authorisation); where that is not possible it returns `NeedsReauthorization`.

## Lifecycle

| Status | create | sync | void | adjustHold | captureHold | refund | Placement |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `RequiresAction` | return it, re-priced if the cart's currency changed | update | cancel, giving `Voided` | throw | throw | throw | do not place |
| `Processing` | return it | throw | throw | throw | throw | throw | wait |
| `RequiresCapture` | return the hold; if the cart's currency changed, void it and create another | not applicable | cancel, giving `Voided` | `Ok` if covered or the increment is confirmed, else `NeedsReauthorization` | capture up to the authorised amount, giving `Captured` | throw | capture, then place |
| `Captured` | create another | no-op | throw | throw | no-op at the captured amount, else throw | up to captured less refunded | place if captured equals the cart total and nothing is refunded, else refund |
| `Voided` | create another | no-op | no-op | throw | throw | throw | do not place |
| `Failed` | create another | no-op | no-op | throw | throw | throw | do not place |

`Failed` is terminal: a retryable decline is `RequiresAction`. A gateway with
nothing to cancel for an unpaid intent (an unapproved PayPal order) marks it
`Voided` in its record; if the customer pays anyway, `fetchIntent()` reports
`Captured` and the consumer refunds it. On `NeedsReauthorization` the consumer
voids the hold, then calls `createHold()`.

## Placement

Placement is `authorize()`, given the stored reference:

```php
Payments::driver('stripe')->cart($cart)->withData(['reference' => $reference])->authorize();
```

For a driver with these capabilities, `authorize()` applies the table's
Placement column, writes the order and its `Transaction` from the intent
without moving money for a `Captured` one, and stores the order on its record.
It is idempotent: a repeat call for a placed order returns that order
successfully, so the consumer and the driver's webhook can both reach it.

## Data objects and enums

```php
class PaymentIntentDescriptor
{
    public function __construct(
        public readonly string $reference,
        public readonly ?string $redirectUrl = null,
        public readonly array $clientPayload = [],
    ) {}
}

class PaymentIntentSnapshot
{
    public function __construct(
        public readonly PaymentIntentStatus $status,
        public readonly PriceValue $amount,
        public readonly PriceValue $captured,
        public readonly PriceValue $refunded,
        public readonly ?string $walletLabel = null,
    ) {}
}

enum PaymentIntentStatus: string
{
    case RequiresAction = 'requires_action';
    case Processing = 'processing';
    case RequiresCapture = 'requires_capture';
    case Captured = 'captured';
    case Voided = 'voided';
    case Failed = 'failed';
}

enum HoldAdjustment: string
{
    case Ok = 'ok';
    case NeedsReauthorization = 'needs_reauthorization';
}
```

`redirectUrl` is set when the customer pays on a gateway-hosted page.
`clientPayload` is whatever the driver's frontend needs to confirm in-page
(Stripe's client secret), passed on unread. The snapshot's amounts are in the
intent's currency; `refunded` includes pending refunds. `walletLabel` names the
wallet that authorised the intent ("Apple Pay").

`PaymentRefund` gains `?string $reference` (the gateway's refund id) and
`bool $pending = false` (accepted, not yet settled).

## Gateway mapping

A check that the vocabulary is not Stripe's. Only Stripe is implemented here.

| | Stripe | PayPal (Orders v2) | Adyen (Checkout) | Mollie |
| --- | --- | --- | --- | --- |
| Create | PaymentIntent | Order | Session | Payment |
| Sync | Update amount | Patch the order | Not offered | Not offered |
| Void | Cancel | Unapproved order: local mark | Unpaid session: local mark | Cancel, or local mark |
| Refund | Refund, may be pending | Refund the capture | Refund, pending until webhook | Refund, may be pending |
| Holds | Manual capture | Order with intent `AUTHORIZE` | Not offered: asynchronous | Not offered: asynchronous captures |

## Alternatives considered

**Add the methods to `PaymentType`.** Breaks every existing driver. Rejected.

**One interface with nullable returns.** Makes "does not support holds"
indistinguishable from "lookup failed". Rejected.

**Contracts in `lunarphp/checkout`.** A driver would depend on checkout to
declare what it can do. Rejected.

## Migration impact

- **Database migrations:** none in core. Slice 2 adds `flavour` to the Stripe
  package's `stripe_payment_intents` baseline.
- **Breaking changes:** none. All new surface; `PaymentRefund` gains trailing
  optional parameters only.
- **Upgrade path for v1.x consumers:** not applicable.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none. Placement writes the usual `Transaction`.

## Out of scope

A reconciliation sweep for money taken against no order, a core event for late
gateway failures, and drivers beyond Stripe. Each is a follow-up spec.

## Open questions

- **When does `@experimental` come off?** When a second driver implements
  `CreatesPaymentIntents`. Owner: Alec Ritson.

## References

- `[[0070-first-party-payment-drivers]]`
- `[[0071-paypal-driver-hardening]]`
- `[[0016-service-layer-di]]`
- `lunarphp/checkout` specs 0002 and 0012, the first consumer.

## Implementation plan

- [ ] Slice 1: the four contracts, `PaymentIntentDescriptor`,
      `PaymentIntentSnapshot`, both enums, `PaymentIntentException`, and the
      two `PaymentRefund` fields.
- [ ] Slice 2: Stripe implements all four, with a test per table cell it
      reaches, and `authorize()` accepts `reference` and is idempotent.
