<?php

namespace GetCandy\Base;

use Closure;
use GetCandy\Models\Cart;
use GetCandy\Models\Order;

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
