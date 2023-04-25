<?php

namespace Lunar\Hub\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Hub\Base\DiscountTypesInterface;

class DiscountTypes extends Facade
{
    /**
     * Return the facade class reference.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return DiscountTypesInterface::class;
    }
}
