<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\PricingManagerInterface;

/**
 * @see \Lunar\Managers\PricingManager
 */
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
