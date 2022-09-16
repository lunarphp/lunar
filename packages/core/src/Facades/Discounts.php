<?php

namespace Lunar\Facades;

use Lunar\Base\DiscountManagerInterface;
use Illuminate\Support\Facades\Facade;

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
