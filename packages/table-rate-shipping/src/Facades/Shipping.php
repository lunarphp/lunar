<?php

namespace Lunar\Shipping\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;

/**
 * @see \Lunar\Shipping\Managers\ShippingManager
 */
class Shipping extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ShippingMethodManagerInterface::class;
    }
}
