<?php

namespace GetCandy\Discounts\Facades;

use Illuminate\Support\Facades\Facade;
use GetCandy\Discounts\Interfaces\DiscountConditionManagerInterface;

class DiscountConditions extends Facade
{
    public static function getFacadeAccessor()
    {
        return DiscountConditionManagerInterface::class;
    }
}
