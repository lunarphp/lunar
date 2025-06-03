<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\DiscountManagerInterface;

/**
 * @see \Lunar\Managers\DiscountManager
 */
class Discounts extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return DiscountManagerInterface::class;
    }
}
