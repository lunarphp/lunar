# 0071 — PayPal driver hardening

- Status: implemented
- Author: Glenn Jacobs
- Created: 2026-08-27
- TODO item: Bring `lunarphp/paypal` up to the first-party driver bar

> Implementation notes (landed): all six slices shipped in one PR. Two findings
> changed the shape of the work. First, slice 1's harness immediately showed the
> driver was not merely thin but non-functional — `authorize()` wrote the removed
> `status` column, so every successful payment threw *after* the money was
> captured; that fix led slice 2. Second, this spec was drafted against a branch
> carrying the line-item refunds work, so its claim that `PaymentRefund` exposes
> a `$transaction` for line attribution does not hold on `2.x`; see the
> References section. One deliberate divergence from the plan: the amount guard
> lands *after* the already-processed check rather than before it, so a spent
> PayPal order is rejected without computing totals.

## Problem

[[0070-first-party-payment-drivers]] narrows the first-party driver set to Stripe
and PayPal. Stripe meets the bar that spec defines; PayPal does not come close.
The package is 473 lines across 9 files with no tests, no config, no webhooks, and
several defects that lose or mis-record money.

### It has no tests and no CI

`tests/paypal` contains one file: `.gitkeep`. There is no `paypal` testsuite in
`phpunit.xml` and no `paypal` entry in the CI matrix. Every change to core since v2
began has landed against this package untested. For comparison, Stripe has 9 test
files, recorded API fixtures under `resources/responses/`, and a `MockClient`.

### Authorization crashes on a column that no longer exists

`PaypalPaymentType::authorize()` finishes by writing the order:

```php
$this->order->update([
    'status' => $status ?? ($this->config['authorized'] ?? null),
    'placed_at' => now(),
]);
```

`lunar_orders` has no `status` column in v2 — it was replaced by the derived
`payment_status` and `fulfilment_status` pair, which `TransactionObserver`
recomputes from the transaction ledger via `RecomputesOrderStatus`. Every
otherwise-successful PayPal payment therefore ends in:

```
SQLSTATE[HY000]: General error: 1 no such column: status
```

The driver is not thin, it is non-functional: the money is captured at PayPal
and the exception is thrown after the capture, so the customer is charged and no
order is placed. This went unnoticed precisely because the package has no CI.

The fix is the one Stripe already uses — set `placed_at` and let the ledger drive
the status. The `authorized` key in the driver's `lunar.payments.types` config
entry becomes meaningless and is dropped.

### Authorization does not verify the amount

`PaypalPaymentType::authorize()` takes `$this->data['paypal_order_id']`
(`PaypalPaymentType.php:57`) — a client-supplied value — fetches that PayPal order,
captures it, and places the Lunar order. Nothing compares the captured amount or
currency against the cart total. A caller can present the ID of a cheaper PayPal
order they legitimately own and have a more expensive Lunar order placed against it.

Stripe closed exactly this hole with `assertIntentMatchesTotal()`
(`StripePaymentType.php`), gated behind an `allow_partial_payment` config flag.
PayPal has no equivalent.

### Amounts are scaled by a hardcoded factor, through a float

`PaypalPaymentType.php:90` builds the capture transaction amount as:

```php
'amount' => (int) ($capture['amount']['value'] * 100),
```

Two bugs in one line. First, the multiply happens in floating point and the cast
truncates, so common amounts come out a penny short:

| PayPal value | Stored amount | Correct |
| --- | --- | --- |
| `19.99` | 1998 | 1999 |
| `1.15` | 114 | 115 |
| `0.29` | 28 | 29 |

Second, `100` is hardcoded. JPY has 0 decimal places and KWD has 3, so every
non-two-decimal currency is stored off by orders of magnitude. `refund()` has the
inverse of the same bug at `PaypalPaymentType.php:156` — `(string) ($amount / 100)`
— which means a JPY refund requests 1/100th of the intended amount from PayPal.

Stripe scales through `StripeManager::toStripeAmount()` /
`fromStripeAmount()`, which read `Currency::decimal_places`. PayPal must do the same.

### `buildInitialOrder()` reads an uncalculated cart

`Paypal.php:112` reads `(string) $cart->total->decimal()`. `Cart::$total` is a
public `?PriceValue` that is null until `calculate()` runs
(`packages/core/src/Models/Cart.php:162`), so this fatals on any cart that has not
been calculated in the current request. Two lines below it, `Paypal.php:128` reads
`$billingAddress->contact_email` without the null-safe operator that every
surrounding line uses, so a cart with no billing address fatals too.

