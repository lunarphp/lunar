<?php

namespace GetCandy\Discounts\Facades;

use GetCandy\Discounts\Interfaces\DiscountRewardManagerInterface;
use Illuminate\Support\Facades\Facade;

class DiscountRewards extends Facade
{
    public static function getFacadeAccessor()
    {
        return DiscountRewardManagerInterface::class;
    }
}
