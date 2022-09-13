<?php

namespace Lunar\Hub\Facades;

use Lunar\Hub\Slots\SlotRegistry;
use Illuminate\Support\Facades\Facade;

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
