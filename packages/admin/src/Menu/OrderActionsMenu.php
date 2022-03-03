<?php

namespace GetCandy\Hub\Menu;

use GetCandy\Hub\Facades\Menu;

class OrderActionsMenu
{
    /**
     * Make menu.
     *
     * @return void
     */
    public static function make()
    {
        (new static())
            ->makeTopLevel();
    }

    /**
     * Make top level navigation.
     *
     * @return static
     */
    protected function makeTopLevel()
    {
        Menu::slot('order_actions');

        return $this;
    }
}
