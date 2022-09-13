<?php

namespace Lunar\Facades;

use Lunar\Base\TaxManagerInterface;
use Illuminate\Support\Facades\Facade;

class Taxes extends Facade
{
    public static function getFacadeAccessor()
    {
        return TaxManagerInterface::class;
    }
}
