# 0095 — PayPal implements the payment intent capabilities

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-11-05
- TODO item: PayPal implements the payment intent capabilities

## Problem

Spec 0077 added four opt-in capabilities for the cart-phase payment lifecycle,
and Stripe implements them. `PaypalPaymentType` implements none, so PayPal has
only the legacy `authorize()` flow. That has three consequences.

- `lunarphp/checkout` cannot offer PayPal. Its spec 0002 sends every method
  except offline ones through fetch, void and refund by reference. Its spec 0012
  makes express depend on `SupportsPaymentHolds`, and names a standalone PayPal
  package as a case it must support.
- The 0077 surface stays `@experimental` until a second driver implements
  `CreatesPaymentIntents`. Its PayPal column in the gateway mapping has not been
  tested against the API.
- The driver has defects that the capabilities need fixed first.

The defects:

1. **No record until placement.** `buildInitialOrder()` creates the PayPal order
   but writes no `paypal_orders` row. The row appears when `authorize()` first
   runs (`PaypalOrder::firstOrCreate`). `ProcessPaypalWebhook` finds the cart
   through that row, so a customer who approves and closes the tab is never
   placed. The README says the webhook covers this case. Its test
   (`places an order the customer never came back to confirm`) creates the row
   by hand.
2. **A failed `authorize()` mutes the webhook.** `authorize()` sets
   `processing_at` before it checks the amount and status, and never clears it.
   `WebhookController` drops every later event for a row with `processing_at` set
   and no `processed_at`, as "Already being processed". So a capture that
   settles later is never recorded.
3. **Creation is not idempotent.** Each call to `POST api/paypal/order` creates
   a new PayPal order, and sends no `PayPal-Request-Id`.
4. **Placement is not idempotent, and not tied to the cart.** A second
   `authorize()` for a placed order fails with "This order has already been
   placed", so the consumer and the webhook cannot both reach it. Nothing checks
   that the PayPal order was created for the cart being placed.
5. **Over-payment places the order.** `assertOrderMatchesTotal()` accepts an
   amount above the total, and its docblock argues for it. Spec 0071 asked for a
   port of Stripe's guard, which refuses any mismatch. 0077 places only when the
   captured amount equals the draft total, and refunds otherwise.
6. **The create payload may not match the API.** `buildInitialOrder()` sends
   `user_action`, `shipping_preference`, `payment_method_preference`,
   `return_url` and `cancel_url` directly under `payment_source.paypal`. PayPal's
   published Orders v2 schema has them under
   `payment_source.paypal.experience_context`. Its create example with a
   `paypal` payment source returns a `payer-action` link, but
   `GetPaypalOrderController` reads the `approve` link. This comes from the
   schema and has not been tried in the sandbox. The recorded
   `order_created.json` has status `CREATED` and an `approve` link.

## Proposal

`PaypalPaymentType` implements `SyncsPaymentIntents` and `SupportsPaymentHolds`,
and so all four 0077 capabilities, as Stripe does. Core does not change. 0077's
wording changes as listed under "Amendments to 0077".

### Vocabulary

- **Intent.** A PayPal Orders v2 order.
- **Reference.** The PayPal order id.
- **Descriptor.** `clientPayload` is `['order_id' => …]`, the value the JS SDK's
  `createOrder` callback returns. `redirectUrl` is the order's `payer-action`
  link, or `approve` for an order created without a payment source. Consumers
  read the order id from `clientPayload`, because the reference is opaque.
- **Driver record.** `paypal_orders`, one row per PayPal order, written when the
  order is created. The baseline migration gains `flavour` (`standard` or
  `hold`). The row's `status` holds PayPal's order status, or `VOIDED` after a
  local void.
- **Ownership.** Each capability method acts only on a reference that has a row
  for the given cart. `buildInitialOrder()` also sets the purchase unit's
  `custom_id` to the cart's `public_id`. This lets `authorize()` refuse an order
  created for another cart even when the order has no row. The payer does not
  see `custom_id`.

