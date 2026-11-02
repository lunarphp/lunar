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

Four opt-in capability interfaces in `Lunar\Core\Contracts`, plus the three
data objects and two enums they speak in. Nothing is added to `PaymentType`,
and the only existing file touched is `PaymentRefund`, which gains two trailing
optional fields, so every current driver keeps working untouched.

Capabilities are discovered with `instanceof`, matching how core already treats
optional driver features. A driver implements the ones its gateway supports and
a consumer degrades gracefully for the rest.

All four interfaces, and the data objects and enums below, carry an
`@experimental` tag until a second driver besides Stripe implements them. Until
then their shape may change in a minor release. The mapping table below is the
check that the vocabulary is not Stripe's, but a table is not an
implementation.

### Why capabilities and not one interface

The concerns are separable, and real gateways support different subsets. Three
stand alone; holds are the one genuine dependency, since a hold that cannot be
voided is not a hold anyone should offer:

| Capability | A driver has it when the gateway can... |
| --- | --- |
| `CreatesPaymentIntents` | pre-create a confirmable object for a cart |
| `SyncsPaymentIntents` | change that object's amount in place as the cart changes |
| `ReconcilesPaymentIntents` | report, void and refund that object by reference |
| `SupportsPaymentHolds` | authorise now and capture later, at a lower amount (extends `ReconcilesPaymentIntents`) |

A synchronous redirect gateway has none of them. An offline or on-account
method has none of them and needs none. Collapsing them into one interface
would force stub methods that lie about what the gateway can do.

### Vocabulary

**Intent.** The gateway's confirmable object, whatever the gateway calls it.
Core uses "intent" as its own word for the cart-phase object, not as a
reference to Stripe's PaymentIntent.

**Reference.** An opaque, driver-owned string that identifies one intent for
its whole lifecycle. Core and consumers store it and pass it back; they never
parse it. A gateway that issues several ids across the lifecycle (PayPal's
order, authorisation and capture ids) is the driver's problem: it maps from the
reference to whichever id the next call needs, from its own record.

**Flavour.** A cart has at most one live intent of each flavour: a *standard*
intent from `createIntent()`, and a *hold* from `createHold()`. The two are
separate objects with separate references and can be live on the same cart at
once, for example when a customer opens a wallet sheet and then switches to
card entry.

**Driver record.** Every driver that implements these capabilities keeps its
own per-cart record of the intents it created (reference, flavour, cart,
currency, idempotency key). Core does not provide one: what needs storing is
gateway-specific. The per-cart idempotency of `createIntent()` and
`createHold()` and the ownership check in `describeHold()` both rest on it.

### `CreatesPaymentIntents`

```php
interface CreatesPaymentIntents
{
    /** @throws PaymentIntentException */
    public function createIntent(Cart $cart): PaymentIntentDescriptor;
}
```

Idempotent per cart and per flavour: re-requesting while the cart's standard
intent is still confirmable returns that intent, never a duplicate. A driver
without this capability is confirm-only.

Idempotency has to survive a lost response, not only a repeat request. A create
call can succeed at the gateway and time out on the way back, which leaves no
stored reference and, on gateways that authorise at creation, a live
authorisation. So before the first create call for a cart and flavour, a driver
persists an idempotency key in its driver record together with the parameters
it is about to send, and every retry replays those frozen parameters with that
key. A retry after a timeout then recovers the object the first attempt made,
even if the cart has changed since; the driver syncs it to the current total
afterwards, or voids and re-creates it where the gateway cannot sync.

A gateway keeps an idempotency key only for a limited time (Stripe about 24
hours). A retry past that window must not trust the key: the driver first looks
the object up by the cart reference it attached as gateway metadata, and only
creates when nothing is found. On a gateway where an unconfirmed object holds no
money (Stripe authorises only at confirmation), a driver may instead derive the
key from the request itself, so a changed cart or an expired key yields a new
object and the first is left as a harmless, unconfirmable orphan.

The key is replaced once its intent stops being confirmable, whatever the
reason: `Captured` (including captured and then refunded), `Voided` or
`Failed`. `createIntent()` and `createHold()` never send a key whose intent has
reached one of those statuses, so a customer whose payment was voided or
refunded can pay again on the same cart.

