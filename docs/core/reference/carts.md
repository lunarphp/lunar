# Carts

## Overview

Carts are a collection of products (or other custom purchasable types) that you
would like to order. Carts belong to Users (which relate to Customers).

::: tip 
Cart prices are dynamically calculated and are not stored (unlike Orders).
:::

## Carts

```php
Lunar\Models\Cart
```

| Field       | Description                                                                     |
| :---------- | :------------------------------------------------------------------------------ |
| id          | Unique ID for the cart.                                                         |
| user_id     | Can be `null` for guest users.                                                  |
| merged_id   | If a cart was merged with another cart, it defines the cart it was merged into. |
| currency_id | Carts can only be for a single currency.                                        |
| channel_id  |                                                                                 |
| coupon_code | Can be `null`, stores a promotional coupon code, e.g. `SALE20`.                 |
| created_at  |                                                                                 |
| updated_at  | When an order was created from the basket, via a checkout.                      |
| meta        | JSON data for saving any custom information.                                    |

### Creating a cart

```php
$cart = \Lunar\Models\Cart::create([
    'currency_id' => 1,
    'channel_id' => 2,
]);
```

## Cart Lines

```php
Lunar\Models\CartLine
```

| Field            | Description                                  |
| :--------------- | :------------------------------------------- |
| id               |                                              |
| cart_id          |                                              |
| purchasable_type | e.g. `Lunar\Models\ProductVariant`.          |
| purchasable_id   |                                              |
| quantity         |                                              |
| created_at       |                                              |
| updated_at       |                                              |
| meta             | JSON data for saving any custom information. |

```php
$cartLine = new \Lunar\Models\CartLine([
    'cart_id' => 1,
    'purchasable_type' => ProductVariant::class,
    'purchasable_id' => 123,
    'quantity' => 2,
    'meta' => [
        'personalization' => 'Love you mum xxx',
    ]
]);

// Or you can use the relationship on the cart.
$cart->lines()->create([/* .. */]);
```

Now you have a basic Cart up and running, it's time to show you how you would
use the cart to get all the calculated totals and tax.

We've also tried to make Carts extendable as much as possible so, depending on
what your stores requirements are, you are free to chop and change things as
much as you need to.

## Hydrating the cart totals

```php
$cart->calculate();
```

This will create a "hydrated" version of your cart with the following:

::: tip 
All values will return a `Lunar\Datatypes\Price` object. So you have
access to the following: `value`, `formatted`, `decimal` 
:::

```php
$cart->total; // The total price value for the cart
$cart->subTotal; // The cart sub total, excluding tax
$cart->subTotalDiscounted; // The cart sub total, minus the discount amount.
$cart->shippingTotal; // The monetary value for the shipping total. (if applicable)
$cart->taxTotal; // The monetary value for the amount of tax applied.
$cart->taxBreakdown; // This is a collection of all taxes applied across all lines.
$cart->discountTotal; // The monetary value for the discount total.
$cart->discountBreakdown; // This is a collection of how discounts were calculated

foreach ($cart->taxBreakdown as $taxRate) {
    $taxRate->name
    $taxRate->total->value
}

foreach ($cart->discountBreakdown as $discountBreakdown) {
    $discountBreakdown->discount_id
    foreach ($discountBreakdown->lines as $discountLine) {
        $discountLine->quantity
        $discountLine->line
    }
    $discountBreakdown->total->value
}

foreach ($cart->discountBreakdown as $discountBreakdown) {
    $discountBreakdown->discount_id
    foreach ($discountBreakdown->lines as $discountLine) {
        $discountLine->quantity
        $discountLine->line
    }
    $discountBreakdown->total->value
}

foreach ($cart->lines as $cartLine) {
    $cartLine->unitPrice; // The monetary value for a single item.
    $cartLine->total; // The total price value for the cart
    $cartLine->subTotal; // The sub total, excluding tax
    $cartLine->subTotalDiscounted; // The sub total, minus the discount amount.
    $cartLine->taxAmount; // The monetary value for the amount of tax applied.
    $cartLine->taxBreakdown; // This is a collection of all taxes applied across all lines.
    $cartLine->discountTotal; // The monetary value for the discount total.
}
```

## Modifying Carts

If you need to programmatically change the Cart values, e.g. custom discounts or
prices, you will want to extend the Cart.

You can find out more in the Extending Lunar section for
[Cart Extending](/core/extending/carts).

## Calculating Tax

During the cart's lifetime, it's unlikely you will have access to any address
information, which can be a pain when you want to accurately display the amount
of tax applied to the basket. Moreover, some countries don't even show tax until
they reach the checkout. We've tried to make this as easy and extendable as
possible for you as the developer to build your store.