### Order shape

| | Standard, `automatic` policy | Standard, `manual` policy | Hold |
| --- | --- | --- | --- |
| `intent` | `CAPTURE` | `AUTHORIZE` | `CAPTURE` |
| `user_action` | `PAY_NOW` | `PAY_NOW` | `CONTINUE` |
| `shipping_preference` | `SET_PROVIDED_ADDRESS` | `SET_PROVIDED_ADDRESS` | `GET_FROM_FILE` |
| Money moves at | placement | admin capture, after placement | `captureHold()` |

A hold is an approved order that the driver captures when the customer
confirms. Alternatives explains why it is not an `AUTHORIZE` order. Approval
reserves no funds, so the capture can still be declined. The `CONTINUE` button
tells the buyer they finish on the store's page. `GET_FROM_FILE` lets the buyer
pick an address in PayPal, because express starts before the store has one.

### Status mapping

`fetchIntent()` reads the order (`GET /v2/checkout/orders/{id}`) and returns the
status of the first row that matches. A response that matches no row throws, as
Stripe's does for a status it does not know.

| PayPal | Status |
| --- | --- |
| A capture `COMPLETED`, `PARTIALLY_REFUNDED` or `REFUNDED` | `Captured` |
| A capture or authorization `PENDING` | `Processing` |
| A capture `DECLINED` or `FAILED`, or an authorization `DENIED` | `Failed` |
| An authorization `CREATED` | `RequiresCapture` |
| An authorization or the order `VOIDED`, the row `VOIDED`, or the order not found | `Voided` |
| Order `APPROVED` | `RequiresCapture` |
| Order `CREATED`, `SAVED` or `PAYER_ACTION_REQUIRED` | `RequiresAction` |

An approved order is `RequiresCapture` because only the merchant's call is
outstanding, which is what the Placement column's "capture, then place" expects.
Captures rank above a local void. So if money is taken after a void, the order
reads `Captured` and the consumer refunds it, as 0077 requires.

The snapshot's amounts come from the order:

- `amount` is the purchase unit's amount.
- `captured` is the sum of captures in the three statuses of the `Captured` row.
- `refunded` is the sum of `COMPLETED` and `PENDING` refunds.

`walletLabel` comes from the `payment_source` key. It is "PayPal" for `paypal`,
"Venmo" for `venmo`, "Apple Pay" for `apple_pay`, "Google Pay" for `google_pay`,
and null otherwise.

### Client

- Every `PaypalInterface` method throws on a non-2xx response, as
  `captureAuthorization()` and `refund()` already do. The capability methods
  wrap that in `PaymentIntentException`.
- `PaypalInterface` gains `patchOrder(string $orderId, array $operations): void`
  and `voidAuthorization(string $authorizationId, ?string $requestId = null):
  array`.
- `buildInitialOrder()` gains `string $flavour = 'standard'` and
  `?string $requestId = null`. It sets `custom_id`, applies the order shape
  above, and moves the checkout settings into `experience_context`.

### Operations

**`createIntent()` and `createHold()`.** Each returns the cart's row of that
flavour while its mapped status is live. Otherwise it creates an order and
writes the row. The `PayPal-Request-Id` is built the way Stripe builds its key:
`lunar-cart-{public_id}-{flavour}-{generation}-{sha1 of the payload}`. That is
89 characters, inside PayPal's 108 for order creation. PayPal keeps the id for
6 hours, so a retry after a lost response recovers the first order. A live row
in another currency is voided locally and replaced. `POST api/paypal/order`
calls `createIntent()`, and its `approve_url` comes from the descriptor's
`redirectUrl`.