### `cancel_url` points at the success route

`Paypal.php:123` sets `'cancel_url' => route($successRoute, $cart->fingerprint())`
— the same route as `return_url`, with the cart fingerprint as a positional
parameter. A customer who cancels at PayPal is returned to the success page.

### The capture policy is dead config

`PaypalPaymentType::__construct()` reads `lunar.paypal.policy`
(`PaypalPaymentType.php:28`) and never uses it. `buildInitialOrder()` hardcodes
`'intent' => 'CAPTURE'`, and `capture()` (`PaypalPaymentType.php:138`) is a no-op
that unconditionally returns `new PaymentCapture(success: true)` — it reports
success without contacting PayPal. Authorize-then-capture, which PayPal supports
via `intent: AUTHORIZE`, does not work, and the manual policy silently behaves as
automatic while the panel is told the capture succeeded.

### There are no webhooks

Stripe ships a webhook controller, signature-verifying middleware, a queued job,
and event DTOs. PayPal ships nothing. Every asynchronous outcome is therefore
invisible to Lunar: pending captures that settle later, `PAYER_ACTION_REQUIRED`
resolutions, refunds issued from the PayPal dashboard, disputes, and chargebacks.
The order's payment status silently drifts from reality.

### There is no persistence and no idempotency

Stripe stores a `StripePaymentIntent` row to guard against double-processing and to
detect orphaned intents. PayPal stores nothing; replaying the same
`paypal_order_id` is guarded only by an `$this->order->placed_at` check
(`PaypalPaymentType.php:42`), which does not cover the window between capture and
order placement. Nor does the driver send a `PayPal-Request-Id` idempotency header
on capture, so a retried capture can charge twice.

### Config is scattered across four namespaces

`services.paypal.env`, `services.paypal.client_id`, `services.paypal.secret`,
`lunar.payments.paypal.success_route`, and `lunar.paypal.policy` — five keys across
four namespaces, none of them in a publishable config file. Stripe has
`packages/stripe/config/stripe.php`.

### The public surface is untyped and unverifiable

`PaypalInterface` is literally `interface PaypalInterface {}`. Nothing is declared,
so the container binding guarantees nothing, phpstan cannot check call sites, and
the `@method` block on the `Paypal` facade is unverified prose — it currently
declares `array refund(void $transactionId, ...)`, a parameter typed `void`.
`Paypal.php` has no parameter or return type declarations, and reaches for
`config()` and the `Http` facade inline rather than taking its collaborators on the
constructor as the service-layer convention requires. The provider binds
`PaypalInterface` as a `singleton` (`PaypalServiceProvider.php`) while `Paypal`
memoizes `$accessToken` — a per-request credential cached for the life of an Octane
or queue worker, with no expiry handling. Bindings also happen in `boot()` rather
than `register()`.

### `getPaymentChecks()` is not implemented

`Transaction::paymentChecks()` (`packages/core/src/Models/Transaction.php:115`)
delegates to the driver, so the admin's payment-checks surface is empty for every
PayPal transaction. PayPal returns AVS and CVV results in
`processor_response`; nothing reads them.

## Proposal

Rebuild the package to the shape Stripe already has, keeping the public entry points
(`Payments::driver('paypal')`, the `Paypal` facade, `PaypalPaymentType`) stable.

### Configuration

Add `packages/paypal/config/paypal.php`, published as `lunar.paypal`, consolidating
every key onto one namespace:

```php
return [
    'env' => env('PAYPAL_ENV', 'sandbox'),        // sandbox | live
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    'webhook_path' => 'paypal/webhook',
    'policy' => 'automatic',                       // automatic | manual
    'allow_partial_payment' => false,
    'success_route' => 'checkout.success',
    'cancel_route' => 'checkout.cancel',
];
```

`services.paypal.*` reads keep working for one release via a fallback in the config
file, with a deprecation note in the upgrade guide.

### `PaypalInterface` becomes a real contract

Move it to `packages/paypal/src/Contracts/PaypalInterface.php` and declare every
method with full parameter and return types. `Paypal` takes its collaborators
(`Factory $http`, `Repository $config`, `CacheRepository $cache`) as promoted
constructor properties. The access token is cached against `expires_in` with a
safety margin rather than memoized on the instance, and the binding becomes
`scoped`, registered in `register()`.

### Amount handling

Add `PaypalManager::toPaypalAmount(int $value, Currency $currency): string` and
`fromPaypalAmount(string $amount, Currency $currency): int`, mirroring
`StripeManager`'s rescaling against `Currency::decimal_places`. Conversion goes
through integer string arithmetic, never a float multiply. Every `* 100` and
`/ 100` in the package is replaced by a call to these.

