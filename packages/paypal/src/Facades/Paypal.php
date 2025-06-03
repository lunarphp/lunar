<?php

namespace Lunar\Paypal\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Paypal\PaypalInterface;

/**
 * @see \Lunar\Paypal\Paypal
 */
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