### `SyncsPaymentIntents`

```php
interface SyncsPaymentIntents
{
    /** @throws PaymentIntentException */
    public function syncIntent(Cart $cart): void;
}
```

Brings the cart's live standard intent in line with the cart's current payable
total, so the gateway confirms exactly the amount the customer was shown. A
no-op when the cart has no live standard intent. It never touches a hold: a
hold's amount moves through `adjustHold()`, which can report that it cannot
stretch.

A consumer must not let the customer confirm a standard intent until the most
recent `syncIntent()` has returned cleanly. If the client confirms directly with
the gateway, a sync that failed silently means the stale amount is charged
before any server-side check runs.

A driver without this capability is one whose gateway fixes the amount at
creation (Mollie, Adyen sessions). Its consumer creates the intent at the point
the cart is final, or voids and re-creates it when the total changes.

### `ReconcilesPaymentIntents`

```php
interface ReconcilesPaymentIntents
{
    /** @throws PaymentIntentException */
    public function fetchIntent(string $reference): PaymentIntentSnapshot;

    /** @throws PaymentIntentException */
    public function voidIntent(string $reference): void;

    /** @throws PaymentIntentException */
    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): PaymentRefund;
}
```

The reconciliation surface, for either flavour. A driver without this
capability cannot be reconciled, and a consumer must treat its in-flight
payments as unresolved rather than assume they were abandoned.

**`fetchIntent()`** reports the intent's status and its money: what it is for,
what has been captured and what has been refunded (see
`PaymentIntentSnapshot`). It never answers "don't know". A reference the gateway
does not recognise throws, the same as an unreachable gateway: the reference is
one the application stored itself, so the gateway having lost it is evidence
something is wrong, not an answer. Returning a nullable snapshot or an `Unknown`
case would push every caller into guessing, and there is no safe direction to
guess in. A gateway with no lookup API (Adyen reports outcomes by webhook)
answers from the driver record its webhook handler maintains.

**`voidIntent()`** aborts an intent that has not been captured. A clean return
means nothing is held and the intent is dead to the application: the driver
marks it voided in its driver record, and from then on `fetchIntent()` reports
`Voided` for it. Its behaviour by current status:

| Status | `voidIntent()` |
| --- | --- |
| `RequiresAction`, `RequiresCapture` | cancels it at the gateway where the gateway can; otherwise marks it voided locally (below) |
| `Voided`, `Failed` | returns cleanly; nothing is held |
| `Processing` | throws; the outcome is not yet known and voiding may be refused or race a success. Retry after the intent settles. |
| `Captured` | throws; the money has moved. The caller refunds instead. |

That makes a retried void safe: a void that timed out and is retried after the
first attempt succeeded returns cleanly.

Some objects cannot be cancelled before the customer pays: an unapproved PayPal
order, an Adyen session, a Mollie payment whose method is not cancellable. None
of them holds money, so the driver marks the intent voided locally and returns
cleanly. The object can still be completed (a hosted page left open in another
tab), so a local void never hides the gateway's later answer: if the gateway
reports money moved on a voided intent, `fetchIntent()` reports that status
(`Captured` or `RequiresCapture`), not `Voided`. The consumer's reconciliation
sweep treats it as problem case 4, money taken against no order, and refunds or
voids it.

**`refundIntent()`** refunds a captured intent before any order or transaction
exists, and never more than its captured amount less what has been refunded.
The caller sizes the refund from `fetchIntent()`, not from the cart, which may
have changed since the customer confirmed.

`$idempotencyKey` identifies the refund attempt, not the intent. The caller
generates a refund id and persists it before the first attempt, and every retry
of that refund reuses it. A second, separate refund of the same intent gets an
id of its own. No key is derived from the call's arguments: one derived from
the reference would make a second partial refund replay the first, and one
derived from the reference and amount does the same for two partial refunds of
equal value, recording two refunds where one happened.

The key protects only as long as the gateway retains it (Stripe keeps keys for
about 24 hours). A caller retrying a refund whose first attempt is older than
that must call `fetchIntent()` first and compare `refundedMinor`, rather than
rely on the key.