### Amount verification on authorize

Port Stripe's guard: an `assertOrderMatchesTotal()` on `PaypalPaymentType` that
compares the PayPal order's amount and currency against the order total (or the
calculated cart total), returns a failing `PaymentAuthorize` on mismatch, and is
skipped when `allow_partial_payment` is set. Declared `protected` so a consumer can
relax it, as Stripe's is.

### Capture policy

Honour `policy`. `buildInitialOrder()` sends `intent: AUTHORIZE` under the manual
policy and `CAPTURE` under automatic. `capture()` calls
`/v2/payments/authorizations/{id}/capture`, supports a partial amount, records the
resulting transaction, and returns a truthful `PaymentCapture`. Capture and refund
requests carry a `PayPal-Request-Id` derived from the transaction so retries are
idempotent.

### Webhooks

Mirror the Stripe layout:

- `routes/webhooks.php` and a `WebhookController`.
- `PaypalWebhookMiddleware` verifying signatures via
  `/v1/notifications/verify-webhook-signature` against the configured `webhook_id`.
- A queued `ProcessPaypalWebhook` job.
- Handling for `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.DENIED`,
  `PAYMENT.CAPTURE.PENDING`, `PAYMENT.CAPTURE.REFUNDED`,
  `CHECKOUT.ORDER.APPROVED`, and `CUSTOMER.DISPUTE.CREATED`.
- A `PaypalWebhookReceived` event so consumers can extend coverage without
  subclassing.

### Persistence

Add a `paypal_orders` table and `PaypalOrder` model (`paypal_order_id`, `cart_id`,
`order_id`, `status`, `processing_at`, `processed_at`), folded into the v2 baseline
migrations per the alpha migration policy, and registered in the upgrade package's
disableable-migrations list. It gives the driver the same double-processing guard
and orphan detection Stripe has, and gives webhooks a way to resolve an inbound
PayPal order ID to a cart or order.

### Fixes to the order payload

`buildInitialOrder()` calls `$cart->calculate()` when `$cart->total` is null, uses
`?->` consistently on the billing address, and points `cancel_url` at the configured
`cancel_route`. `GetPaypalOrderController` gets a rate limiter and returns only the
fields the client needs.

### `getPaymentChecks()`

Implement it, reading AVS and CVV results from the capture's `processor_response`
into `PaymentCheck` entries, matching how Stripe maps its `*_check` metadata.

### Tests

Register a `paypal` testsuite in `phpunit.xml` and add `paypal` to the CI matrix in
`.github/workflows/tests.yml`. Record PayPal API responses as fixtures under
`packages/paypal/resources/responses/` and drive tests through `Http::fake()`,
mirroring `tests/stripe`. Coverage targets: the amount rescaling table (including
JPY and KWD), authorize success and every failure branch, the amount-mismatch
guard, both capture policies, full and partial refunds, webhook signature
verification and each handled event type, and the order-payload builder against an
uncalculated cart and a cart with no billing address.

## Alternatives considered

**Adopt an existing PayPal SDK** (`paypal/paypal-server-sdk` or similar). Rejected
for now. The driver touches five endpoints; Laravel's HTTP client covers them
without a dependency whose release cadence we do not control, and `Http::fake()`
gives cleaner tests than mocking an SDK. Worth revisiting if the endpoint surface
grows.

**Patch the money bugs only, leave the rest.** Rejected. The amount bugs are the
most urgent, but shipping PayPal as first-party while it has no webhooks means
orders whose payment status silently diverges from PayPal's — a support burden that
outlives the fix.

**Rewrite from scratch as a new package.** Rejected. The public entry points are
right; the implementation behind them is what is thin. A rewrite would break
consumers for no gain.

**Drop PayPal too and ship Stripe alone.** Rejected. PayPal is the one gateway with
genuinely global reach and merchant demand, which is exactly the argument for
keeping it that does not apply to Opayo.

## Migration impact

- **Database migrations**: one new `paypal_orders` table, folded into the v2
  baseline per the alpha policy, plus an entry in the upgrade package's
  disableable-migrations list.
- **Breaking changes**: `PaypalInterface` moves to `Lunar\Paypal\Contracts` and
  gains method declarations — a break only for consumers who implemented the empty
  interface, which guaranteed nothing. Ships with a Rector rule in the `upgrade`
  package for the namespace move. `Paypal` gains a constructor, so anything
  instantiating it directly rather than resolving it from the container must be
  updated; the facade and container binding are unaffected. `capture()` returning a
  truthful result is a behaviour change for anyone relying on its unconditional
  success.
