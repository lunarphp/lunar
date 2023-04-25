# Payments

## Overview

Lunar provides an easy way for you to add your own payment drivers, by default, there is a basic `OfflinePayment` driver that ships with Lunar, additional providers should be added to your Storefront via addons.

Below is a list of available payment drivers.

## Available drivers

### First party

- [Stripe](https://github.com/lunarphp/stripe)

### Community

> Made your own driver you want listing here? Get in touch on our discord channel and we'll get it added.

## Building your own

A payment driver should take into account 2 fundamentals:

* Capturing a payment (whether straight away, or at a later date)
* Refunding an existing payment

### Registering your driver

```php
use Lunar\Facades\Payments;

Payments::extend('custom', function ($app) {
    return $app->make(CustomPayment::class);
});
```

### The payment driver class

First, we'll show you the complete class and then break it down to see what's going on.

```php
<?php

namespace App\PaymentTypes;

use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Transaction;

class CustomPayment extends AbstractPayment
{
    /**
     * {@inheritDoc}
     */
    public function authorize(): PaymentAuthorize
    {
        if (!$this->order) {
            if (!$this->order = $this->cart->order) {
                $this->order = $this->cart->createOrder();
            }
        }

        // ...

        return new PaymentAuthorize(true);
    }

    /**
     * {@inheritDoc}
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        // ...
        return new PaymentRefund(true);
    }

    /**
     * {@inheritDoc}
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        // ...
        return new PaymentCapture(true);
    }
}
```

This is the most basic implementation of a driver, you can see we are extending an `AbstractPayment`. This is a class which is provided by Lunar and contains some useful helpers you can utilise in your own driver.

[See available methods](#abstract-class-methods)

#### Releasing payments

```php
public function authorize();
```

This is where you'd check the payment details which have been passed in, create any transactions for the order and return the response.

If you're not taking payment straight away, you should set any transactions to the type of `intent`. When you then later capture the payment, we would recommend creating another transaction that is related to that via the `parent_transaction_id`.

#### Capturing payments

```php
public function capture(Transaction $transaction, $amount = 0): PaymentCapture
```

When you have a transaction that has a type of `intent` the Staff member who is logged into the hub can then decide to capture it so the card used gets charged the amount that has been authorised.

You can pass an optional amount, but be cautious as you generally cannot capture an amount that exceeds the original amount on the `intent` transaction. If you capture an amount less, services like Stripe will treat that as a partial refund and no further captures can take place on that order.

Here you should create an additional transaction against the order to show how much has been captured.

#### Refunding payments

```php
public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
```

When refunding a transaction, you can only do so to one that's been captured. If you need to refund an order that hasn't been captured you should instead capture an amount less to what's been authorised.

You should only refund transactions with the type `capture`.


<a name="abstract-class-methods"></a>
## The AbstractPayment class

### Available methods

- [`cart`](#cart)
- [`order`](#order)
- [`setData`](#setdata)
- [`setConfig`](#setconfig)

#### `cart`

```php
public function cart(Cart $cart): self
```

Sets the `$cart` property on the payment driver. When using the `release` method we recommend expecting a `$cart` instance and checking for the existence of an order.

#### `order`

```php
public function order(Order $order): self
```

Sets the `$order` property on the payment driver.

#### `withData`

```php
public function withData(array $data): self
```

This method allows you to add any additional data to the payment driver, this can be anything that the payment driver needs to function, for example.

```php
Payments::driver('stripe')->withData([
    'payment_intent' => $paymentIntentId
])->authorize();
```

#### `setConfig`

```php
public function setConfig(array $config): self
```

Here you can set up any additional config for this payment driver. By default, this will be called when you register your payment driver and will take any values which are set in `config/lunar/payments.php` for that type.


## Creating transactions

Depending on how your driver works, you're likely going to need to create some transactions depending on different scenarios.

### Database Schema

```
Lunar\Models\Transaction
```

|Field|Description|Example|
|:-|:-|:-|
|id||
|parent_transaction_id|The ID of the related transaction, nullable|
|order_id|The ID of the order this transaction relates to|
|success| Whether or not the transaction was successful|1
|type|Whether `intent`,`capture` or `refund`|`intent`
|driver|The driver used i.e. `stripe`|`stripe`
|amount|The amount for the transaction in cents|`10000`
|reference|The reference for the driver to use|`STRIPE_123456`
|status|Usually populated from the payment provider|`success`
|notes|Any additional notes for the transaction|
|card_type|The card type| `visa`
|last_four|The last four digits of the card used|`1234`
|captured_at| The DateTime the transaction was captured|
|meta| Any additional meta info for the transaction| `{"cvc_check": "pass", "address_line1_check": "pass", "address_postal_code_check": "pass"}`
|created_at||
|updated_at||


### Best Practices

#### Releasing

When releasing a payment, if you're not charging the card straight away, you should create a transaction with type `intent`. This tells Lunar you intend to charge the card at a later date.

```php
Transaction::create([
    //...
    'type' => 'intent',
]);
```

If you are charging the card straight away, set the type to `capture`.

```php
Transaction::create([
    //...
    'type' => 'capture',
]);
```

#### Capturing

:::tip
If you're already charging the card, you can skip this as you already have payment. 🥳
:::

When capturing a transaction, you should create an additional transaction with the amount that's been captured. Even if this is the same amount as the `intent` transaction.

```php
$intent = Transaction::whereType('intent')->first();

Transaction::create([
    //...
    'parent_transaction_id' => $intent->id,
    'type' => 'capture',
    'amount' => 2000,
]);
```

#### Refunding

```php
$capture = Transaction::whereType('capture')->first();

Transaction::create([
    //...
    'parent_transaction_id' => $capture->id,
    'type' => 'refund',
]);
```
