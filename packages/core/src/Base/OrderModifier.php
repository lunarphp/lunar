<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;
use GetCandy\Models\Order;

abstract class OrderModifier
{
    public function creating(Cart $cart)
    {
        //
    }

    public function created(Order $order)
    {
        //
    }
}
