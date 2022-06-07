# Order Modifiers

[[toc]]

## Overview

If you want to add additional functionality to the Order creation process, you can do so using Order Modifiers.

## Registering an Order modifier

Much like Carts, we just need to register these in our service provider.

```php
public function boot(OrderModifiers $orderModifiers)
{
    $orderModifiers->add(
        \App\Modifiers\CustomOrderModifier::class
    )
}
```

```php
namespace App\Modifiers;

use Closure;
use GetCandy\Base\OrderModifier;
use GetCandy\Models\Cart;
use GetCandy\Models\Order;

class CustomOrderModifier extends OrderModifier
{
    public function creating(Cart $cart, Closure $next): Cart
    {
        //...

        return $next($cart);
    }

    public function created(Order $order, Closure $next): Order
    {
        //...
        return $next($order);
    }
}

```

When using your own `OrderModifier` things can go wrong or for whatever reason you may need to abort the process and take the customer back to the checkout. For this you can throw a `CartException` (or your own exception that extends this) at any point in the flow and it'll stop.

The process is wrapped in a transaction so no need to worry about incomplete data making it's way in to the database.

```php
namespace App\Exceptions;

class MyCustomException extends \GetCandy\Exceptions\CartException
{

}
```

```php
public function creating(Cart $cart)
{
    if ($somethingWentTerriblyWrong) {
        throw new MyCustomException;
    }
}
```
