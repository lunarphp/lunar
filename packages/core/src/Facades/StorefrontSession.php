<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\StorefrontSessionInterface;

class StorefrontSession extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StorefrontSessionInterface::class;
    }
}
