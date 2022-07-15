<?php

namespace GetCandy\Hub\Facades;

use GetCandy\Hub\Menu\MenuRegistry;
use GetCandy\Hub\Menu\MenuSlot;
use Illuminate\Support\Facades\Facade;

/**
 * @method static MenuSlot slot(string $handle)
 *
 * @see \GetCandy\Hub\Menu\MenuRegistry
 */
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
