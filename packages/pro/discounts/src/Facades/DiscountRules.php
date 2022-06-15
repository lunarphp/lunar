<?php

namespace GetCandy\Discounts\Facades;

use Illuminate\Support\Facades\Facade;
use GetCandy\Discounts\Interfaces\DiscountConditionManagerInterface;
use GetCandy\Discounts\Interfaces\DiscountRuleManagerInterface;

class DiscountRules extends Facade
{
    public static function getFacadeAccessor()
    {
        return DiscountRuleManagerInterface::class;
    }
}