`refundIntent()` returns the existing `PaymentRefund` data object rather than a
bare reference string, so a caller gets the gateway's refund reference, any
`message` it returned, and whether the refund is still pending. Its
`?Transaction $transaction` is always null here, which the object already
documents as "recorded, but not attributable": `transactions.order_id` is
non-nullable, and the whole premise of this call is that no order was ever
placed. See the `PaymentRefund` change under data objects.

### `SupportsPaymentHolds`

```php
interface SupportsPaymentHolds extends ReconcilesPaymentIntents
{
    /** @throws PaymentIntentException */
    public function createHold(Cart $cart): PaymentIntentDescriptor;

    /** @throws PaymentIntentException */
    public function describeHold(Cart $cart, string $reference): ?HoldDescription;

    /** @throws PaymentIntentException */
    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment;

    /** @throws PaymentIntentException */
    public function captureHold(string $reference, int $amountMinor): void;
}
```

Authorise-only flows. `createHold()` is idempotent per cart and per flavour,
with the same lost-response rule as `createIntent()`. `describeHold()` always
asks the gateway (or, for a webhook-driven gateway, the driver record its
webhooks maintain) and never trusts a client claim; it returns null when the
reference is unknown, is not a hold, was not created by `createHold()` for the
given cart, or is in a different currency from the cart. `adjustHold()` reports
whether the hold now covers a new total. `captureHold()` captures at most the
authorised figure and is idempotent per reference.

Releasing a hold is `ReconcilesPaymentIntents::voidIntent()`, which is why this
interface extends it rather than adding a release verb of its own. No gateway
can authorise a hold without also being able to void it, and the extension
makes that a type guarantee: a consumer that has checked
`instanceof SupportsPaymentHolds` can void without a second check. Left as
documentation, the money-back path is exactly where a driver implementing only
one of the two would fail, and failing there is the worst case there is.

**Ownership is the driver's job, not every caller's.** The reference
`describeHold()` takes arrives from the client, so without the cart a caller
could be shown a hold that belongs to someone else: another session's, another
storefront's on the same gateway account, or the customer's own from an
abandoned cart. A plain amount comparison does not catch it either, because a
hold in a different currency with a larger minor-unit figure (1000 JPY against a
999 pence total) passes. Passing the cart in lets the driver check the hold
against its own record of what it created for that cart, once, rather than
leaving each consumer to remember a check the contract never asked for.
`HoldDescription` therefore needs no currency or owner fields: a non-null
description is already in the cart's currency and belongs to it.

`adjustHold()` and `captureHold()` keep taking the bare reference. A caller only
passes them a reference that `describeHold()` has accepted for the cart, or one
the application stored from `createHold()` itself.

`describeHold()` returning null where `fetchIntent()` throws is deliberate, not
an inconsistency. The reference `describeHold()` takes arrives from the client,
so "the gateway has never heard of this" is a routine answer to an unverified
claim. The reference `fetchIntent()` takes is one the application stored, so the
same condition means something has gone wrong.

### Switching flavour

When a customer abandons one flavour for the other on the same cart (a wallet
hold, then card entry), the first object is still live and, for a hold, still
holds an authorisation. The consumer stored both references when it received
their descriptors, so the consumer voids the one it is leaving, before the
customer confirms the other. Core offers no "list a cart's live references"
call: the consumer already has them, and the driver record is the driver's.

### Failures and what a clean return means

Every method on all four interfaces throws a named
`Lunar\Core\Exceptions\PaymentIntentException` (extending `LunarException`)
when the gateway refuses the call, cannot be reached, or returns something the
driver cannot interpret, and carries an `@throws` tag saying so. A consumer
catches that type rather than `\Throwable`, and cannot confuse a gateway that
went quiet with a bug in its own code.

The create and sync methods are no exception. A failed create may still have
made an object at the gateway, which the persisted idempotency key recovers on
retry. A failed sync leaves the intent at a stale amount, which the consumer
must see so it can keep confirmation blocked.

**A clean return means the gateway accepted the operation and the driver knows
of no failure.** It does not always mean the money has settled. Stripe's
captures and cancels are synchronous, but Adyen's capture, cancel and refund
calls return an acknowledgement and report the result later by webhook, and
Stripe and Mollie refunds can sit pending and fail afterwards. So:

