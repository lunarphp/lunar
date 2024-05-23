# Payments

:::tip
If you're looking for a guide on how to create your own Payment Driver, or for a more in-depth look at how they work,
head over to the [extending section](/core/extending/payments).
:::

## Overview

Lunar takes a driver based approach with Payments, meaning you are free to use either add ons to support the provider
you wish to use, or you can create your own to meet your exact needs.

## Configuration

All configuration for payments is located in `config/lunar/payments.php`. Here you can specify different types of
payments and the driver each one should use.

```php
<?php

return [
    'default' => env('PAYMENTS_TYPE', 'offline'),

    'types' => [
        'cash-in-hand' => [
            'driver' => 'offline',
            'released' => 'payment-offline',
        ],
        'card' => [
            'driver' => 'stripe',
            'released' => 'payment-received',
        ],
    ],
];
```

## Usage

To use a payment driver, you need to pass the type of payment you wish to use, this will then return an instance of the
driver.

```php
$driver = \Lunar\Facades\Payments::driver('card');
```

We can then set the cart.

```php
$driver->cart(\Lunar\Models\Cart $cart);
```

Set any additional data that the driver may need.

```php
$driver->withData([
    'payment_token' => $token,
]);
```

Finally, we can authorize the payment.

```php
$driver->authorize();
```

<!-- As you'd expect, orders on an online system show what users have purchased. They are linked to a Cart and you can only have 1 order per cart in the database.

```php
Lunar\Models\Order
```

|Field|Description|
|:-|:-|
|id||
|user_id|If this is not a guest order, this will have the users id|
|channel_id|Which channel this was purchased through|
|status|A status that makes sense to you as the store owner|
|reference|Your stores own reference
|customer_reference|If you want customers to add their own reference, it goes here.
|sub_total|The sub total minus any discounts, excl. tax
|discount_total| Any discount amount excl. tax
|shipping_total| The shipping total excl. tax
|tax_breakdown| A json field for the tax breakdown e.g. `[{"name": "VAT", "total": 123, "percentage": 20}]`
|tax_total| The total amount of tax applied
|total|The grand total with tax
|notes|Any additional order notes
|currency_code|The code of the currency the order was placed in
|compare_currency_code|The code of the default currency at the time
|exchange_rate| The exchange rate between `currency_code` and `compare_currency_code`
|placed_at|The datetime the order was considered placed.
|meta|Any additional meta info you wish to store
|created_at||
|updated_at||

## Create an order

You can either create an order directly, or the recommended way is via a `Cart` model.

```php
$order = \Lunar\Models\Order::create([/** .. */]);

// Recommended way
$order = Cart::first()->createOrder();
```

If you are using the `CartSession` you can create a order via the facade, this will then handle removing the cart from the session if you want it to.

```php
$order = CartSession::createOrder();
```

By default, this will create the order and remove the cart id from the session. You can, however retain the cart id if you want by passing an option `boolean` parameter to the method:

```php
$order = CartSession::createOrder(false);
```

Now when you create the order, you will still have the cart id in the session.

So what's happening when we call `createOrder` on a cart, that's so different from just creating an order manually? Well there's a few steps Lunar takes to make sure data stays consistent and valid, it also means that a lot of the columns on an order will automatically be populated based on the cart.

Here's the order things happen when you call `createOrder`:

1. We check if the Cart has been calculated and it's totals are populated, if not we calculate
2. Validation happens on the cart to ensure we have all the data we need for the order, things like billing info etc.
3. Creation is about to happen, so before that we get any modifiers that have been set up and pass through the `Cart` so you can make any changes beforehand.
4. We create the order from the `Cart` including `CartLine` models and copying `CartAddress` models across to the new order.
5. We associate the newly created order to the `Cart`
6. The new order is then run through a series of post creation modifiers so you can make any adjustments to the new order.

Given that there is validation taking place and there could be exceptions thrown, it makes sense to wrap this function in a try/catch.

```php
try {
    $order = $cart->createOrder();
} catch (\Lunar\Exceptions\CartException $e) {
    // Return back to checkout.
}
```

If you want more fine grained control of what you do under the different exceptions, here they are:

```php
\Lunar\Exceptions\Carts\BillingAddressIncompleteException;
\Lunar\Exceptions\Carts\BillingAddressMissingException;
\Lunar\Exceptions\Carts\OrderExistsException;
```

They each extend `CartException` so it depends on how much control you need.

If you also want to check before you attempt this if the cart is ready to create an order, you can call the helper method:

```php
$cart->canCreateOrder();
```

This essentially does the same as above, except we already catch the exceptions for you and just return false if any are caught.

## Modifying Orders

If you need to programmatically change the Order values or add in new behaviour, you will want to extend the Order system.

You can find out more in the Extending Lunar section for [Order Modifiers](/extending/order-modifiers).

## Order Lines

```php
Lunar\Models\OrderLine
```

