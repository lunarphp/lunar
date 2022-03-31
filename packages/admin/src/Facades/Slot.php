<?php

namespace GetCandy\Hub\Facades;

use GetCandy\Hub\Slots\SlotRegistry;
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