- A caller that sees `voidIntent()` return cleanly may treat the money as
  released, or being released.
- A caller that sees `captureHold()` return cleanly may hand the customer their
  order.
- A caller that sees `refundIntent()` return cleanly may tell the customer
  their money is coming. If `PaymentRefund::$pending` is true, it keeps the
  refund open rather than marking it settled.

A failure that arrives after a clean return shows up in `fetchIntent()`: a
pending refund that later fails drops out of `refundedMinor`, and a void or
capture that later fails leaves the status where it was. A consumer that needs
certainty (a reconciliation sweep, a support tool) re-fetches. Core defines no
event for late failures; a driver may dispatch its own.

**One failure channel, not two.** `PaymentRefund` carries a `success` flag, and
it would be easy to read that as the way `refundIntent()` reports a failed
refund. It is not. A refund the gateway refuses throws; a returned
`PaymentRefund` always has `success` true. Two channels would be worse than
either alone: the natural consumer treats any clean return as success, so a
driver reporting failure by flag would have a failed refund recorded as done
and nobody chasing the money.

### Confirming the payment before placing the order

Two checks keep the charged amount equal to the amount the customer was shown,
and both belong to the consumer (`lunarphp/checkout`, or a storefront's own
payment step).

**Before confirmation.** The consumer calls `syncIntent()` after every cart
change that affects the total, and does not enable confirmation until the
latest sync returned cleanly.

**Before placing the order.** The rule differs by flavour, because a hold is
meant to be captured at or below what it authorised:

| | Standard intent (`fetchIntent()`) | Hold (`describeHold()`) |
| --- | --- | --- |
| Status that allows placement | `Captured` | `RequiresCapture` |
| Amount rule | `capturedMinor` equals the cart's payable total, in the cart's currency | `amountMinor` is at least the cart's payable total (currency is guaranteed by `describeHold()`) |
| On an amount failure | refund it and ask the customer to pay again | call `adjustHold()`; ask the customer to authorise again only on `NeedsReauthorization` |
| `Processing` | wait and check again; do not void | not applicable |
| Any other status | do not place; void it if it is still voidable, and ask the customer to pay again | the same |

A null from `describeHold()` fails the check, the same as a wrong status. A
hold that passes is captured with `captureHold()` at the cart's total, never at
the authorised figure.

The consumer runs this flow only for a driver that implements both
`CreatesPaymentIntents` and `ReconcilesPaymentIntents`. A driver that can
create intents but not report them falls back to order-time payment through
`PaymentType::authorize()`, where the driver bar already requires the driver to
verify the authorised amount and currency.

### Data objects

All three are plain readonly containers in `Lunar\Core\DataObjects`, per the
`DataObjects/` folder responsibility.

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
        public readonly string $currency,
        public readonly int $amountMinor,
        public readonly int $capturedMinor = 0,
        public readonly int $refundedMinor = 0,
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

**`PaymentIntentDescriptor`.** `reference` is what the backend reconciles by.
`redirectUrl` is set when the customer completes payment on a page the gateway
hosts (Mollie's checkout, PayPal's approval link without the JS SDK).
`clientPayload` is whatever the driver's own frontend component needs to
confirm in-page: Stripe's client secret, Adyen's session id and session data.
It is opaque to core and to the consumer, which passes it to the driver's
component unread. An in-page flow whose only input is the reference (PayPal's
JS SDK) leaves both empty.

**`PaymentIntentSnapshot`.** `currency` is the ISO 4217 code. `amountMinor` is
what the intent is for (the authorised amount, for a hold). `capturedMinor` is
what has been captured, which for a hold may be less than `amountMinor`.
`refundedMinor` counts refunds that have succeeded or are pending, so the most
a caller can still refund is `capturedMinor - refundedMinor`. A fully refunded
intent is `Captured` with `refundedMinor` equal to `capturedMinor`; refund
state lives in the amounts, not in extra status cases.

**`HoldDescription`.** `walletLabel` lets a consumer name the wallet a hold came
from ("authorised with Apple Pay") without knowing any gateway's wallet
taxonomy.

