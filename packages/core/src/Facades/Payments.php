<?php

namespace GetCandy\Facades;

use GetCandy\Base\PaymentManagerInterface;
use Illuminate\Support\Facades\Facade;

class Payments extends Facade
{
    public static function getFacadeAccessor()
    {
        return PaymentManagerInterface::class;
    }
}