When you calculate the cart totals, you will be able to set the billing and/or
shipping address on the cart, which will then be used when we calculate which
tax breakdowns should be applied.

```php

$shippingAddress = [
    'country_id' => null,
    'title' => null,
    'first_name' => null,
    'last_name' => null,
    'company_name' => null,
    'line_one' => null,
    'line_two' => null,
    'line_three' => null,
    'city' => null,
    'state' => null,
    'postcode' => 'H0H 0H0',
    'delivery_instructions' => null,
    'contact_email' => null,
    'contact_phone' => null,
    'meta' => null,
];

$billingAddress = /** .. */;

$cart->setShippingAddress($shippingAddress);
$cart->setBillingAddress($billingAddress);
```

You can also pass through a `\Lunar\Models\Address` model, or even another
`\Lunar\Models\CartAddress`

```php
$shippingAddress = \Lunar\Models\Address::first();

$cart->setShippingAddress($shippingAddress);

$cart->setBillingAddress(
    $cart->shippingAddress
);
```

## Cart Session Manager

::: tip 
The cart session manager is useful if you're building a traditional
Laravel storefront which makes use of sessions. 
:::

When building a store, you're going to want an easy way to fetch the cart for
the current user (or guest user) by retrieving it from their current session.
Lunar provides an easy to use class to make this easier for you, so you don't
have to keep reinventing the wheel.

### Available config

Configuration for your cart is handled in `lunar/cart.php`

| Field         | Description                                                                            | Default      |
| :------------ | :------------------------------------------------------------------------------------- | :----------- |
| `session_key` | What key to use when storing the cart id in the session                                | `lunar_cart` |
| `auto_create` | If no current basket exists, should we create one in the database?                     | `false`      |
| `auth_policy` | When a user logs in, how should we handle merging of the basket?                       | `merge`      |
| `eager_load`  | Which relationships should be eager loaded by default when calculating the cart totals |

### Getting the cart session instance

You can either use the facade or inject the `CartSession` into your code.

```php
$cart = \Lunar\Facades\CartSession::current();

public function __construct(
    protected \Lunar\Base\CartSessionInterface $cartSession
) {
    // ...
}
```

### Fetching the current cart

```php
$cart = \Lunar\Facades\CartSession::current();
```

When you call current, you have two options, you either return `null` if they
don't have a cart, or you want to create one straight away. By default, we do
not create them initially as this could lead to a ton of cart models being
created for no good reason. If you want to enable this functionality, you can
adjust the config in `lunar/cart.php`

### Forgetting the cart

```php
CartSession::forget();
```

### Using a specific cart

You may want to manually specify which cart should be used for the session.

```php
$cart = \Lunar\Models\Cart::first();
CartSessionManager::use($cart);
```

The other available methods are as follows:

### Add a cart line

```php
CartSession::add($purchasable, $quantity);
```

### Add multiple lines

```php
CartSession::addLines([
    [
        'id' => 1,
        'quantity' => 25,
        'meta' => ['foo' => 'bar'],
    ],
    // ...
]);
```

_Accepts a `collection` or an `array`_

### Update a single line

```php
CartSession::updateLine($cartLineId, $quantity, $meta);
```

### Update multiple lines

```php
CartSession::updateLines(collect([
    [
        'id' => 1,
        'quantity' => 25,
        'meta' => ['foo' => 'bar'],
    ],
    // ...
]));
```

### Remove a line

```php
CartSession::removeLine($cartLineId);
```

### Clear a cart

This will remove all lines from the cart.

```php
CartSession::clear();
```

### Associating a cart to a user

You can easily associate a cart to a user.

```php
$cart = \Lunar\Models\Cart::first();
CartSession::associate($cart, $user, 'merge');
```

### Adding shipping/billing address

As outlined above, you can add shipping / billing addresses to the cart using
the following methods:

```php
$cart->setShippingAddress([
    'first_name' => null,
    'last_name' => null,
    'line_one' => null,
    'line_two' => null,
    'line_three' => null,
    'city' => null,
    'state' => null,
    'postcode' => null,
    'country_id' => null,
]);
$cart->setBillingAddress([
    'first_name' => null,
    'last_name' => null,
    'line_one' => null,
    'line_two' => null,
    'line_three' => null,
    'city' => null,
    'state' => null,
    'postcode' => null,
    'country_id' => null,
]);
```

You can easily retrieve these addresses by accessing the appropriate property:

```php
$cart->shippingAddress;
$cart->billingAddress;
```

## Handling User Login

When a user logs in, you will likely want to check if they have a cart
associated to their account and use that, or if they have started a cart as a
guest and logged in, you will likely want to be able to handle this. Lunar takes
the pain out of this by listening to the authentication events and responding
automatically by associating any previous guest cart they may have had and,
depending on your `auth_policy` merge or override the basket on their account.
