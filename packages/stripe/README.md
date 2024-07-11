<p align="center"><img src="https://github.com/lunarphp/addons/assets/1488016/59ab3f7a-3a3f-4519-a14d-254525dbcf78" width="300" ></p>


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

## Minimum Requirements

- Lunar  `1.x`
- A [Stripe](http://stripe.com/) account with secret and public keys

## Optional Requirements

- Laravel Livewire (if using frontend components)
- Alpinejs (if using frontend components)
- Javascript framework

## Installation

### Require the composer package

```sh
composer require lunarphp/stripe
```

### Publish the configuration

This will publish the configuration under `config/lunar/stripe.php`.

```sh
php artisan vendor:publish --tag=lunar.stripe.config
```

### Publish the views (optional)

Lunar Stripe comes with some helper components for you to use on your checkout, if you intend to edit the views they provide, you can publish them.

```sh
php artisan vendor:publish --tag=lunar.stripe.components
```

### Enable the driver

Set the driver in `config/lunar/payments.php`

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

Below is a list of the available configuration options this package uses in `config/lunar/stripe.php`

| Key | Default | Description |
| --- | --- | --- |
| `policy` | `automatic` | Determines the policy for taking payments and whether you wish to capture the payment manually later or take payment straight away. Available options `manual` or `automatic` |

---

## Backend Usage

### Create a PaymentIntent

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::createIntent(\Lunar\Models\Cart $cart, $options = []);
```

This method will create a Stripe PaymentIntent from a Cart and add the resulting ID to the meta for retrieval later. If a PaymentIntent already exists for a cart this will fetch it from Stripe and return that instead to avoid duplicate PaymentIntents being created.

You can pass any additional parameters you need, by default the following are sent:

```php
[
    'amount' => 1099,
    'currency' => 'GBP',
    'automatic_payment_methods' => ['enabled' => true],
    'capture_method' => config('lunar.stripe.policy', 'automatic'),
    // If a shipping address exists on a cart
    // $shipping = $cart->shippingAddress
    'shipping' => [
        'name' => "{$shipping->first_name} {$shipping->last_name}",
        'phone' => $shipping->contact_phone,
        'address' => [
            'city' => $shipping->city,
            'country' => $shipping->country->iso2,
            'line1' => $shipping->line_one,
            'line2' => $shipping->line_two,
            'postal_code' => $shipping->postcode,
            'state' => $shipping->state,
        ],
    ]
]
```

```php
$paymentIntentId = $cart->meta['payment_intent']; // The resulting ID from the method above.
```
```php
$cart->meta->payment_intent;
```

### Fetch an existing PaymentIntent

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::fetchIntent($paymentIntentId);
```

### Syncing an existing intent

If a payment intent has been created and there are changes to the cart, you will want to update the intent so it has the correct totals.

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::syncIntent(\Lunar\Models\Cart $cart);
```


### Update an existing intent

For when you want to update certain properties on the PaymentIntent, without needing to recalculate the cart.

See https://docs.stripe.com/api/payment_intents/update

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::updateIntent(\Lunar\Models\Cart $cart, [
    'shipping' => [/*..*/]
]);
```

### Cancel an existing intent

If you need to cancel a PaymentIntent, you can do so. You will need to provide a valid reason, those of which can be found in the Stripe docs: https://docs.stripe.com/api/payment_intents/cancel.

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::cancelIntent(\Lunar\Models\Cart $cart, string $reason);
```

### Update the address on Stripe

So you don't have to manually specify all the shipping address fields you can use the helper function to do it for you.

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::updateShippingAddress(\Lunar\Models\Cart $cart);
```

## Charges

### Retrieve a specific charge

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::getCharge(string $chargeId);
```

### Get all charges for a payment intent

```php
use \Lunar\Stripe\Facades\Stripe;

Stripe::getCharges(string $paymentIntentId);
```

## Webhooks

The plugin provides a webhook you will need to add to Stripe. You can read the guide on how to do this on the Stripe website [https://stripe.com/docs/webhooks/quickstart](https://stripe.com/docs/webhooks/quickstart).

The 3 events you should listen to are `payment_intent.payment_failed`,`payment_intent.processing`,`payment_intent.succeeded`. 

The path to the webhook will be `http:://yoursite.com/stripe/webhook`.

You can customise the path for the webhook in `config/lunar/stripe.php`.

You will also need to add the webhook signing secret to the `services.php` config file:

```php
<?php

return [
    // ...
    'stripe' => [
        // ...
        'webhooks' => [
            'payment_intent' => '...'
        ],
    ],
];
```

## Storefront Examples

First we need to set up the backend API call to fetch or create the intent, this isn't Vue specific but will likely be different if you're using Livewire.

```php
use \Lunar\Stripe\Facades\Stripe;

Route::post('api/payment-intent', function () {
    $cart = CartSession::current();

    $cartData = CartData::from($cart);

    if ($paymentIntent = $cartData->meta['payment_intent'] ?? false) {
        $intent = StripeFacade::fetchIntent($paymentIntent);
    } else {
        $intent = StripeFacade::createIntent($cart);
    }

    if ($intent->amount != $cart->total->value) {
        StripeFacade::syncIntent($cart);
    }
        
    return $intent;
})->middleware('web');
```

### Vuejs

This is just using Stripe's payment elements, for more information [check out the Stripe guides](https://stripe.com/docs/payments/elements)

### Payment component

```js
<script setup>
const { VITE_STRIPE_PK } = import.meta.env

const stripe = Stripe(VITE_STRIPE_PK)
const stripeElements = ref({})

const buildForm = async () => {
    const { data } = await axios.post("api/payment-intent")

    stripeElements.value = stripe.elements({
        clientSecret: data.client_secret,
    })

    const paymentElement = stripeElements.value.create("payment", {
        layout: "tabs",
        defaultValues: {
            billingDetails: {
                name: `{$billingAddress.value.first_name} {$billingAddress.value?.last_name}`,
                phone: billingAddress.value?.contact_phone,
            },
        },
        fields: {
            billingDetails: "never",
        },
    })

    paymentElement.mount("#payment-element")
}

onMounted(async () => {
    await buildForm()
})

// The address object can be either passed through as props or via a second API call, but it should likely come from the cart.

const submit = async () => {
    try {
        const address = {...}

        const { error } = await stripe.confirmPayment({
            //`Elements` instance that was used to create the Payment Element
            elements: stripeElements.value,
            confirmParams: {
                return_url: 'http://yoursite.com/checkout/complete',
                payment_method_data: {
                    billing_details: {
                        name: `{$address.first_name} {$address.last_name}`,
                        email: address.contact_email,
                        phone: address.contact_phone,
                        address: {
                            city: address.city,
                            country: address.country.iso2,
                            line1: address.line_one,
                            line2: address.line_two,
                            postal_code: address.postcode,
                            state: address.state,
                        },
                    },
                },
            },
        })
    } catch (e) {
    
    }
}
</script>
```

```html
<template>
    <form @submit.prevent="submit">
        <div id="payment-element">
            <!--Stripe.js injects the Payment Element-->
        </div>
    </form>
</template>
```
---

## Contributing

Contributions are welcome, if you are thinking of adding a feature, please submit an issue first so we can determine whether it should be included.


## Testing

A [MockClient](https://github.com/lunar/stripe/blob/main/tests/Stripe/MockClient.php) class is used to mock responses the Stripe API will return.
