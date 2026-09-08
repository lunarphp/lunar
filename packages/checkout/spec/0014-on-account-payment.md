# 0014: On-account payment, exclusive methods, and pay-time guards

- Status: accepted (2026-09-08), not yet implemented
- Author: Alec Ritson
- Created: 2026-09-08
- Depends on: [[0002-payment-methods-and-driver]] §A (`PaymentMethod`, `requiresIntent`,
  the synchronous path), [[0004-checkout-session]] §C (`Open → Completed`),
  [[0010-cart-session-reconciliation]] §E (the pay boundary), [[0011-address-lookup-and-element-bag]]
  (the element bag that holds the order reference)
- Sibling change outside this package: `panel` (order payment state). Core, `stripe` and
  `table-rate-shipping` are untouched.

## Problem

A trade customer with an account does not pay at the checkout. The order is placed, the
goods go out, and the ERP invoices the account on terms. This package already has the
mechanics: `PaymentMethods\Offline` completes a session synchronously with no gateway and
core's `OfflinePayment` places the order unpaid. Three things are missing before a host can
ship it.

1. **Nothing decides who may pay on account.** Lunar's `lunar_customers.account_ref` exists,
   but a populated reference means nothing to Lunar on its own. A host must be able to say,
   in code, what qualifies a cart, and the package must never assume.
2. **Nothing makes a method exclusive.** For an account customer the card form must not
   appear at all, and neither must the express wallets on the cart and checkout pages.
   `PaymentMethodRegistry::availableFor()` filters each method independently; it cannot
   express "when this one applies, only this one applies".
3. **Nothing can require an element field for one payment method.** Trade counters commonly
   want a purchase order reference on account orders. The reference lives in the session's
   element bag (`element_data`, spec 0011), not on the cart, so `isAvailable(Cart)` cannot
   see it, and element rules run when the element is stored, before any method is chosen.

Two smaller gaps surface once those are fixed:

4. **The synchronous path records no method.** `meta.payment_method` is written only when an
   intent is created, and it holds the driver name (`stripe`), because
   `PaymentIntentGateway` resolves the gateway from it. An offline completion leaves no trace
   of how the order was paid, so nothing downstream (panel, mails, ERP) can tell an
   on-account order from a card order whose payment never recorded.
5. **The panel shows an unpaid order as Pending.** Core's offline type writes no
   `Transaction`, so the payment rollup for an on-account order is indistinguishable from a
   card order with a lost webhook.

## Proposal

### A. `OnAccount`, a shipped method with a host-defined eligibility rule

```php
namespace Lunar\Checkout\PaymentMethods;

class OnAccount extends Offline implements ExclusivePaymentMethod, GuardsPayment
{
    public function handle(): string   { return 'on-account'; }
    public function label(): string    { return __('lunar-checkout::checkout.payments.on_account.label'); }
    public function component(): string { return 'on-account-notice'; }

    /** @param  (\Closure(Cart): bool)|null  $eligible */
    public static function eligibleWhen(?\Closure $eligible): void;
    public static function requireReference(bool $required = true): void;

    public function isAvailable(Cart $cart): bool
    {
        return static::$eligible !== null && (static::$eligible)($cart);
    }
}
```

- With no closure registered the method is never available. That is the agnostic default:
  registering `OnAccount` in a store that never calls `eligibleWhen()` changes nothing.
- The closure receives the cart, not the user: eligibility is a property of what is being
  bought and by whom Lunar says is buying it (`$cart->customer`, `$cart->user`). A guest
  cart has neither, so guests never qualify unless a host says otherwise.
- `requireReference()` defaults to **off**. When on, §C blocks pay until the order
  details element's `reference` is filled.
- `driver()` stays `offline`: core's `OfflinePayment` places the order and writes no
  transaction. The package does not invent a "paid on account" transaction; nothing was
  paid.

The host registers it beside its gateway methods:

```php
$registry->add(OnAccount::class);
OnAccount::eligibleWhen(fn (Cart $cart): bool => filled($cart->customer?->account_ref));
```

### B. Exclusive methods, resolved in one place

```php
namespace Lunar\Checkout\Contracts;

/** Marker: when this method is available for a cart, it is the only method offered. */
interface ExclusivePaymentMethod {}
```

`PaymentMethodRegistry::availableFor(Cart $cart)` filters by `isAvailable()` as today, then,
if any survivor implements `ExclusivePaymentMethod`, returns only the exclusive survivors in
registration order. Every consumer already reads this list: the checkout projection's
`paymentMethods`, `Express::projection()` (so the wallets vanish from the cart and checkout
pages for an account customer), and `CheckoutController::resolveAvailableMethod()` at
`/payment-intent` and `/pay` (so a posted `card` for an eligible cart gets the existing
"The selected payment method is not available." 422). No other file learns about
exclusivity.

