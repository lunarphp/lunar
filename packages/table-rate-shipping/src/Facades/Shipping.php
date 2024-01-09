<?php

namespace Lunar\Shipping\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Models\Cart;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;

/**
 * @method static shippingRates(Cart $cart)
 */
class Shipping extends Facade
{
    public static function getFacadeAccessor()
    {
        return ShippingMethodManagerInterface::class;
    }
}
