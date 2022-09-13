<?php

namespace Lunar\Facades;

use Lunar\Base\PricingManagerInterface;
use Illuminate\Support\Facades\Facade;

class Pricing extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return PricingManagerInterface::class;
    }
}
