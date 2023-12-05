<?php

namespace Lunar\Paypal\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\DiscountManagerInterface;
use Lunar\Paypal\PaypalInterface;

class Paypal extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PaypalInterface::class;
    }
}