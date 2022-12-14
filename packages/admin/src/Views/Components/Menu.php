<?php

namespace Lunar\Hub\Views\Components;

use Illuminate\View\Component;
use Lunar\Hub\Facades\Menu as MenuFacade;

class Menu extends Component
{
    public $handle;

    public $sections;

    public $items;

    public $groups;

    public function __construct($handle = null)
    {
        $slot = MenuFacade::slot($handle);
        $this->items = $slot->getItems();
        $this->sections = $slot->getSections();
        $this->groups = $slot->getGroups();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('adminhub::components.menu');
    }
}
