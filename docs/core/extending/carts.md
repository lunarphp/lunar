# Cart Extending

## Overview

Carts are a central part of any E-Commerce storefront. We have designed Carts to be easily extended, so you can add any logic you need for your storefront throughout its lifetime.

## Pipelines

### Adding a Cart Pipeline

All pipelines are defined in `config/lunar/cart.php`

```php
'pipelines' => [
    /*
     |--------------------------------------------------------------------------
     | Run these pipelines when the cart is calculating.
     |--------------------------------------------------------------------------
     */
    'cart' => [
        \Lunar\Pipelines\Cart\CalculateLines::class,
        \Lunar\Pipelines\Cart\ApplyShipping::class,
        \Lunar\Pipelines\Cart\ApplyDiscounts::class,
        \Lunar\Pipelines\Cart\CalculateTax::class,
        \Lunar\Pipelines\Cart\Calculate::class,
    ],
    /*
     |--------------------------------------------------------------------------
     | Run these pipelines when the cart lines are being calculated.
     |--------------------------------------------------------------------------
     */
    'cart_lines' => [
        \Lunar\Pipelines\CartLine\GetUnitPrice::class,
    ],
],
```

You can add your own pipelines to the configuration, they might look something like:

```php
<?php

namespace App\Pipelines\Cart;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;

class CustomCartPipeline
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        // Do something to the cart...

        return $next($cart);
    }
}
```

```php
'cart' => [
    // ...
    App\Pipelines\Cart\CustomCartPipeline::class,
],
```

::: tip
Pipelines will run from top to bottom
:::

## Actions

During the lifecycle of a Cart, various actions are taken. While generally what Lunar provides will be fine for most storefronts, there are times where you may want something done slightly differently. For this reason we have made all actions configurable, so you can swap them out as you see fit.

Actions are defined in `config/lunar/carts` and if you need to replace an action, check the class of the action you want to change to see what it is expecting.

## Action validation

You may wish to provide some validation against actions before they run. Your own validation may look something like:


```php
<?php

namespace App\Validation\CartLine;

use Lunar\Validation\BaseValidator;

class CartLineQuantity extends BaseValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        $quantity = $this->parameters['quantity'] ?? 0;

        // ...

        if (!$condition) {
            return $this->fail('cart', 'Something went wrong');
        }


        return $this->pass();
    }
}

```

You can then register this class against the corresponding action in `config/lunar/cart.php`:

```php
'validators' => [
    'add_to_cart' => [
        // ...
        \App\Validation\CartLine\CartLineQuantity::class,
    ],
    // ...
],
```

If validation fails, a `Lunar\Exceptions\CartException` will be thrown. You will be able to access errors like you can on Laravel's own validation responses.

```php
try {
    $cart->setShippingOption($option);
} catch (CartException $e) {
    $e->errors()->all();
}
```
