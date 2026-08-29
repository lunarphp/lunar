# Lunar PayPal

PayPal payment driver for Lunar, built on the PayPal Orders v2 and Payments v2 APIs.

The package is server-side only. It creates and captures PayPal orders, records the money against Lunar's transaction ledger, handles refunds and webhooks, and exposes the results to the admin. It deliberately **does not ship a storefront integration** — the client-side half is a handful of calls to PayPal's JS SDK, and every storefront wants it wired differently (Blade, Inertia, Livewire, a headless SPA, a native app). The flow you need to implement is documented below.

## Requirements

- PHP 8.4+
- `lunarphp/core` (installed automatically)
- A PayPal REST app — [developer.paypal.com](https://developer.paypal.com/dashboard/applications/sandbox)

## Installation

```bash
composer require lunarphp/paypal
```

The service provider auto-registers. Publish the config and run migrations:

```bash
php artisan vendor:publish --tag=lunar.paypal.config
php artisan migrate
```

Then enable the driver as a payment type in `config/lunar/payments.php`:

```php
'types' => [
    'paypal' => [
        'driver' => 'paypal',
    ],
],
```

## Configuration

`config/lunar/paypal.php`, or the matching environment variables:

| Key | Env | Default | Purpose |
| --- | --- | --- | --- |
| `env` | `PAYPAL_ENV` | `sandbox` | `sandbox` or `live` |
| `client_id` | `PAYPAL_CLIENT_ID` | — | REST app client ID |
| `secret` | `PAYPAL_SECRET` | — | REST app secret |
| `webhook_id` | `PAYPAL_WEBHOOK_ID` | — | Required for webhooks; without it, inbound notifications are rejected |
| `webhook_path` | — | `paypal/webhook` | Where the driver listens |
| `policy` | — | `automatic` | `automatic` captures immediately; `manual` authorizes now, captures later |
| `allow_partial_payment` | — | `false` | Allow PayPal to cover less than the order total |
| `success_route` | — | `checkout.success` | Where PayPal returns an approving customer |
| `cancel_route` | — | `checkout.cancel` | Where PayPal returns a cancelling customer |
| `order_rate_limit` | — | `10,1` | Throttle on the create-order endpoint, as `attempts,minutes` |

Credentials fall back to the equivalent `services.paypal.*` keys if unset. **That fallback is deprecated** and will be removed in a future release — move your credentials to `lunar.paypal.*`.

## The checkout flow

Four steps. Your storefront owns steps 2 and 4.

```
 1. POST /api/paypal/order          -> this package creates a PayPal order
 2. PayPal JS SDK                   -> your storefront renders the buttons
 3. Customer approves at PayPal
 4. Your checkout controller        -> calls authorize(), which captures and places
```

### 1. Create the PayPal order

The package registers one storefront route:

```
POST /api/paypal/order      (name: post.paypal.order)
```

It builds a PayPal order for the **current session cart** (`CartSession::current()`) and returns just what the client needs:

```json
{
  "id": "5O190127TN364715T",
  "status": "CREATED",
  "approve_url": "https://www.sandbox.paypal.com/checkoutnow?token=5O190127TN364715T"
}
```

It returns `422` when there is no cart to pay for, and `502` when PayPal declines to create the order. Each call costs a PayPal API request, so the route is throttled — see `order_rate_limit`.

The amount is taken from the calculated cart total, in the cart's currency. If you need a different payload — multiple purchase units, a custom `reference_id`, line-item breakdown — bind your own implementation of `Lunar\Paypal\Contracts\PaypalInterface`, or call `Paypal::buildInitialOrder()` from your own controller instead of using this route.

### 2. Render the PayPal buttons

Load the SDK with your client ID and currency, and point `createOrder` at the route above:

```html
<div id="paypal-button-container"></div>

<script src="https://www.paypal.com/sdk/js?client-id=YOUR_CLIENT_ID&currency=GBP"></script>
<script>
paypal.Buttons({
    createOrder: () => fetch('/api/paypal/order', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(order => order.id),

    // PayPal has taken the approval; hand the id back to your own checkout.
    onApprove: (data) => fetch('/checkout/paypal', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ paypal_order_id: data.orderID }),
        })
        .then(response => response.json())
        .then(result => window.location = result.redirect),
}).render('#paypal-button-container');
</script>
```

`/checkout/paypal` is **your** route. The package does not provide it, because what happens after payment — which page you land on, what you email, how you handle failure — is yours.

### 3. Customer approves

Handled entirely by PayPal. The customer may never come back to your storefront; step 4 covers that case via webhooks.

### 4. Authorize

In your own controller, hand the approved PayPal order ID to the driver:

```php
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\Payments;

public function store(Request $request)
{
    $request->validate([
        'paypal_order_id' => ['required', 'string'],
    ]);

    $payment = Payments::driver('paypal')
        ->cart(CartSession::current())
        ->withData([
            'paypal_order_id' => $request->paypal_order_id,
        ])
        ->authorize();

    if (! $payment->success) {
        return response()->json(['message' => $payment->message], 422);
    }

    CartSession::forget();

    return response()->json([
        'redirect' => route('checkout.success', $payment->orderId),
    ]);
}
```

`authorize()` verifies the amount, captures the money, creates the order and places it, in that order. It returns a `PaymentAuthorize` carrying `success`, `message` and `orderId`.

The `message` on a failure is a **diagnostic for you**, not copy to show a shopper — decide your own wording per failure case.

### Amount verification

Before capturing anything, the driver checks the PayPal order against the expected total:

| PayPal covers | Result |
| --- | --- |
| Less than the total | **Fails.** No money is captured. Set `allow_partial_payment` to permit it (deposits, part payments) |
| A different currency | **Fails.** No money is captured |
| Exactly the total | Captures and places the order |
| More than the total | Captures and places the order. The excess shows in the order's settlement state for an admin to refund |

Over-payment is deliberately allowed through: the money has already left the customer's account, and refusing would strand a captured payment with no order attached to it. To change the policy, override the `protected assertOrderMatchesTotal()` on a subclass of `PaypalPaymentType`.

## Capture policies

**`automatic`** (default) captures at authorize time. One `capture` transaction, order placed, payment status `paid`.

**`manual`** authorizes only. The driver requests an `AUTHORIZE` intent, and the held funds are recorded as an `intent` transaction — the order places, with payment status `authorized`. Capture later, in full or in part:

```php
$intent = $order->intents()->first();

$intent->capture();          // whole authorization
$intent->capture(5_00);      // part of it, in minor units
```

PayPal authorizations expire (typically 29 days). Capture within that window or the hold is lost.

## Webhooks

Without webhooks the driver only learns what happens while the customer is on your site. Anything asynchronous — a capture that settles later, a customer who approves and closes the tab, a refund issued from the PayPal dashboard, a dispute — never reaches Lunar, and the order's payment status drifts from reality.

Create a webhook in the PayPal dashboard pointing at:

```
https://your-store.test/paypal/webhook
```

Subscribe it to:

- `CHECKOUT.ORDER.APPROVED`
- `PAYMENT.CAPTURE.COMPLETED`
- `PAYMENT.CAPTURE.DENIED`
- `PAYMENT.CAPTURE.PENDING`
- `PAYMENT.CAPTURE.REFUNDED`
- `CUSTOMER.DISPUTE.CREATED`

Then set the webhook ID it gives you as `PAYPAL_WEBHOOK_ID`. Signatures are verified with PayPal on every request; **without a webhook ID, notifications are rejected rather than trusted.**

Approval and capture events carry the PayPal order through to a placed Lunar order, so a customer who never returns to your storefront still gets one. Refund events record a refund transaction, skipping any the driver itself already wrote.

To act on events yourself, listen for `PaypalWebhookReceived` — it fires for every verified webhook, including the ones the driver does not act on:

```php
use Lunar\Paypal\Events\PaypalWebhookReceived;

Event::listen(function (PaypalWebhookReceived $event) {
    if ($event->eventType === 'CUSTOMER.DISPUTE.CREATED') {
        // your dispute handling
    }
});
```

## Refunds

Refund against a capture transaction:

```php
$capture = $order->captures()->first();

$capture->refund(10_00, 'Returned damaged');
```

Amounts are minor units in the order's currency. Refunds raised in the PayPal dashboard arrive by webhook and are recorded automatically.

## Payment checks

PayPal returns AVS and CVV results on each capture. The driver stores them and exposes them through the standard seam, which the admin renders:

```php
$transaction->paymentChecks();
```

## Extending

The client is bound to `Lunar\Paypal\Contracts\PaypalInterface`. Swap it in your own service provider:

```php
$this->app->scoped(PaypalInterface::class, MyPaypal::class);
```

The driver itself is a `PaymentType`. Subclass `PaypalPaymentType` and re-register it to change authorization behaviour:

```php
Payments::extend('paypal', fn ($app) => $app->make(MyPaypalPaymentType::class));
```

## What this package does not do

- **Render anything.** No Blade components, no Livewire, no JS. See the flow above.
- **Own your checkout routes.** `/api/paypal/order` is the only route it adds on the storefront side.
- **Decide what the shopper sees.** Failure messages on `PaymentAuthorize` are diagnostics; the copy is yours.
- **Support PayPal subscriptions or payouts.** Orders and Payments only.
