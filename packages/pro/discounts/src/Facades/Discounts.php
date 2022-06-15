<?php

namespace GetCandy\Discounts\Facades;

use GetCandy\Discounts\Interfaces\DiscountsInterface;
use Illuminate\Support\Facades\Facade;

class Discounts extends Facade
{
    public static function getFacadeAccessor()
    {
        return DiscountsInterface::class;
    }
}
