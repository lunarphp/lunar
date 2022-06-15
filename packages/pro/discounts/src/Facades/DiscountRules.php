<?php

namespace GetCandy\Discounts\Facades;

use GetCandy\Discounts\Interfaces\DiscountRuleManagerInterface;
use Illuminate\Support\Facades\Facade;

class DiscountRules extends Facade
{
    public static function getFacadeAccessor()
    {
        return DiscountRuleManagerInterface::class;
    }
}
