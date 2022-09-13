<?php

namespace Lunar\Hub\Facades;

use Lunar\Hub\Menu\MenuRegistry;
use Illuminate\Support\Facades\Facade;

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
