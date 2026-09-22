# 0077 — Pre-order payment intent capabilities

- Status: accepted
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
intent, an order, a session. That object has a lifecycle of its own, entirely
before any `Order` or `Transaction` row is written:

1. It is created when the payment form mounts.
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

Four opt-in capability interfaces in `Lunar\Core\Contracts`, plus the two data
objects and two enums they speak in. Nothing is added to `PaymentType`, and the
only existing file touched is `PaymentRefund`, which gains one trailing
optional field, so every current driver keeps working untouched.

Capabilities are discovered with `instanceof`, matching how core already treats
optional driver features. A driver implements the ones its gateway supports and
a consumer degrades gracefully for the rest.

### Why capabilities and not one interface

The concerns are separable, and real gateways support different subsets. Three
stand alone; holds are the one genuine dependency, since a hold that cannot be
voided is not a hold anyone should offer:

| Capability | A driver has it when the gateway can... |
| --- | --- |
| `CreatesPaymentIntents` | pre-create a confirmable object for a cart |
| `SyncsPaymentIntents` | carry an amount that can go stale and be corrected |
| `SupportsPaymentIntents` | report, void and refund that object by reference |
| `SupportsPaymentHolds` | authorise now and capture later, at a lower amount (extends `SupportsPaymentIntents`) |

A synchronous redirect gateway has none of them. An offline or on-account
method has none of them and needs none. Collapsing them into one interface
would force stub methods that lie about what the gateway can do.

### `CreatesPaymentIntents`

```php
interface CreatesPaymentIntents
{
    public function createIntent(Cart $cart): PaymentIntentDescriptor;
}
```

Implementations are idempotent per cart: re-requesting while an intent is
still confirmable returns the existing intent, never a duplicate. A driver
without this capability is confirm-only.

### `SyncsPaymentIntents`

```php
interface SyncsPaymentIntents
{
    public function syncIntent(Cart $cart): void;
}
```

Brings the cart's confirmable intent in line with the cart's current payable
total, so the gateway confirms exactly the amount the customer was shown. A
no-op when the cart has no live intent. A driver without this capability is
assumed to derive the amount at confirmation time.

### `SupportsPaymentIntents`

```php
interface SupportsPaymentIntents
{
    public function fetchIntent(string $reference): PaymentIntentStatus;

    public function voidIntent(string $reference): void;

    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): PaymentRefund;
}
```

The reconciliation surface. `voidIntent()` aborts an in-flight, uncaptured
intent. `refundIntent()` refunds a captured intent before any order or
transaction exists; `$idempotencyKey` is derived from the intent reference so a
retry can never double-refund. A driver without this capability cannot be
reconciled, and a consumer must treat its in-flight payments as unresolved
rather than assume they were abandoned.

`fetchIntent()` never answers "don't know". A reference the gateway does not
recognise throws, the same as an unreachable gateway: the reference is one the
application stored itself, so the gateway having lost it is evidence something
is wrong, not an answer. Returning a nullable status or an `Unknown` case would
push every caller into guessing, and there is no safe direction to guess in.

`refundIntent()` returns the existing `PaymentRefund` data object rather than a
bare reference string, so a caller gets `success` and `message` alongside the
gateway's refund reference. Its `?Transaction $transaction` is always null
here, which the object already documents as "recorded, but not attributable":
`transactions.order_id` is non-nullable, and the whole premise of this call is
that no order was ever placed. See the `PaymentRefund` change under data
objects.

### `SupportsPaymentHolds`

```php
interface SupportsPaymentHolds extends SupportsPaymentIntents
{
    public function createHold(Cart $cart): PaymentIntentDescriptor;

    public function describeHold(string $reference): ?HoldDescription;

    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment;

    public function captureHold(string $reference, int $amountMinor): void;
}
```

Authorise-only flows. `createHold()` is idempotent per cart like
`createIntent()`. `describeHold()` always asks the gateway and never trusts a
client claim; it returns null when the reference is unknown or is not a hold.
`adjustHold()` reports whether the hold now covers a new total.
`captureHold()` captures at most the authorised figure and is idempotent per
reference.

`describeHold()` returning null where `fetchIntent()` throws is deliberate, not
an inconsistency. The reference `describeHold()` takes arrives from the client,
so "the gateway has never heard of this" is a routine answer to an unverified
claim. The reference `fetchIntent()` takes is one the application stored, so the
same condition means something has gone wrong.

### Unknown outcomes throw, and name what they throw

Five methods must never let an unknown outcome read as a settled one:
`fetchIntent()`, `voidIntent()`, `describeHold()`, `adjustHold()` and
`captureHold()`. A caller that sees `voidIntent()` return cleanly is entitled to
treat the money as released; one that sees `captureHold()` return cleanly is
entitled to hand the customer their order.

Saying so in prose is not a contract a caller can catch, so these methods throw
a named `Lunar\Core\Exceptions\PaymentIntentException` (extending
`LunarException`) and carry an `@throws` tag. A consumer catches that type
rather than `\Throwable`, and cannot confuse a gateway that went quiet with a
bug in its own code. `createIntent()`, `syncIntent()`, `createHold()` and
`refundIntent()` are deliberately left out: their failures are ordinary error
paths where the caller has somewhere to go, and `refundIntent()` already reports
failure through `PaymentRefund::$success`.

Releasing a hold is `SupportsPaymentIntents::voidIntent()`, which is why this
interface extends it rather than adding a release verb of its own. No gateway
can authorise a hold without also being able to void it, and the extension
makes that a type guarantee: a consumer that has checked
`instanceof SupportsPaymentHolds` can void without a second check. Left as
documentation, the money-back path is exactly where a driver implementing only
one of the two would fail, and failing there is the worst case there is.

