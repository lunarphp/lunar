# Payment terms: orders paid after checkout

- Status: proposed
- Author: Glenn Jacobs
- Created: 2026-11-12
- TODO item: Payment terms — record when an order is meant to be paid (at checkout, on account, or on pickup) as a typed order field, and record a payment taken later (spec payment-terms)

## Problem

An order's payment status is derived from its transactions (`ResolvePaymentStatus`). An order with nothing captured or authorized is `Pending`. Some orders are meant to be unpaid when they are placed:

- **On account.** A trade customer is invoiced and pays later.
- **On pickup.** The customer pays at the counter when they collect.

These orders have no transactions, so they read as `Pending`, the same as an order whose card payment failed. Core has no record that they are expected to be unpaid.

The panel works around this. `OrderShowController::onAccount()` treats an order as on account when `meta['payment_method'] === 'on-account'` and the status is `Pending`, and relabels the status on the order screen (#2749). Nothing in core writes that key: a checkout has to know to pass it through `OfflinePayment`'s `meta`. The orders index, the Filament admin and the notifications still show these orders as an ordinary "Pending".

There is also no way to record the payment when it arrives. `OfflinePayment::authorize()` writes no transaction, and its `capture()` writes none either, so nothing moves an on-account order to `Paid` when the invoice is settled. Staff can't record a payment taken at the counter.

Settling this before beta avoids a breaking change and a data migration later.

## Proposal

Core decides on the terms, so they are a typed column, not a key in `meta`. Core reads and writes none of its own keys in `meta`: `meta` belongs to the store and its add-ons. This spec removes the panel's read of `meta['payment_method']`.

### Terms

An enum `Lunar\Core\Enums\PaymentTerms`:

| Case | Value | Means |
|---|---|---|
| `Checkout` | `checkout` | Paid at checkout. The default, and every order today. |
| `OnAccount` | `on-account` | Invoiced and paid later. |
| `OnPickup` | `on-pickup` | Paid in store when the customer collects. |

The enum has a `label()` for display, translated in all 16 locales.

### Schema

- `orders.payment_terms`: string, not null, default `checkout`, cast to `PaymentTerms`.
- `carts.payment_terms`: string, nullable, cast to `PaymentTerms`. Null means checkout.

Both fold into the baseline create-table migrations while v2 is in alpha.

### Who sets the terms

The cart and the payment type can both set terms. The payment type wins, because it runs last.

- **The checkout**, through `$cart->setPaymentTerms(?PaymentTerms $terms)`, a model verb delegating to `Actions\Carts\SetCartPaymentTerms` (contract `SetsCartPaymentTerms`), following [[0029-entry-point-conventions]]. `CreateOrder` copies the cart's terms onto the order.
- **The payment type**, through its config. A payment type entry in `config/lunar/payments.php` can declare `terms`:

  ```php
  'on-account' => [
      'driver' => 'offline',
      'terms' => 'on-account',
  ],
  ```

  `AbstractPayment` exposes `terms(): ?PaymentTerms`, read from the config. `OfflinePayment::authorize()` writes it to the order when it places the order. A gateway driver doesn't declare terms, so card payments keep `checkout`.

This replaces the `meta['payment_method']` convention. A store moves its on-account checkout to a payment type that declares the terms, and the panel stops reading meta.

### Reading the terms

- `Order::isAwaitingPaymentByTerms(): bool` is true when the terms are not `Checkout` and the payment status is `Pending`. Core, the panel and Filament use it, instead of each matching a handle.
- `Order::paymentStatusLabel(): string` returns the terms' label ("On account", "Pay on pickup") while the order is awaiting payment by terms, and the payment status label otherwise. The panel order screen, the orders index, the Filament order resource and notifications use it.

Terms never change the payment status itself. Once money is recorded the status moves on as it does today (`PartiallyPaid`, `Paid`), and the order reads like any other.

### Zero-value orders

A zero-value order needs no special case. `ResolvePaymentStatus` already settles a zero-total order as `Paid`, so it is never `Pending`:

- Its terms are `checkout` unless the cart or payment type sets others.
- If other terms are set, they are stored as given. `isAwaitingPaymentByTerms()` is false and `paymentStatusLabel()` shows the payment status.
- `recordPayment()` has no outstanding balance to record against, and the panel doesn't offer the action.
- If the order is edited and its total rises, the status leaves `Paid` and the stored terms apply from then on. This is why core doesn't reset the terms to `checkout` on a free order.

### Recording a payment taken later

`Order::recordPayment(int $amount, ?string $reference = null, ?string $notes = null): Transaction`, delegating to `Actions\Orders\RecordPayment` (contract `RecordsPayment`). It writes a successful `capture` transaction with driver `offline`, then recomputes the order status through `RecomputesOrderStatus`. The amount can't exceed the outstanding balance.

A recorded payment is refundable with no extra work: `RefundOrder` refunds any successful capture through its transaction's driver, and `OfflinePayment::refund()` already writes the refund transaction.

The panel gets a "Record payment" action on an order with an outstanding balance, with amount (defaulting to the balance), reference and notes. Filament gets the same action on the order page.

### Validation

`ValidateCartForOrderCreation` rejects `OnPickup` terms on a cart with nothing to pick up, with a new translated message `exceptions.carts.payment_terms_pickup_missing`. Until [[0083-cart-line-fulfilment-method]] lands, "nothing to pick up" means the chosen shipping option is not a pickup. After it lands, it means no line resolves to the `pickup` method.

### Notifications and the settlement banner

The `payment-received` notification fires when the payment status reaches `paid`, so a recorded payment that settles the order sends it, and an order placed on account doesn't. The settlement banner already handles an order with nothing captured. Neither needs a code change; the docs describe how terms affect them.

## Alternatives considered

- **Keep `meta['payment_method']` and document it.** Core doesn't use `meta` for its own values, and each surface would still have to know the handle.
- **New payment states (`AwaitingInvoice`, `AwaitingPickup`).** The payment status describes money, and is derived from transactions. A state that depends on an arrangement instead would need its own writers and would conflict with `ResolvePaymentStatus` the moment a part payment arrives.
- **Put terms in [[0077-payment-intent-capabilities]].** That spec covers gateway intents before an order exists. Terms are about what payment an order expects, often with no gateway at all.
- **Leave payment recording to the store.** Every store with on-account customers needs it, and a store that writes its own transaction has to know how to recompute the status.

## Migration impact

- Database: `orders.payment_terms` and `carts.payment_terms`, in the baseline migrations.
- Public contract: additive. New enum, model verbs, two actions and their contracts, `AbstractPayment::terms()`, and the `terms` payment config key.
- Upgrade from v1: no data step. v1 orders get `checkout`. The upgrade guide shows how to move an on-account checkout from `meta['payment_method']` to a payment type with `terms`.
- Translations: the three terms labels, the record-payment action and its fields, and the validation message, in all 16 locales across `core`, `panel` and `admin`.
- Admin: the panel replaces `OrderShowController::onAccount()` and its `ON_ACCOUNT` constant with `isAwaitingPaymentByTerms()` and `paymentStatusLabel()`, and the orders index uses the label. Filament's order resource uses the label and gets the record-payment action.

## Open questions

- **Due dates.** An invoice on account usually has a due date (net 30). Is a nullable `orders.payment_due_at` worth adding now, while it is free, even though core does nothing with it yet? Owner: Glenn.
- **Changing terms after placement.** Should staff be able to move an order to on account from the panel, for example when a card payment fails for a trade customer? This spec doesn't add it.

## References

- #2749: the panel's on-account reading of `meta['payment_method']`.
- #2846: the typed-checkout-choices draft this spec was split from.
- [[0083-cart-line-fulfilment-method]]: the pickup choice that `OnPickup` validates against.
- [[0077-payment-intent-capabilities]]
- `packages/core/src/Actions/Orders/ResolvePaymentStatus.php`, `packages/core/src/PaymentTypes/OfflinePayment.php`, `packages/panel/src/Http/Controllers/Orders/OrderShowController.php`.

## Implementation plan

- [ ] Slice 1 — `PaymentTerms` enum and labels, `orders.payment_terms` and `carts.payment_terms`, `setPaymentTerms()` and its action, `CreateOrder` copying the terms, `AbstractPayment::terms()` and `OfflinePayment` writing them, `isAwaitingPaymentByTerms()` and `paymentStatusLabel()`, the `OnPickup` validation. Tests.
- [ ] Slice 2 — `recordPayment()` and `RecordPayment`. Tests, including refunding a recorded payment.
- [ ] Slice 3 — Panel: replace the meta reads with the order methods, the label on the index, the record-payment action. Translations.
- [ ] Slice 4 — Filament: the label and the record-payment action. Translations.
- [ ] Slice 5 — Docs and upgrade guide.
