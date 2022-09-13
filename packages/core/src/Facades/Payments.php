<?php

namespace Lunar\Facades;

use Lunar\Base\PaymentManagerInterface;
use Illuminate\Support\Facades\Facade;

class Payments extends Facade
{
    public static function getFacadeAccessor()
    {
        return PaymentManagerInterface::class;
    }
}
