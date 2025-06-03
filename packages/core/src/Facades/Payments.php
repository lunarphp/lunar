<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\PaymentManagerInterface;

/**
 * @see \Lunar\Managers\PaymentManager
 */
class Payments extends Facade
{
    public static function getFacadeAccessor()
    {
        return PaymentManagerInterface::class;
    }
}