The existing `PaymentRefund` gains two optional fields so it can carry what
`refundIntent()` needs to report:

```php
public function __construct(
    public bool $success = false,
    public ?string $message = null,
    public ?Transaction $transaction = null,
    public ?string $reference = null,   // new
    public bool $pending = false,       // new
) {}
```

Trailing optional parameters, so every existing construction site and every
third-party driver keeps working unchanged. `reference` is the gateway's own
refund reference, which a consumer needs for audit and support even when there
is no transaction row to attribute the refund to. `pending` is true when the
gateway accepted the refund but has not yet settled it.

### Enums

```php
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

`PaymentIntentStatus` is the set of outcomes a consumer branches on, not a
mirror of any one gateway's status list. A driver maps its own statuses onto
these:

- `RequiresAction`: waiting on the customer (no payment method yet, a 3-D
  Secure challenge, an unapproved PayPal order). Nothing is held; voiding is
  safe, by cancellation or by a local mark (see `voidIntent()`).
- `Processing`: the customer has acted and the gateway has not decided. The
  payment may still succeed, so it is neither abandoned nor voidable.
- `RequiresCapture`: authorised and held, waiting for `captureHold()`.
- `Captured`: money taken. Refunds are visible in the snapshot's amounts.
- `Voided`: cancelled before capture; nothing is held.
- `Failed`: the gateway declined or the object expired; nothing is held.

Adding a case later breaks every consumer that matches on the enum without a
default arm, which is the reason the set is settled here rather than grown
later.

`HoldAdjustment::Ok` means the hold now covers the new total, whether it
already did or was incremented. `NeedsReauthorization` means this hold cannot
stretch and the customer must authorise again.

### Amounts

Every amount is an `int` in minor units, named `$amountMinor` (or
`*Minor` on a data object) so there is no ambiguity at the boundary. That is
core's own money representation, not any gateway's: Stripe and Adyen take
minor units on the wire, PayPal and Mollie take decimal strings. Drivers scale
to their wire format through `Currency::decimal_places`, per the
payment-driver bar in `CLAUDE.md`. No `PriceValue` in these signatures: the
cart the amount came from is the consumer's to hold, and a bare integer is the
smallest thing both sides of the boundary agree on.

### Gateway mapping

Every method against the four gateways the problem names, from their
documented APIs. Stripe is implemented in slice 2; PayPal is a follow-on (see
open questions); Adyen and Mollie are third-party territory and are here to
test the vocabulary, not to promise drivers.

| Method | Stripe | PayPal (Orders v2) | Adyen (Checkout) | Mollie |
| --- | --- | --- | --- | --- |
| `createIntent` | PaymentIntent; `clientPayload` carries the client secret | Order (intent `CAPTURE`); JS SDK takes the reference, or `redirectUrl` is the approval link | Session; `clientPayload` carries id and session data | Payment; `redirectUrl` is the checkout URL |
| `syncIntent` | Update amount | Patch the order amount | Not supported: amount fixed per session | Not supported: amount fixed per payment |
| `fetchIntent` | Retrieve | Get order; driver maps to the capture | Driver record kept by webhooks | Get payment |
| `voidIntent` | Cancel | Void the authorisation; unapproved order marked voided locally | Cancel an authorised payment (async, webhook); unpaid session marked voided locally | Cancel where the method allows; otherwise marked voided locally |
| `refundIntent` | Refund by intent; may be pending | Refund the capture; driver maps from the reference | Refund (async, webhook) | Refund; may be pending |
| `createHold` | Manual capture method | Order (intent `AUTHORIZE`) | Session with manual capture | Payment with manual capture mode (cards) |
| `describeHold` | Retrieve, check driver record | Get order and authorisation | Driver record kept by webhooks | Get payment, check driver record |
| `adjustHold` | Incremental authorisation where eligible, else `NeedsReauthorization` | Reauthorise is constrained; `NeedsReauthorization` in practice | Amount update (async, scheme-dependent) | `NeedsReauthorization` |
| `captureHold` | Capture with amount | Capture the authorisation | Capture (async, webhook) | Create capture |

Two things the table settles. The reference has to be opaque, because PayPal
and Adyen both act on ids other than the one created first. And a clean return
has to mean "accepted" rather than "settled", because Adyen's modifications are
all asynchronous.

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

**`Refunded` and `PartiallyRefunded` status cases.** Refund state is a
quantity, and two cases still would not say how much can be refunded next.
`refundedMinor` on the snapshot carries it exactly. Rejected.

**A core event for late gateway failures.** Would have to be dispatched from
every driver's webhook handling, with no consumer yet that reacts to it.
`fetchIntent()` already reflects a late failure for anyone who re-checks.
Deferred until a consumer needs push rather than pull.

**Do nothing.** Leaves every cart-phase payment flow written against a
specific driver's API, and leaves `lunarphp/checkout` unable to support a
second gateway without a code change in checkout itself.

## Migration impact

- **Database migrations:** none in core. Slice 2 adds a `flavour` column to
  the Stripe package's `stripe_payment_intents` baseline migration, folded
  into the existing baseline per the v2 alpha convention.
- **Breaking changes:** none. Ten of the eleven files are new, `PaymentType`
  is untouched, and existing drivers keep working without implementing
  anything. The eleventh, `PaymentRefund`, gains two trailing optional
  constructor parameters, which is non-breaking for callers and for
  third-party drivers that construct it. No Rector rule needed in the
  `upgrade` package.
- **Upgrade path for v1.x consumers:** not applicable. There is no v1
  equivalent to migrate from.
- **Translation / locale impact:** none. No user-facing strings. A driver's
  refusal reason is the driver's to word, not core's.
- **Filament / admin impact:** none. These are cart-phase seams; the admin
  works on orders and transactions.

## Open questions

- **Does PayPal get these capabilities in this spec's scope?** The mapping
  table shows PayPal fits the vocabulary, with the driver mapping its order,
  authorisation and capture ids from one reference. But the driver was only
  just hardened (spec 0071) and holds have a different shape there.
  Resolution: out of scope, tracked as a follow-on. Owner: Alec Ritson.
- **When does `@experimental` come off?** When a second driver implements
  `ReconcilesPaymentIntents`. PayPal is the expected candidate. Owner: Alec
  Ritson.

## References

- `[[0070-first-party-payment-drivers]]`: the first-party driver bar these
  capabilities are implemented against.
- `[[0071-paypal-driver-hardening]]`: why PayPal is out of scope here.
- `[[0016-service-layer-di]]`: contracts in `Contracts/`, container over
  config for substitution.
- `lunarphp/checkout` specs 0002 (payment methods and driver) and 0012
  (express payments) are the first consumer of this surface, and live in that
  repository.

## Implementation plan

- [ ] Slice 1: the core surface. Ten new files in `packages/core/src`:
      four contracts in `Contracts/` (`CreatesPaymentIntents`,
      `SyncsPaymentIntents`, `ReconcilesPaymentIntents`,
      `SupportsPaymentHolds`), `PaymentIntentDescriptor`,
      `PaymentIntentSnapshot` and `HoldDescription` in `DataObjects/`,
      `PaymentIntentStatus` and `HoldAdjustment` in `Enums/`,
      `PaymentIntentException` in `Exceptions/`, plus trailing optional
      `reference` and `pending` fields on the existing `PaymentRefund`.
- [ ] Slice 2: Stripe implements all four. `StripePaymentType` declares the
      capabilities; `StripeManager` gains `createHold()` and flavour-keyed
      intent lookup so a standard intent and a hold can coexist on one cart,
      persists a per-cart, per-flavour idempotency key before each create
      call, and corrects a locally-active record when the gateway reports the
      intent dead. Maps Stripe's statuses onto `PaymentIntentStatus`
      (`requires_payment_method`, `requires_confirmation` and
      `requires_action` to `RequiresAction`; `processing` to `Processing`),
      reports captured and refunded amounts from the intent and its charge,
      treats cancelling an already-cancelled intent as success, and marks
      pending refunds. Adds the `flavour` column, a `minimum_amounts` config
      key, and recorded hold responses under
      `packages/stripe/resources/responses/` per the driver bar.
