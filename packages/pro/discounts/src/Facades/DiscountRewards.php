<?php

namespace GetCandy\Discounts\Facades;

use Illuminate\Support\Facades\Facade;
use GetCandy\Discounts\Interfaces\DiscountRewardManagerInterface;

class DiscountRewards extends Facade
{
    public static function getFacadeAccessor()
    {
        return DiscountRewardManagerInterface::class;
    }
}