Two exclusive methods available at once is a host configuration error, not a runtime
decision: both are returned and the first registered wins the default selection, the same
as today.

### C. Pay-time guards

```php
namespace Lunar\Checkout\Contracts;

interface GuardsPayment
{
    /** A reason pay must not proceed with this method, or null when it may. */
    public function paymentBlocker(CheckoutSession $session, Cart $cart): ?string;
}
```

- `CheckoutController::pay()` resolves the method, then, if it implements `GuardsPayment`
  and returns a message, rejects with a 422 on `payment_method` carrying that message. This
  runs before the intent path and the synchronous path alike, so it also covers a gateway
  method a host later decides to guard.
- The projection adds `paymentBlocker: ?string` to each entry in `paymentMethods`. The
  client disables the pay button while the active method's blocker is non-null and shows the
  message inline, the same pattern as `pickupPointRequired` (spec 0013 §F). The reason is
  computed server-side on every projection, so it tracks element edits through the existing
  partial reloads.
- `OnAccount::paymentBlocker()` returns null unless `requireReference` is on and
  `$session->element_data['order_details']['reference']` is blank, in which case:
  "Enter your purchase order reference to place this order on account." The element key is
  the one `Elements\OrderDetails` writes; a host that replaced that element carries its own
  method.

### D. Recording the method on the session and the order

- `pay()` writes `meta.payment_handle = $method->handle()` on the session before either
  path proceeds. `meta.payment_method` (the driver) is untouched; `PaymentIntentGateway`
  and the hold code depend on it.
- `LunarCheckoutDriver::complete()` stamps `order.meta.payment_method` from
  `session.meta.payment_handle` when it is set, on both the synchronous and the
  PaymentProcessing paths, inside the existing order transaction. The webhook-first path
  arrives through `CompleteSessionOnPaymentSuccess` and gets the same stamp. Hosts that need
  more (an account reference snapshot, say) listen to `OrderPlacing`, which already carries
  the session.

### E. UI

- `on-account-notice` component, registered like `offline-notice`: a locked row with the
  store icon reading "This order will be charged to your trade account. You will be invoiced
  on your usual terms." No account reference is shown at the checkout; the customer knows
  their own account and the number belongs on the invoice, not the screen.
- When the active method's `requiresIntent` is false the pay button reads "Place order"
  (translation key `checkout.pay.place_order`), not "Pay £x". The total still shows in the
  summary.
- With one available method the existing single-method layout applies: no tabs.
- The express squeeze page is unaffected: an exclusive offline method never reaches it,
  because `Express::projection()` filters it out (no `supportsExpress`) and the wallets are
  filtered out by §B.

### F. Panel (sibling change: `panel`)

The order screen's payment summary reads `order.meta.payment_method`. When it is
`on-account` the state chip reads "On account" instead of "Pending", the sub-copy reads
"Invoiced on account, no payment taken at checkout", and the transactions table is hidden
rather than shown empty. Any other handle keeps today's rollup. Translation keys
`payments.on_account`, `payments.on_account_sub`.

## Testing

Package (`tests/checkout`):

- `OnAccount` unavailable with no closure; available when the closure says so; a guest cart
  with no customer is unavailable under the reference host closure.
- Registry: an exclusive available method hides every other method; exclusive but
  unavailable hides nothing; two exclusive methods are both returned in registration order.
- Express projection returns no wallets for a cart where an exclusive method is available.
- `/pay` with `card` for an eligible cart is a 422 "not available"; `/pay` with
  `on-account` completes synchronously, the order is placed with no transactions, and
  `order.meta.payment_method === 'on-account'`.
- `requireReference(true)`: projection carries the blocker while the reference is blank,
  `/pay` is a 422 with the message, storing a reference clears the blocker and pay
  completes; `requireReference(false)` never blocks.
- A Stripe card completion (sync fake gateway) also stamps `order.meta.payment_method`.
- Source invariant: `paymentBlocker` disables both pay buttons in `LunarCheckout.vue`.

Panel (`tests/panel/Feature/Orders`): an order with `meta.payment_method = on-account`
projects the on-account state and no transactions table; a card order is unchanged.

## Out of scope

- Credit limits, account holds, and any live ERP check. The eligibility closure is the seam
  a host uses when its ERP can answer; the package stays synchronous and local.
- Recording a transaction for on-account orders. Payment happens at invoice time, outside
  this package.
- Partial on-account (split between account and card).
- Showing the account reference to the customer on any surface.
