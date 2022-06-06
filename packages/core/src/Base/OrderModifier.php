<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;
use GetCandy\Models\Order;

abstract class OrderModifier
{
    /**
     * Handle the process calculating pipeline
     *
     * @param Cart $cart
     * @param \Closure $next
     *
     * @return \Closure
     */
    public function processCreating(Cart $cart, $next)
    {
        $this->creating($cart);

        return $next($cart);
    }

    /**
     * Handle the process calculating pipeline
     *
     * @param Order $order
     * @param \Closure $next
     *
     * @return \Closure
     */
    public function processCreated(Order $order, $next)
    {
        $this->created($order);

        return $next($order);
    }

    public function creating(Cart $cart)
    {
        //
    }

    public function created(Order $order)
    {
        //
    }
}
