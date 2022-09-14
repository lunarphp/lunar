<p align="center"><img src="https://user-images.githubusercontent.com/1488016/161026191-aab67703-e932-40d0-a4ac-e8bc85fff35e.png" width="300" ></p>


<p align="center">This addon enables Stripe payments on your Lunar storefront.</p>

## Alpha Release

This addon is currently in Alpha, whilst every step is taken to ensure this is working as intended, it will not be considered out of Alpha until more tests have been added and proved.

## Tests required:

- [ ] Successful charge response from Stripe.
- [ ] Unsuccessful charge response from Stripe.
- [ ] Test `manual` config reacts appropriately.
- [x] Test `automatic` config reacts appropriately.
- [ ] Ensure transactions are stored correctly in the database
- [x] Ensure that the payment intent is not duplicated when using the same Cart
- [ ] Ensure appropriate responses are returned based on Stripe's responses.
- [ ] Test refunds and partial refunds create the expected transactions
- [ ] Make sure we can manually release a payment or part payment and handle the different responses.

## Requirements

- Lunar >= `0.1`
- A [Stripe](http://stripe.com/) account with secret and public keys
- Laravel Livewire (if using frontend components)
- Alpinejs (if using frontend components)

## Installation

### Require the composer package

```sh
composer require getcandy/stripe
```

### Publish the configuration

This will publish the configuration under `config/getcandy/stripe.php`.

```sh
php artisan vendor:publish --tag=getcandy.stripe.config
```

### Publish the views (optional)

Lunar Stripe comes with some helper components for you to use on your checkout, if you intend to edit the views they provide, you can publish them.

```sh
php artisan vendor:publish --tag=getcandy.stripe.components
```

### Enable the driver

Set the driver in `config/getcandy/payments.php`

```php
<?php

return [
    // ...
    'types' => [
        'card' => [
            // ...
            'driver' => 'stripe',
        ],
    ],
];
```

### Add your Stripe credentials

Make sure you have the Stripe credentials set in `config/services.php`

```php
'stripe' => [
    'key' => env('STRIPE_SECRET'),
    'public_key' => env('STRIPE_PK'),
],
```

> Keys can be found in your Stripe account https://dashboard.stripe.com/apikeys

## Configuration

Below is a list of the available configuration options this package uses in `config/getcandy/stripe.php`

| Key | Default | Description |
| --- | --- | --- |
| `policy` | `automatic` | Determines the policy for taking payments and whether you wish to capture the payment manually later or take payment straight away. Available options `manual` or `automatic` |

---

# Backend Usage

## Creating a PaymentIntent

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::createIntent(\Lunar\Models\Cart $cart);
```

This method will create a Stripe PaymentIntent from a Cart and add the resulting ID to the meta for retrieval later. If a PaymentIntent already exists for a cart this will fetch it from Stripe and return that instead to avoid duplicate PaymentIntents being created.

```php
$cart->meta->payment_intent;
```

## Fetch an existing PaymentIntent

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::fetchIntent($paymentIntentId);
```

Both these methods will return a `Stripe\PaymentIntent` object.

# Storefront Usage

This addon provides some useful components you can use in your Storefront, they are built using Laravel Livewire and AlpineJs so bear that in mind.

If you are using the [Demo Store](https://github.com/getcandy/demo-store), this is already set up for you so you can refer to the source code to see what's happening.

## Set up the scripts

Place this in the `<head>` of your Storefront.

```blade
@stripeScripts
```

## Add the payment component

Wherever you want the payment form to appear, add this component:

```blade
@livewire('stripe.payment', [
  'cart' => $cart,
  'returnUrl' => route('checkout.view'),
])
```

The `returnUrl` is where we want Stripe to redirect us afer they have processed the payment on their servers.

**Do NOT point this to the order confirmation page, as you'll see below**

## Process the result

You'll notice above we've told Stripe to redirect back to the checkout page, this is because although Stripe has either taken payment or allocated funds based on your policy, we still need Lunar to process the result and create the transactions it needs against the order.

When Stripe redirects us we should have two parameters passed in the query string. `payment_intent_client_secret` and `payment_intent`. We can then check for these values and pass them off using Lunar's Payments driver.

So, assuming we are using Livewire and on a `CheckoutPage` component (like on the Demo Store)

```php
if ($request->payment_intent) {
    $payment = \Lunar\Facades\Payments::driver('card')->cart($cart)->withData([
        'payment_intent_client_secret' => $request->payment_intent_client_secret,
        'payment_intent' => $request->payment_intent,
    ])->authorize();

    if ($payment->success) {
        redirect()->route('checkout-success.view');
        return;
    }
}

```

And that should be it, you should then see the order in Lunar with the correct Transactions.

If you have set your policy to `manual` you'll need to go into the Hub and manually capture the payment.

---

### Contributing

Contributions are welcome, if you are thinking of adding a feature, please submit an issue first so we can determine whether it should be included.


### Testing

Currently we use a manual [MockClient](https://github.com/getcandy/stripe/blob/main/tests/Stripe/MockClient.php) to mock the responses the Stripe API will return. This is likely to be improved upon as tests are written, but it should be apparent what this is doing, so feel free to add your own responses.
