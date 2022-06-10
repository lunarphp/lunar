<?php

namespace GetCandy\Discounts\Facades;

use Illuminate\Support\Facades\Facade;
use GetCandy\Discounts\Interfaces\DiscountsInterface;

class Discounts extends Facade
{
    public static function getFacadeAccessor()
    {
        return DiscountsInterface::class;
    }
}
