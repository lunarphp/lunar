<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\PaymentManagerInterface;

class Payments extends Facade
{
    public static function getFacadeAccessor()
    {
        return PaymentManagerInterface::class;
    }
}
