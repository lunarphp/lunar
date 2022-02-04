<?php

namespace GetCandy\Facades;

use Illuminate\Support\Facades\Facade;
use GetCandy\Base\TaxManagerInterface;

class Taxes extends Facade
{
    public static function getFacadeAccessor()
    {
        return TaxManagerInterface::class;
    }
}
