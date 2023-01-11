<?php

namespace Lunar\Hub\Views\Components\Menus\AppSide;

use Illuminate\View\Component;
use Lunar\Hub\Menu\MenuLink;

class SubLink extends Component
{
    public MenuLink $item;

    public $active = false;

    public function __construct(MenuLink $item, $active = false)
    {
        $this->item = $item;
        $this->active = $active;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menus.app-side.sub-link');
    }
}
