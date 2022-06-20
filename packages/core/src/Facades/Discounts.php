<?php

namespace GetCandy\Facades;

use GetCandy\Base\DiscountManagerInterface;
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
