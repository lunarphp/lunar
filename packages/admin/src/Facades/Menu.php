<?php

namespace GetCandy\Hub\Facades;

use GetCandy\Hub\Menu\MenuRegistry;
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