|Field|Description|
|:-|:-|
|id||
|order_id||
|purchasable_type|Class reference for the purchasable item e.g. `Lunar\Models\ProductVariant`|
|purchasable_id|
|type|Whether `digital`,`physical` etc
|description|A description of the line item
|option|If this was a variant, the option info is here
|identifier|Something to identify the purchasable item, usually an `sku`
|unit_price|The unit price of the line
|unit_quantity|The line unit quantity, usually this is 1
|quantity|The amount of this item purchased
|sub_total|The sub total minus any discounts, excl. tax
|discount_total| Any discount amount excl. tax
|tax_breakdown| A json field for the tax breakdown e.g. `[{"name": "VAT", "total": 123, "percentage": 20}]`
|tax_total| The total amount of tax applied
|total|The grand total with tax
|notes|Any additional order notes
|meta|Any additional meta info you wish to store
|created_at||
|updated_at||


### Create an order line

::: tip
If you are using the `createOrder` method on a cart, this is all handled for you automatically.
:::

```php
\Lunar\Models\OrderLine::create([
    // ...
]);
```

Or via the relationship

```php
$order->lines()->create([
    // ...
]);
```

## Order Addresses

An order can have many addresses, typically you would just have one for billing and one for shipping.

::: tip
If you are using the `createOrder` method on a cart, this is all handled for you automatically.
:::

```php
\Lunar\Models\OrderAddress::create([
    'order_id' => 1,
    'country_id' => 1,
    'title' => null,
    'first_name' => 'Jacob',
    'last_name' => null,
    'company_name' => null,
    'line_one' => '123 Foo Street',
    'line_two' => null,
    'line_three' => null,
    'city' => 'London',
    'state' => null,
    'postcode' => 'NW1 1WN',
    'delivery_instructions' => null,
    'contact_email' => null,
    'contact_phone' => null,
    'type' => 'shipping', // billing/shipping
    'shipping_option' => null, // A unique code for you to identify shipping
]);

// Or via the relationship.
$order->addresses()->create([
    // ...
]);
```

You can then use some relationship helpers to fetch the address you need:

```php
$order->shippingAddress;
$order->billingAddress;
```


## Shipping Options

::: tip
A Shipping Tables addon is planned to make setting up shipping in the admin hub easy for most scenarios.
:::

To add Shipping Options you will need to [extend Lunar](/extending/shipping) to add in your own logic.

Then in your checkout, or where ever you want, you can fetch these options:

```php
\Lunar\Facades\ShippingManifest::getOptions(\Lunar\Models\Cart $cart);
```

This will return a collection of `Lunar\DataTypes\ShippingOption` objects.

### Adding the shipping option to the cart

Once the user has selected the shipping option they want, you will need to add this to the cart so it can calculate the new totals.

```php
$cart->setShippingOption(\Lunar\DataTypes\ShippingOption $option);
```

## Transactions

```php
Lunar\Models\Transaction
```

|Field|Description|
|:-|:-|
|id||
|success|Whether the transaction was successful|
|refund|`true` if this was a refund|
|driver|The payment driver used e.g. `stripe`|
|amount|An integer amount|
|reference|The reference returned from the payment Provider. Used to identify the transaction with them.
|status|A string representation of the status, unlinked to Lunar e.g. `settled`|
|notes|Any relevant notes for the transaction
|cart_type| e.g. `visa`
|last_four| Last 4 digits of the card
|meta|Any additional meta info you wish to store
|created_at||
|updated_at||

### Create a transaction

::: tip
Just because an order has a transaction does not mean it has been placed. Lunar determines whether an order is considered placed when the `placed_at` column has a datetime, regardless if any transactions exist or not.
:::

Most stores will likely want to store a transaction against the order, this helps determining how much has been paid, how it was paid and give a clue on the best way to issue a refund if needed.

```php
\Lunar\Models\Transaction::create([
    //...
]);

// Or via the order
$order->transactions()->create([
    //..
]);
```

These can then be returned via the relationship.

```php
$order->transactions; // Get all transactions.

$order->charges; // Get all transactions that are charges.

$order->refunds; // Get all transactions that are refunds.
```

### Payment Checks

Some providers return information based on checks that can occur before payment is validated and completed. 
This is usually related to 3DSecure but in some cases can relate to credit checks or anything the payment provider has deemed relevant to the transaction.

You can get access to these checks via the `paymentChecks()` method on the `Transaction`.

```php
foreach($transaction->paymentChecks() as $check) {
    $check->successful;
    $check->label;
    $check->message;
}
```
## Payments

We will be looking to add support for the most popular payment providers, so keep an eye out here as we will list them all out.

In the meantime, you can absolutely still get a storefront working, at the end of the day Lunar doesn't really mind if you what payment provider you use or plan to use.

In terms of an order, all it's worried about is whether or not the `placed_at` column is populated on the orders table, the rest is completely up to you how you want to handle that. We have some helper utilities to make such things easier for you as laid out above.

And as always, if you have any questions you can reach out on our Discord! -->
