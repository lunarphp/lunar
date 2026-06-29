<?php

namespace Lunar\Core\Modifiers;

use Closure;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;

abstract class OrderModifier
{
    public function creating(Cart $cart, Closure $next): Cart
    {
        return $next($cart);
    }

    public function created(Order $order, Closure $next): Order
    {
        return $next($order);
    }
}
