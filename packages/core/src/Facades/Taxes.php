<?php

namespace GetCandy\Facades;

use GetCandy\Base\TaxManagerInterface;
use Illuminate\Support\Facades\Facade;

class Taxes extends Facade
{
    public static function getFacadeAccessor()
    {
        return TaxManagerInterface::class;
    }
}
