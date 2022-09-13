<?php

namespace Lunar\Hub\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Hub\Menu\MenuRegistry;

class Menu extends Facade
{
    /**
     * Return the facade class reference.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MenuRegistry::class;
    }
}