### Data objects

Both are plain readonly containers in `Lunar\Core\DataObjects`, per the
`DataObjects/` folder responsibility.

```php
class PaymentIntentDescriptor
{
    public function __construct(
        public readonly string $reference,
        public readonly ?string $clientSecret = null,
    ) {}
}

class HoldDescription
{
    public function __construct(
        public readonly PaymentIntentStatus $status,
        public readonly int $amountMinor,
        public readonly ?string $walletLabel = null,
    ) {}
}
```

`reference` is what the backend reconciles by. `clientSecret` is what the
gateway's own frontend component confirms with, null for gateways with no
client-side step. `walletLabel` lets a consumer name the wallet a hold came
from ("authorised with Apple Pay") without knowing any gateway's wallet
taxonomy.

The existing `PaymentRefund` gains one optional field so it can carry what
`refundIntent()` needs to report:

```php
public function __construct(
    public bool $success = false,
    public ?string $message = null,
    public ?Transaction $transaction = null,
    public ?string $reference = null,   // new
) {}
```

A trailing optional parameter, so every existing construction site and every
third-party driver keeps working unchanged. The field is the gateway's own
refund reference, which a consumer needs for audit and support even when there
is no transaction row to attribute the refund to.

### Enums

```php
enum PaymentIntentStatus: string
{
    case Pending = 'pending';
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

`PaymentIntentStatus` is deliberately small: the five outcomes a consumer
branches on, not a mirror of any one gateway's status list. A driver maps its
own statuses onto these.

`HoldAdjustment::Ok` means the hold now covers the new total, whether it
already did or was incremented. `NeedsReauthorization` means this hold cannot
stretch and the customer must authorise again.

### Amounts

Every amount is an `int` in minor units, named `$amountMinor` at each call
site so there is no ambiguity at the boundary. Drivers scale through
`Currency::decimal_places` internally, per the payment-driver bar in
`CLAUDE.md`. No `PriceValue` in these signatures: the values cross a gateway
boundary where a bare minor-unit integer is what the wire protocol takes, and
the cart the amount came from is the consumer's to hold.

## Alternatives considered

**Add the methods to `PaymentType`.** Breaks every existing driver, including
third-party ones built against the current contract, and forces every gateway
to stub four concerns it may support none of. Rejected.

**A `config('lunar.payments.intents.*')` map of gateway to handler class.**
Directly against the CLAUDE.md rule that config is for values and the
container is for substitutions. Rejected.

**Put the contracts in `lunarphp/checkout`.** Tempting, since checkout is the
first consumer. Rejected because it inverts the dependency: `packages/stripe`
would have to depend on the checkout package to declare what Stripe can do,
and any other storefront wanting the same lifecycle would have to install
checkout to get the vocabulary. The capability of a payment driver is core's
business.

**One `SupportsPaymentLifecycle` interface with nullable returns for the
unsupported parts.** Makes "does not support holds" indistinguishable from
"hold lookup failed", which is exactly the distinction reconciliation needs.
Rejected.

**Do nothing.** Leaves every cart-phase payment flow written against a
specific driver's API, and leaves `lunarphp/checkout` unable to support a
second gateway without a code change in checkout itself.

## Migration impact

- **Database migrations:** none in core. Slice 2 adds a `flavour` column to
  the Stripe package's `stripe_payment_intents` baseline migration, folded
  into the existing baseline per the v2 alpha convention.
- **Breaking changes:** none. Nine of the ten files are new, `PaymentType` is
  untouched, and existing drivers keep working without implementing anything.
  The tenth, `PaymentRefund`, gains a trailing optional constructor parameter,
  which is non-breaking for callers and for third-party drivers that construct
  it. No Rector rule needed in the `upgrade` package.
- **Upgrade path for v1.x consumers:** not applicable. There is no v1
  equivalent to migrate from.
- **Translation / locale impact:** none. No user-facing strings. A driver's
  refusal reason is the driver's to word, not core's.
- **Filament / admin impact:** none. These are cart-phase seams; the admin
  works on orders and transactions.

## Open questions

- **Does PayPal get these capabilities in this spec's scope?** PayPal orders
  map onto `CreatesPaymentIntents` and `SupportsPaymentIntents` cleanly, but
  the driver was only just hardened (spec 0071) and holds have a different
  shape there. Resolution: out of scope, tracked as a follow-on. Owner: Alec
  Ritson.

## References

- `[[0070-first-party-payment-drivers]]` — the first-party driver bar these
  capabilities are implemented against.
- `[[0071-paypal-driver-hardening]]` — why PayPal is out of scope here.
- `[[0016-service-layer-di]]` — contracts in `Contracts/`,
  container over config for substitution.
- `lunarphp/checkout` specs 0002 (payment methods and driver) and 0012
  (express payments) are the first consumer of this surface, and live in that
  repository.

## Implementation plan

- [ ] Slice 1 — the core surface. Nine new files in `packages/core/src`:
      four contracts in `Contracts/`, `PaymentIntentDescriptor` and
      `HoldDescription` in `DataObjects/`, `PaymentIntentStatus` and
      `HoldAdjustment` in `Enums/`, `PaymentIntentException` in `Exceptions/`,
      plus a trailing optional `reference` field on the existing
      `PaymentRefund`.
- [ ] Slice 2 — Stripe implements all four. `StripePaymentType` declares the
      capabilities; `StripeManager` gains `createHold()` and flavour-keyed
      intent lookup so a standard intent and a hold can coexist on one cart,
      and corrects a locally-active record when the gateway reports the intent
      dead. Adds the `flavour` column, a `minimum_amounts` config key, and
      recorded hold responses under `packages/stripe/resources/responses/`
      per the driver bar.