- **Upgrade path**: config keys move from `services.paypal.*` to `lunar.paypal.*`,
  with a fallback for one release. Merchants using webhooks configure a webhook ID
  in the PayPal dashboard; the driver works without one, with async outcomes
  unhandled as they are today.
- **Translations**: `packages/paypal/resources/lang/` is added with all 16
  locales, covering the payment-check labels — the only driver strings the admin
  actually renders, via `Transaction::paymentChecks()`. The `PaymentAuthorize`
  failure messages stay untranslated English, matching `packages/stripe`: they
  are diagnostics returned to the storefront integrator, who decides what the
  shopper sees, not strings Lunar renders. Translating them would put PayPal out
  of step with the reference driver for no user-visible gain.
- **Filament / panel impact**: none structurally. The panel resolves drivers through
  `PaymentManager` and reads checks via `Transaction::paymentChecks()`, so
  implementing `getPaymentChecks()` populates an existing surface rather than
  adding one.

## Open questions

**Resolved — over-capture places the order.** `authorize()` fails when PayPal
reports *less* than the order total (unless `allow_partial_payment` is set) and
places the order when it reports *more*, leaving the settlement banner to surface
it as `refund_due`. Rationale: at that point the money is already taken at PayPal.
Failing the authorization would leave a captured payment with no order attached —
the orphaned-payment problem Stripe needed a dedicated
`OrphanedPaymentIntentDetected` event to paper over. The banner already computes
and renders exactly this case, so an over-capture becomes a visible admin task
rather than a silent inconsistency. This is a deliberate divergence from Stripe's
`assertIntentMatchesTotal()`, which fails on any mismatch.

**Resolved — `paypal_orders` keys on the PayPal order ID.** One row per PayPal
order (the object `authorize()` is handed and the thing that maps to a cart),
with capture IDs living on `transactions.reference` as they do for every other
driver. `PAYMENT.CAPTURE.*` webhooks arrive keyed by capture ID but carry
`supplementary_data.related_ids.order_id`, so they resolve back to the row
without a second table.

**Open — reference storefront integration.** Do we ship a reference client-side
integration (PayPal JS SDK button wiring), or document the flow and leave the
storefront to the consumer? The v1 blade-component approach does not fit v2's
headless shape. Owner: Glenn; can follow the driver work, nothing in this spec
depends on it.

## References

- [[0070-first-party-payment-drivers]] — the bar this spec meets.
- [[0028-line-item-refunds]] — **not yet on `2.x`**. `PaymentRefund` currently
  carries only `success` and `message`; the `$transaction` property and
  `RefundRequest` land with 0028. Neither Stripe nor PayPal populates a refund
  transaction on the DTO today, so this spec does not either. When 0028 merges,
  PayPal's `refund()` needs the same one-line change Stripe's does — assign the
  created transaction and pass it to `PaymentRefund` — for line attribution to
  work. Tracked there, not here.
- `packages/stripe/` — the reference implementation for every section above.
- PayPal Orders v2 and Payments v2 API documentation.

## Implementation plan

- [x] Slice 1 — Test harness: `paypal` testsuite in `phpunit.xml`, CI matrix entry,
      `tests/paypal/TestCase.php`, recorded response fixtures, and characterisation
      tests covering current behaviour so the later slices have a safety net.
- [x] Slice 2 — Correctness: stop writing the removed `status` column so a
      successful payment can place an order at all, `PaypalManager` rescaling
      helpers, remove every hardcoded `* 100` / `/ 100`, and add the amount and
      currency verification guard on `authorize()`.
- [x] Slice 3 — Contract and configuration: real `PaypalInterface` under
      `Contracts/`, constructor injection, cached access token, `scoped` binding
      registered in `register()`, `config/paypal.php` with the `services.paypal.*`
      fallback, and the `buildInitialOrder()` / `cancel_url` fixes.
- [x] Slice 4 — Persistence and capture policy: `paypal_orders` table and model,
      double-processing guard, idempotency headers, working `intent: AUTHORIZE`
      flow, and a real `capture()`.
- [x] Slice 5 — Webhooks: routes, signature-verifying middleware, queued job, event
      handlers, and the `PaypalWebhookReceived` event.
- [x] Slice 6 — `getPaymentChecks()` from `processor_response`, plus the
      `resources/lang/` strings across all 16 locales.
