<?php

namespace Lunar\Hub\Views\Components\Menus\AppSide;

use Illuminate\View\Component;
use Lunar\Hub\Menu\MenuGroup;

class Group extends Component
{
    public MenuGroup $group;

    public $active = false;

    public $current = null;

    public function __construct(MenuGroup $group, $current = null)
    {
        $this->group = $group;
        $this->current = $current;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menus.app-side.group');
    }
}
