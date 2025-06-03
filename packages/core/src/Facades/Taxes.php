<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\TaxManagerInterface;

/**
 * @see \Lunar\Managers\TaxManager
 */
class Taxes extends Facade
{
    public static function getFacadeAccessor()
    {
        return TaxManagerInterface::class;
    }
}
