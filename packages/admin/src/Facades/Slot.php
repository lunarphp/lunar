<?php

namespace Lunar\Hub\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Hub\Slots\SlotRegistry;

class Slot extends Facade
{
    /**
     * Return the facade class reference.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SlotRegistry::class;
    }
}