**`syncIntent()`.** Patches the live standard order's amount and shipping
address while it is `RequiresAction`. It sends `PATCH` with `replace` on
`/purchase_units/@reference_id=='default'/amount` and
`/purchase_units/@reference_id=='default'/shipping/address`. The address is
patched because PayPal shows the address the order was created with. Sync
throws, as Stripe's does, in two cases:

- The order is `APPROVED`. The buyer approved that amount with Pay Now, so the
  consumer voids it and calls `createIntent()`.
- The cart's currency changed. The consumer calls `createIntent()`, which
  replaces it.

**`voidIntent()`.** PayPal has no endpoint that cancels an order.

- For `RequiresAction`, or an approved order with no authorization, the driver
  marks the row `VOIDED`. `authorize()`, `captureHold()` and the webhook refuse
  a voided row. Approval moves no money, so the mark is a complete void unless
  code outside the driver captures the order.
- An authorization at `CREATED` (a standard order under the manual policy) is
  voided at PayPal with `POST /v2/payments/authorizations/{id}/void`.
- `Processing` and `Captured` throw. `Voided` and `Failed` are no-ops.

**`refundIntent()`.** Refunds the order's completed capture with `POST
/v2/payments/captures/{id}/refund`, sending the consumer's key as
`PayPal-Request-Id`.

- It throws when the amount is more than captured less refunded, and when
  PayPal answers `FAILED` or `CANCELLED`. A `PENDING` refund returns
  `pending: true`.
- It writes the capture and the refund as transactions on the cart's draft, as
  Stripe's does.
- PayPal does not document how long it keeps a refund's request id. The
  remaining-amount check limits what a late retry can refund.

**`adjustHold()`.** Requires `RequiresCapture`.

- An amount at or below the order's returns `Ok` without a call.
- An amount above it patches the order up and returns `Ok`. A 422 refusal of
  the patch returns `NeedsReauthorization`. Any other failure throws.

**`captureHold()`.** Requires `RequiresCapture`. A `Captured` order at the same
amount is a no-op.

- An amount above the order's throws, as Stripe's does above the authorised
  amount. An amount below it patches the order down first.
- It captures with a `PayPal-Request-Id` keyed on the order and the amount, and
  returns when the capture is `COMPLETED`.
- A `PENDING` capture throws, because the capture is not confirmed. The intent
  reads `Processing` until the webhook settles it.
- `INSTRUMENT_DECLINED` throws, and the order stays `APPROVED`.

### Placement

`authorize()` accepts `reference`, and keeps `paypal_order_id` as an alias. In
order, it:

1. Refuses a row that belongs to another cart, or an order whose `custom_id`
   names another cart.
2. Returns success with the placed order when the row's order is already placed.
3. Refuses a row marked `VOIDED`, or one already processed.
4. Claims the row with a conditional update on `processing_at`, and fails if
   another call holds the claim. A claim older than five minutes is stale and can
   be taken. The claim is released when the call fails or throws, so a failed
   attempt no longer mutes the webhook.
5. Applies 0077's Placement column to the mapped status, on the cart's draft
   order. It creates an order only when the cart has no draft.
   - `RequiresCapture`: checks that the amount equals the draft total, then
     places. An order with intent `CAPTURE` is captured first. An order with
     intent `AUTHORIZE` is authorized first, unless it already has an
     authorization at `CREATED`. The order's intent decides this, not the
     current policy setting.
   - `Captured`: places when the captured amount equals the draft total and
     nothing is refunded. Otherwise it fails, and the consumer refunds.
   - `Processing`: fails, and leaves the row unprocessed so that
     `PAYMENT.CAPTURE.COMPLETED` places it later. This covers a capture that
     comes back `PENDING`.
   - `RequiresAction`, `Voided` and `Failed`: fail.

`allow_partial_payment` still skips the amount checks. `voidIntent()` and
`captureHold()` take the same claim, so a void cannot interleave with a capture.

### Webhooks

- Rows exist from creation, so `CHECKOUT.ORDER.APPROVED` can now place an order
  for a customer who closed the tab.
- `CHECKOUT.ORDER.APPROVED` places standard rows only. The webhook never captures
  a hold.
- `PAYMENT.CAPTURE.COMPLETED` places any unprocessed row through `authorize()`.
  This covers a pending capture that settles, and a captured hold whose
  placement never arrived.
- Voided rows are ignored.
- An event for a claimed row goes back on the queue with a delay. It is no
  longer dropped.

### Amendments to 0077

These land in 0077's text with this spec:

- The `SupportsPaymentHolds` row reads "authorise or approve now and capture
  later, confirming cancel and capture synchronously. An approval may not
  reserve funds."
- In the gateway mapping's PayPal column, Void reads "Order: local mark.
  Authorization: void." Holds reads "Approved order, captured at confirm."

This one lands with slice 2, unless the first open question removes the need:

- The `RequiresAction` create cell reads "return it, re-priced if the cart's
  currency changed. A driver that cannot re-price it voids it and creates
  another." PayPal does not document changing an order's currency by `PATCH`.
  The consumer always stores the reference `createIntent()` returns, so a new
  reference is safe.

### Notes for `lunarphp/checkout`

- Checkout 0002 places with `withData(['payment_intent' => …])`. This driver
  accepts 0077's `reference` key, so checkout moves to it.
- A PayPal hold reserves no funds, so 0012's copy ("Card authorised… a hold for
  £272.00") needs a PayPal wording.
- The PayPal order exists before the buyer sees PayPal, because the JS SDK's
  `createOrder` callback runs on the button click. So an express hold carries
  the total from before shipping is chosen. 0012 creates the Stripe hold after
  the sheet confirms, and that ordering does not carry over.

## Alternatives considered

**Holds as `AUTHORIZE` orders, as 0077's mapping has them.** The driver would
have to authorize at approval, and 0077 has no call for that. `fetchIntent()`
could authorize as a side effect, but then the reconciliation sweep would hold
funds for abandoned carts. Authorizing inside `captureHold()` adds a second call
that moves money, and an authorization to void if the capture fails. PayPal has
no incremental authorization, and allows reauthorizing only after the 3-day
honor period, so an authorization would not help a rising total either.
Rejected.

**Keep the current over-payment rule.** 0077 refunds money taken without an
order through `refundIntent()`. In PayPal's standard flow the check runs before
capture, so refusing costs the customer nothing. Rejected, in line with Stripe.

**Patch the order to the draft total at placement.** Under Pay Now, this
captures an amount the buyer did not approve. Rejected.

**Do nothing.** PayPal stays out of `lunarphp/checkout`, and defects 1 to 4 stay.
Rejected.

## Migration impact

- **Database migrations:** `paypal_orders` gains `flavour` (string, default
  `standard`) in the baseline migration, while v2 is in alpha.
- **Breaking changes:**
  - `PaypalInterface` gains two methods and new `buildInitialOrder()`
    parameters, and its methods throw on a non-2xx response. This breaks a
    custom implementation, which the README suggests binding.
  - `Paypal::getOrder()` throws for an unknown order instead of returning
    PayPal's `RESOURCE_NOT_FOUND` body.
  - An over-paid PayPal order no longer places.
- **Behaviour changes:**
  - A repeat `authorize()` for a placed order succeeds.
  - A pending capture no longer places the order. The webhook places it once the
    capture settles.
  - `POST api/paypal/order` reuses the cart's live PayPal order instead of
    creating one per call.
- **Upgrade path for v1.x consumers:** not applicable.
- **Translation / locale impact:** none.
- **Filament / admin impact:** none. Placement writes the usual transactions.
- **Docs:** the README's checkout flow uses `createIntent()` and `reference`.

## Out of scope

Reading the address a buyer picks in PayPal back onto the cart for express. The
snapshot carries no address. Checkout's express work for PayPal decides how the
address reaches the cart.

## Open questions

- **Can `PATCH` change an order's currency?** If the sandbox accepts it,
  `createIntent()` and `syncIntent()` re-price in place, and the currency
  amendment is dropped. Owner: slice 2's author, before it merges.
- **Can `PATCH` update an order at `PAYER_ACTION_REQUIRED`?** PayPal documents
  `PATCH` for `CREATED` and `APPROVED` only, and the `experience_context` fix
  may put new orders at `PAYER_ACTION_REQUIRED`. If it cannot, orders for
  in-page buttons are created without a payment source. Owner: slice 1's author,
  in the sandbox.
- **Can `PATCH` raise an approved order's amount without the buyer approving
  again, and by how much?** An express hold starts at the total from before
  shipping is chosen, so most express orders need this. If PayPal refuses,
  `adjustHold()` returns `NeedsReauthorization` for every rise, which conforms
  to 0077 but sends those customers back to PayPal. Owner: slice 3's author,
  settled in the sandbox before slice 3 is accepted.
- **What does `GET` return after a capture declined with
  `INSTRUMENT_DECLINED`?** The mapping assumes the order stays `APPROVED`. It then
  reads `RequiresCapture` until the buyer picks another funding source with
  `actions.restart()` in the JS SDK. 0077 calls a retryable decline
  `RequiresAction`, so if PayPal's response tells the two apart, the mapping
  follows it. The same test shows whether a retried capture that reuses the
  request id replays the stored decline. Owner: slice 2's author, recorded with
  PayPal's negative testing (`PayPal-Mock-Response`).
- **Should `CHECKOUT.ORDER.APPROVED` capture when the consumer owns placement?**
  Capture on approval is how the legacy flow places a customer who closed the
  tab. Checkout 0002 refunds a success that arrives for a session not in
  `PaymentProcessing`, so for checkout the same capture becomes a charge and
  then a refund. One option is to capture only when the cart has no draft order,
  since a capability consumer creates the draft before the customer confirms.
  Owner: Alec Ritson, before slice 1 is accepted.
- **Does `@experimental` come off when slice 2 lands?** This is 0077's open
  question. Owner: Alec Ritson.

## References

- `[[0077-payment-intent-capabilities]]`
- `[[0071-paypal-driver-hardening]]`
- `[[0070-first-party-payment-drivers]]`
- `lunarphp/checkout` specs 0002 and 0012.
- PayPal's Orders v2 and Payments v2 OpenAPI specs
  (`paypal/paypal-rest-api-specifications`). They cover `PATCH` on `CREATED` and
  `APPROVED` orders, `PayPal-Request-Id` on the order and payment endpoints, and
  the capture, authorization and refund statuses.
- PayPal's authorization guide. It gives the 29-day authorization, the 3-day
  honor period, and reauthorization only after that period.

## Implementation plan

- [ ] Slice 1: the record and placement.
  - `flavour` on `paypal_orders`.
  - Rows written at creation, with `custom_id` and `PayPal-Request-Id`.
  - `api/paypal/order` reuses the live order.
  - The `experience_context` fix, checked in the sandbox with re-recorded
    fixtures.
  - The client throws on a non-2xx response.
  - The status mapping, which placement reads.
  - `authorize()` takes `reference`, checks ownership, is idempotent, claims the
    row, requires an exact amount and waits on `Processing`.
  - The webhook changes for standard rows.
- [ ] Slice 2: `CreatesPaymentIntents` and `SyncsPaymentIntents` (`createIntent`,
  `syncIntent`, `fetchIntent`, `voidIntent`, `refundIntent`).
  - A test per table cell they reach.
  - The client additions.
  - The currency amendment to 0077, if it is still needed.
- [ ] Slice 3: `SupportsPaymentHolds` (`createHold`, `adjustHold`,
  `captureHold`), and the webhook's rule for holds.

Each slice updates the README's checkout flow for what it ships.
