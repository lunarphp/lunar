<?php

namespace Lunar\Hub\Views\Components\Menus\AppSide;

use Illuminate\View\Component;
use Lunar\Hub\Menu\MenuLink;
use Lunar\Hub\Menu\MenuSection;

class Link extends Component
{
    public MenuLink|MenuSection $item;

    public $active = false;

    public $hasSubItems = false;

    public function __construct(MenuLink|MenuSection $item, $active = false, $hasSubItems = false)
    {
        $this->item = $item;
        $this->active = $active;
        $this->hasSubItems = $hasSubItems;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menus.app-side.link');
    }
}
